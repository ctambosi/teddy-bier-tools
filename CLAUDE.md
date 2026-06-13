# Teddy Bier Tools — CLAUDE.md

Ferramentas de cálculo para cervejeiros artesanais. Migração de PHP puro + jQuery → Laravel 11 + Inertia.js + Vue 3 + Tailwind CSS 4.

---

## Stack

| Camada      | Tecnologia |
|-------------|------------|
| Backend     | Laravel 11, PHP 8.4-FPM |
| Frontend    | Vue 3 (Composition API, `<script setup>`), Inertia.js 2 |
| CSS         | Tailwind CSS 4 (`@tailwindcss/vite`) |
| Build       | Vite 8 |
| Estado      | Pinia 3 + `@vueuse/core` (`useLocalStorage`) |
| Rotas Vue   | Ziggy (`tightenco/ziggy` + `ziggy-js`) |
| HTTP        | axios (com `Accept: application/json` global) |
| DB          | MySQL 8 |
| Cache       | Redis 7 |

---

## Docker

| Container    | Papel           | Porta host |
|--------------|-----------------|------------|
| `bier_app`   | PHP-FPM         | —          |
| `bier_nginx` | Nginx           | 8010       |
| `bier_mysql` | MySQL           | 3307       |
| `bier_redis` | Redis           | 6380       |
| `bier_node`  | Vite dev server | 5173 (profile `dev` — raramente usado) |

**Conexão interna ao MySQL:** host `mysql`, porta `3306` (não 3307).

### Comandos do dia a dia

```bash
# Subir o projeto
docker compose up -d

# Rebuildar assets após mudar código Vue/CSS
docker exec bier_app npm run build

# Rodar migrations
docker exec bier_app php artisan migrate

# Limpar cache de rotas (após mudar routes/web.php)
docker exec bier_app php artisan route:clear
docker exec bier_app php artisan optimize:clear

# Acessar shell do container
docker exec -it bier_app sh
```

### Sobre o `public/hot`

O arquivo `public/hot` é criado pelo `npm run dev` (servidor de desenvolvimento do Vite). Se ele existir com o servidor parado, a página fica em branco (assets apontam para `localhost:5173` que não responde).

**Proteções já em vigor:**
- `docker/php/entrypoint.sh` remove o arquivo a cada subida do container
- O serviço `node` (profile `dev`) remove o arquivo ao parar
- `public/hot` está no `.gitignore`

Se a página ficar em branco: `rm public/hot` e recarregue.

---

## Mapeamento de Funcionalidades

### Módulo: Conversões (`/conversao/`)

| URL | Rota nomeada | Service | Descrição |
|-----|---|---|---|
| `/conversao/densidade` | `conversao.densidade` | `ConversaoService::calcularDensidade()` | Converte entre SG, Brix e Plato (1 campo → 2) |
| `/conversao/temperatura` | `conversao.temperatura` | `ConversaoService::calcularTemperatura()` | Converte entre °C e °F |
| `/conversao/cor` | `conversao.cor` | `ConversaoService::calcularCor()` | Converte entre EBC, SRM e Lovibond |
| `/conversao/pressao` | `conversao.pressao` | `ConversaoService::calcularPressao()` | Converte entre Bar e PSI |

**Fórmulas-chave:**
- SG → Brix: `((182.4601 × SG − 775.6821) × SG + 1262.7794) × SG − 669.5622`
- SG → Plato: polinômio cúbico NBS
- EBC ↔ SRM: `SRM = EBC × 0.508` / `EBC = SRM × 1.97`
- SRM ↔ Lovibond: `Lov = (SRM + 0.76) / 1.3546`

---

### Módulo: Correções (`/correcao/`)

| URL | Rota nomeada | Service | Descrição |
|-----|---|---|---|
| `/correcao/densimetro` | `correcao.densimetro` | `CorrecaoService::corrigirDensimetro()` | Corrige SG pela temperatura (NBS/NIST, calibração 20°C) |
| `/correcao/refratometro` | `correcao.refratometro` | `CorrecaoService::corrigirRefratometro()` | Corrige refratômetro durante fermentação (Novotný) |
| `/correcao/pressao-temperatura` | `correcao.pressao-temperatura` | `CorrecaoService::calcularPressaoCarbonatacao()` | Pressão necessária para carbonatação forçada dado CO₂ e temperatura |

