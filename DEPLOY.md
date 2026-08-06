# Guia de Deploy - Teddy Bier Tools

## Pré-requisitos no Servidor

- Docker Engine 20.10+
- Docker Compose 2.0+
- Rede Docker externa `web` criada:
  ```bash
  docker network create web
  ```
- Traefik em execução e conectado à rede `web`
- Cloudflare Tunnel (cloudflared) já configurado

## Primeira Implantação

### 1. Clonar o repositório

```bash
git clone https://github.com/seu-usuario/teddy-bier-tools.git /srv/teddy-bier-tools
cd /srv/teddy-bier-tools
```

### 2. Criar arquivo `.env.production`

Crie o arquivo baseado no `.env.example`:

```bash
cp .env.example .env.production
```

Edite `.env.production` com as credenciais reais:

```bash
nano .env.production
```

**Variáveis críticas:**
- `APP_KEY`: Gere com `php artisan key:generate --show` numa máquina com Laravel instalado
- `DB_PASSWORD`: Escolha uma senha forte para o usuário MySQL `bier`
- `REDIS_PASSWORD`: Opcional, mas recomendado em produção
- `APP_URL`: Deve ser `https://tools.teddybier.com.br`
- `RUN_MIGRATIONS`: Defina como `true` **apenas** para a primeira implantação

### 3. Gerar APP_KEY

Se não tiver Laravel instalado localmente, use Docker:

```bash
docker run -it --rm -v /srv/teddy-bier-tools:/app -w /app php:8.4-fpm \
  php artisan key:generate --show
```

Copie o valor gerado e insira em `.env.production`:

```
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
```

### 4. Construir e subir a stack

```bash
cd /srv/teddy-bier-tools
docker compose -f compose.yaml -f compose.prod.yaml build
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

**Aguarde** alguns segundos para os containers iniciarem. O entrypoint de produção aguardará MySQL e Redis ficarem healthy antes de iniciar o PHP-FPM.

### 5. Verificar status

```bash
docker compose -f compose.yaml -f compose.prod.yaml ps
```

Verifique que todos os containers estão com `Up` ou `healthy`.

### 6. Acompanhar logs

```bash
docker compose -f compose.yaml -f compose.prod.yaml logs -f app
```

Procure por mensagens de sucesso como:
```
>>> Cache de configuração...
>>> Cache de rotas...
>>> Cache de views...
>>> Iniciando php-fpm...
```

## Deploys Subsequentes

### Atualização de código

```bash
cd /srv/teddy-bier-tools
git pull origin main
docker compose -f compose.yaml -f compose.prod.yaml build
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

A imagem será reconstruída com o código novo. O container `app` reiniciará automaticamente.

### Executar migrações após deploy

Se houve mudanças no schema, edite `.env.production`:

```bash
RUN_MIGRATIONS=true
```

Execute um redeploy:

```bash
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

Depois de confirmar que a migração completou com sucesso nos logs, **volte** `RUN_MIGRATIONS=false`:

```bash
RUN_MIGRATIONS=false
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

## Troubleshooting

### "Cannot find module" ou "vendor not found"

A imagem de produção não inclui node_modules nem vendor no contexto de build por motivos de segurança. Se os arquivos não foram instalados:

```bash
cd /srv/teddy-bier-tools
docker compose -f compose.yaml -f compose.prod.yaml build --no-cache
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

### Site retorna erro 502 Bad Gateway

O Nginx está rodando mas PHP-FPM não respondeu a tempo. Verifique:

```bash
docker compose -f compose.yaml -f compose.prod.yaml logs app
```

Procure por:
- Erros de conexão com MySQL (`SQLSTATE[HY000]`)
- Erros de configuração do Laravel
- Problemas de permissão em `storage/` ou `bootstrap/cache`

Se o container está morto:

```bash
docker compose -f compose.yaml -f compose.prod.yaml restart app
```

### Mixed Content (site via HTTPS, assets via HTTP)

O Traefik envia `X-Forwarded-Proto: https` mas o Laravel não está reconhecendo. Verifique que o middleware `TrustProxies` está ativo nos logs:

```bash
docker compose -f compose.yaml -f compose.prod.yaml logs app | grep -i proxy
```

Se não aparecer, a imagem pode estar desatualizada. Force rebuild:

```bash
docker compose -f compose.yaml -f compose.prod.yaml build --no-cache
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

### Reverter para commit anterior

Se algo crítico quebrou:

```bash
cd /srv/teddy-bier-tools
git log --oneline | head -10    # Localize o commit seguro
git checkout <commit-hash>
docker compose -f compose.yaml -f compose.prod.yaml build
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

Depois investigar o commit problemático num branch separado.

## Manutenção

### Backup do banco de dados

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec mysql \
  mysqldump -u bier -p teddy_bier_tools > /backup/bier_$(date +%Y%m%d_%H%M%S).sql
```

### Limpeza de logs do container

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec app \
  php artisan log:clear
```

### Cache de view/config expirado

Se o site ficar desfazido após um deploy manual:

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec app php artisan view:clear
docker compose -f compose.yaml -f compose.prod.yaml exec app php artisan config:clear
docker compose -f compose.yaml -f compose.prod.yaml restart app
```

## Arquivo de Exemplo `.env.production`

```bash
APP_NAME="Teddy Bier Tools"
APP_ENV=production
APP_KEY=base64:XXXXX...
APP_DEBUG=false
APP_URL=https://tools.teddybier.com.br

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=notice

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=teddy_bier_tools
DB_USERNAME=bier
DB_PASSWORD=SENHA_FORTE_AQUI
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis

CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

MAIL_MAILER=log

VITE_APP_NAME="${APP_NAME}"
GOOGLE_ANALYTICS_ID=

RUN_MIGRATIONS=false
PHP_OPCACHE_ENABLE=1
```