**Fórmulas-chave:**
- Densímetro: razão NBS — `SG_corrigido = SG × (fatorNBS(T_med) / fatorNBS(T_cal))`
- Refratômetro (Novotný): `SG = 0.00628 × CG_brix − 0.0025 × OG_brix + 1.0013`
- Pressão × Temperatura (equação inversa de carbonatação, P em bar, T em °C)

---

### Módulo: Cálculos (`/calculo/`)

| URL | Rota nomeada | Service | Descrição |
|-----|---|---|---|
| `/calculo/abv` | `calculo.abv` | `CalculoService::calcularAbv()` | Teor alcoólico: `(OG − FG) × 131` |
| `/calculo/extracao-frio` | `calculo.extracao-frio` | `CalculoService::calcularExtracaoFrio()` | Volume de água para extração a frio: `1.9 × g / 454` litros |
| `/calculo/percentual-acucar` | `calculo.percentual-acucar` | `CalculoService::calcularPercentualAcucar()` | Densidade do grist sem o açúcar, dado OG e % açúcar |
| `/calculo/levedura` | `calculo.levedura` | `CalculoService::calcularLevedura()` | Gramas de lama: `ceil(células × 1.087 / concentração)` |
| `/calculo/diluicao-alcoolica` | `calculo.diluicao-alcoolica` | `CalculoService::calcularDiluicaoAlcoolica()` | Água a adicionar para reduzir graduação |
| `/calculo/adicao-alcoolica` | `calculo.adicao-alcoolica` | `CalculoService::calcularAdicaoAlcoolica()` | Álcool a adicionar para elevar graduação |
| `/calculo/motor` | `calculo.motor` | `CalculoService::calcularPolia()` | Polias: `n₁ × D₁ = n₂ × D₂`, 1 campo em branco resolvido |
| `/calculo/volume-mosto` | `calculo.volume-mosto` | `CalculoService::calcularVolumeMosto()` | `π × r² × h / 1000 − perda`; panelas salvas no localStorage |
| `/calculo/priming` | `calculo.priming` | `CalculoService::calcularPriming()` | CO₂ residual (Brewer's Friend), 5 tipos de açúcar, distribuição por garrafa |

**Constantes do `CalculoService`:**
- `CELULAS_POR_GRAMA = 1.087` (bilhões de células/g de lama)
- `FATORES_ACUCAR`: sucrose 2.0, dextrose mono 2.09, dextrose anidra 1.89, DME 2.73, mel 2.69

---

### Páginas não migradas (descartadas)

- PPM
- Roda de aromas
- Lista de equipamentos
- Fornecedores
- Contato

---

## Arquitetura

### Convenção de rotas

```
GET  /prefix/recurso   → Controller::recurso()         → Inertia::render('Page')
POST /prefix/recurso   → Controller::calcularRecurso() → response()->json([...])
```

Nunca usar Inertia forms para cálculos — sempre axios POST retornando JSON.

### Estrutura de um módulo completo

```
app/
  Http/
    Controllers/FooController.php     ← GET (Inertia) + POST (JSON)
    Requests/FooBarRequest.php        ← validação com mensagens em PT-BR
  Services/FooService.php             ← lógica de negócio (métodos static)

resources/js/
  Pages/Foo/Bar.vue                   ← página Vue (Composition API)
```

### Regra crítica: SG (densidade específica)

- Sempre **3 casas decimais, sem arredondamento**: `1.048`, nunca `1.050`
- Formatação feita **exclusivamente no frontend** pelo componente `SgInput.vue`
- O usuário digita `1048`, o sistema exibe `1.048`
- O backend recebe e processa o valor como float normalmente

### Validação

- Form Requests em `app/Http/Requests/`
- Mensagens de erro sempre em **português brasileiro**
- Para erros de negócio (ex: "mais de um campo preenchido"): chave `geral` em `withValidator`
- `bootstrap/app.php` tem `shouldRenderJsonWhen` configurado com `|| $request->wantsJson()` para que `ValidationException` retorne 422 JSON (não 302)

### Padrão Vue (todas as páginas de calculadora)

```vue
<script setup>
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

const form = reactive({ campo: '' })
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('prefix.recurso.calcular'), form)
        resultado.value = data
    } catch (e) {
        const erros = e.response?.data?.errors ?? {}
        erroGeral.value = erros.geral?.[0]
            ?? Object.values(erros)[0]?.[0]
            ?? 'Erro ao calcular.'
    } finally {
        loading.value = false
    }
}
</script>
```

---

## Componentes reutilizáveis

| Componente | Uso |
|---|---|
| `CalculadoraCard.vue` | Wrapper de card para todas as ferramentas. Props: `titulo`, `descricao` |
| `SgInput.vue` | Input de densidade (SG). `v-model` com string. Auto-formata 1048 → 1.048 |

---

## Estado persistido (Pinia)

`resources/js/stores/panelas.js` — panelas do Volume de Mosto salvas no localStorage via `useLocalStorage('bier_panelas_v1', [])`.

Campos persistidos por panela: `id`, `nome`, `diametro`, `alturaPanela`, `perda`.

---

## Middleware

- `HandleInertiaRequests` — share props do Inertia
- `TrackPageView` — registra visitas na tabela `page_views` (MySQL)

---

## Configurações críticas

### `.env` (dentro do Docker)
```
DB_CONNECTION=mysql
DB_HOST=mysql        # nome do serviço Docker, não localhost
DB_PORT=3306         # porta interna, não 3307
```

### `bootstrap/app.php`
```php
// Validação retorna JSON (não redirect) para requisições axios
$exceptions->shouldRenderJsonWhen(
    fn(Request $request) => $request->is('api/*') || $request->wantsJson(),
);

// Middleware web
$middleware->web(append: [
    \App\Http\Middleware\HandleInertiaRequests::class,
    \App\Http\Middleware\TrackPageView::class,
]);
```

### `resources/js/app.js`
```js
// axios configurado globalmente para sempre pedir JSON
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
```

### `resources/views/app.blade.php`
```html
@routes   ← obrigatório para Ziggy funcionar nas páginas Vue
@vite([...])
@inertiaHead
```

---

## Diretrizes de Desenvolvimento

### PHP — PSR-2 e boas práticas

**Formatação obrigatória (PSR-2 / PSR-12):**
- Indentação com 4 espaços (nunca tabs)
- Chaves de classe e método na mesma linha da assinatura para closures, linha seguinte para classes/métodos
- Uma linha em branco entre métodos
- Propriedades e constantes de classe antes dos métodos
- `declare(strict_types=1);` em todos os arquivos PHP
- Imports agrupados: `use` sem misturar classes, funções e constantes

**Type hints obrigatórios:**
```php
// Sempre tipar parâmetros e retorno — nunca omitir
public static function calcular(float $og, float $fg): float
{
    return ($og - $fg) * 131;
}

// Nullable explícito, não omitido
public static function calcularPolia(?float $n1, ?float $d1): array
```

**Nomenclatura:**
- Classes: `PascalCase`
- Métodos e variáveis: `camelCase`
- Constantes: `UPPER_SNAKE_CASE`
- Colunas de banco: `snake_case`

---

### PHP — SOLID

**Single Responsibility:** cada Service tem uma única responsabilidade de domínio (`CalculoService`, `ConversaoService`, `CorrecaoService`). Controllers apenas orquestram: recebem request, chamam service, retornam response.

```php
// Correto — controller delega ao service
public function calcularAbv(CalculoAbvRequest $request): JsonResponse
{
    $resultado = CalculoService::calcularAbv($request->float('og'), $request->float('fg'));
    return response()->json(['abv' => $resultado]);
}

// Errado — lógica no controller
public function calcularAbv(Request $request): JsonResponse
{
    $abv = ($request->og - $request->fg) * 131; // nunca
    return response()->json(['abv' => $abv]);
}
```

**Open/Closed:** use constantes de lookup (`FATORES_ACUCAR`) em vez de `if/elseif` para adicionar novos tipos sem modificar a lógica existente.

**Liskov Substitution:** Form Requests estendem `FormRequest` sem quebrar o contrato de `authorize()` e `rules()`.

**Interface Segregation:** sem interfaces artificiais — Services são classes concretas com métodos estáticos pois não há necessidade de polimorfismo neste domínio.

**Dependency Inversion:** não injetar dependências concretas nos controllers manualmente; usar Form Requests para validação e Services estáticos para cálculo puro.

---

### Laravel — Boas Práticas

**Form Requests:**
- Validação exclusivamente em `app/Http/Requests/` — nunca `$request->validate()` inline no controller
- Mensagens de erro em português brasileiro no método `messages()`
- Erros de negócio (campos mutuamente exclusivos etc.) em `withValidator()` com chave `geral`

```php
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $v) {
        $preenchidos = collect(['sg', 'brix', 'plato'])
            ->filter(fn($c) => $this->filled($c))
            ->count();

        if ($preenchidos > 1) {
            $v->errors()->add('geral', 'Preencha apenas um campo.');
        }
    });
}
```

**Controllers:**
- Métodos GET: apenas `Inertia::render('Page')` — sem lógica
- Métodos POST: `return response()->json([...])` — sem redirect
- Nunca `dd()`, `var_dump()` ou `print_r()` em produção

**Services:**
- Métodos `static` — sem estado de instância (cálculos puros)
- Retornam arrays ou scalars — nunca objetos de response HTTP
- Nomear como `calcular*()`, `converter*()`, `corrigir*()`

**Migrations:**
- Sempre com `down()` funcional
- Colunas com comentários quando a unidade não é óbvia

---

### Vue 3 — Boas Práticas

**Composition API com `<script setup>` obrigatório em todos os componentes.**

**Ordem dentro do `<script setup>`:**
1. Imports de Vue (`ref`, `reactive`, `computed`, `watch`)
2. Imports de bibliotecas (`axios`, `Head`, `route`)
3. Imports de layouts e componentes
4. Props e emits (`defineProps`, `defineEmits`)
5. Estado reativo (`const form = reactive(...)`, `const resultado = ref(null)`)
6. Computed properties
7. Funções (handlers, métodos)

**Reatividade:**
- `reactive()` para formulários com múltiplos campos correlacionados
- `ref()` para valores escalares (resultado, loading, erro)
- Nunca misturar `.value` e `reactive` no mesmo objeto

**Nomenclatura:**
- Componentes: `PascalCase.vue`
- Props: `camelCase` no JS, `kebab-case` no template
- Eventos: `kebab-case` no template (`@update:model-value`)
- Funções de handler: prefixo da ação (`calcular`, `salvar`, `remover`)

**Template:**
- Usar `v-bind:` shorthand `:`
- Usar `v-on:` shorthand `@`
- `key` obrigatório em `v-for`
- Evitar lógica complexa no template — extrair para `computed`

**Sem `options API`** — nunca usar `data()`, `methods:`, `computed:` como objeto.

---

### Tailwind CSS 4 — Boas Práticas

- Classes utilitárias diretamente no template — sem `@apply` salvo componentes reutilizáveis de base
- Manter consistência de espaçamento com a escala do Tailwind (`p-4`, `gap-6`, não `p-[14px]`)
- Valores arbitrários (`p-[14px]`) apenas quando não existe equivalente na escala padrão
- Responsividade mobile-first: `sm:`, `md:`, `lg:` para breakpoints

---

## Preferências do usuário

- Respostas sempre em **português brasileiro**
- Vue sempre com Composition API e `<script setup>`
- Sem comentários de código desnecessários
- Sem arredondamento de SG jamais
- Lógica de negócio sempre nos Services, nunca no Controller
- Google Analytics via variável de ambiente `GOOGLE_ANALYTICS_ID` (atualmente vazia)