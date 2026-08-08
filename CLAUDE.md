# Teddy Bier Tools — CLAUDE.md

Ferramentas de cálculo para cervejeiros artesanais. Laravel 11 + Inertia.js + Vue 3 + Tailwind CSS 4.

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 11, PHP 8.4-FPM |
| Frontend | Vue 3 (`<script setup>`), Inertia.js 2 |
| CSS | Tailwind CSS 4 (`@tailwindcss/vite`) |
| Build | Vite 8 |
| Estado | Pinia 3 + `useLocalStorage` |
| Rotas Vue | Ziggy |
| HTTP | axios (`Accept: application/json` global) |
| DB | MySQL 8, Redis 7 |

## Docker

| Container | Porta host |
|-----------|-----------|
| `bier_nginx` | 8010 |
| `bier_mysql` | 3307 (interno: host `mysql`, porta `3306`) |
| `bier_redis` | 6380 |
| `bier_node` | 5173 (Vite dev server / HMR) |

```bash
docker compose up -d                      # sobe tudo, inclusive o Vite
docker exec bier_app php artisan migrate
docker exec bier_app php artisan optimize:clear
```

Todos os containers usam `restart: unless-stopped` — sobem sozinhos quando o Docker inicia.

**Assets:** com o `bier_node` rodando, alterações em `.vue`/`.css` refletem via HMR — **não** rode `npm run build` em desenvolvimento. O `public/hot` (criado pelo Vite) faz o Laravel apontar os assets para `http://localhost:5173`; o container o remove ao parar.

Para produção: `docker exec bier_app npm run build`.

Página em branco → `public/hot` órfão (container morto à força). `rm public/hot` e recarregue, ou `docker compose up -d node`.

## Ferramentas e Services

Um controller por funcionalidade (`show()` GET + `calcular()` POST). Rotas planas sem prefixo de grupo.

### DensidadeService
| Rota | Controller | Descrição |
|------|-----------|-----------|
| `densidade` | `DensidadeController` | SG ↔ Brix ↔ Plato — `calcularDensidade()` |
| `densimetro` | `DensimetroController` | NBS/NIST: `SG×(fatorNBS(T)/fatorNBS(20°C))` — `corrigirDensimetro()` |
| `refratometro` | `RefratometroController` | Novotný: `0.00628×CG−0.0025×OG+1.0013` — `corrigirRefratometro()` |
| `percentual-acucar` | `PercentualAcucarController` | OG sem açúcar dado % — `calcularPercentualAcucar()` |

Fórmula SG→Brix: `((182.4601×SG−775.6821)×SG+1262.7794)×SG−669.5622`

### CarbonacaoService
| Rota | Controller | Descrição |
|------|-----------|-----------|
| `pressao` | `PressaoController` | Bar ↔ PSI — `calcularPressao()` |
| `pressao-temperatura` | `PressaoTemperaturaController` | CO₂+T°C → bar — `calcularPressaoCarbonatacao()` |
| `priming` | `PrimingController` | CO₂ residual, 5 açúcares, distribuição/garrafa — `calcularPriming()` |

Constante: `PSI_POR_BAR=14.5037738007` | `FATORES_ACUCAR`: sucrose 2.0, dextrose mono 2.09, dextrose anidra 1.89, DME 2.73, mel 2.69

### AlcoolService
| Rota | Controller | Descrição |
|------|-----------|-----------|
| `abv` | `AbvController` | `(OG−FG)×131` |
| `diluicao-alcoolica` | `DiluicaoAlcoolicaController` | Água para reduzir ABV |
| `adicao-alcoolica` | `AdicaoAlcoolicaController` | Álcool para elevar ABV |

### LeveduraService
| Rota | Controller | Descrição |
|------|-----------|-----------|
| `levedura` | `LeveduraController` | `ceil(células×1.087/concentração)` |

Constante: `CELULAS_POR_GRAMA=1.087`

### ExtracaoService
| Rota | Controller | Descrição |
|------|-----------|-----------|
| `extracao-frio` | `ExtracaoFrioController` | Água por peso de malte p/ extração a frio de maltes escuros — `1.9×g/454` L — `calcularExtracaoFrio()` |

### EquipamentoService
| Rota | Controller | Descrição |
|------|-----------|-----------|
| `volume-mosto` | `VolumeMostoController` | `π×r²×h/1000−perda` |
| `motor` | `MotorController` | `n₁×D₁=n₂×D₂`, 1 campo em branco resolvido |

### TemperaturaService
| Rota | Controller | Descrição |
|------|-----------|-----------|
| `temperatura` | `TemperaturaController` | °C ↔ °F |

### CorService
| Rota | Controller | Descrição |
|------|-----------|-----------|
| `cor` | `CorController` | EBC ↔ SRM ↔ Lovibond |

Fórmulas: `EBC↔SRM: ×0.508/×1.97` | `SRM↔Lov: (SRM+0.76)/1.3546`

## Arquitetura

**Rotas:** `GET /funcionalidade → Inertia::render()` | `POST /funcionalidade → response()->json()` — sem prefixos de grupo  
**Controllers:** 1 por funcionalidade, métodos `show()` (GET) e `calcular()` (POST)  
**Services:** agrupados por domínio cervejeiro (não por tipo de operação)  
**Camadas:** Controller (orquestra) → Service (métodos `static`, lógica pura) — sem Repository  
**Cálculos:** sempre axios POST → JSON; nunca Inertia forms  
**Validação:** sempre em `FormRequest`; mensagens em PT-BR; erros de negócio → chave `geral` em `withValidator()`

**SG:** 3 casas decimais, sem arredondamento jamais. Formatação só no frontend via `SgInput.vue`. Usuário digita `1048` → exibe `1.048`. Backend processa como float.

**Inputs decimais PT-BR:** usar composable `useDecimalInput` (`resources/js/composables/useDecimalInput.js`) para qualquer campo decimal com auto-formatação ao digitar.  
API: `{ display, numeric, onInput, reset, set }` — `display` → `:value` | `numeric` → payload axios | `onInput` → `@input` | `reset()` → em `limpar()` | `set(valor)` → exibir resultado no input  
Opção `casas`: `useDecimalInput()` = 1 casa (`XX,X`); `useDecimalInput({ casas: 2 })` = 2 casas (`XX,XX`) — ao digitar `123` exibe `1,23`, ao digitar `1234` exibe `12,34`  
Funções exportadas: `formataUmaCasaDecimal(digits)` | `formataDuasCasasDecimais(digits)` | `formatarPtBr(value, decimais)` | `parsePtBr(str)`  
Usa: `Correcao/Densimetro.vue` (1 casa), `Conversao/Pressao.vue` (2 casas) | Candidatas: `Correcao/PressaoTemperatura.vue`, `Conversao/Temperatura.vue`, `Calculo/Priming.vue`

## Componentes

| Componente | Props / Uso |
|-----------|------------|
| `CalculadoraCard.vue` | `titulo`, `descricao` — wrapper de card |
| `SgInput.vue` | `v-model` string, auto-formata 1048→1.048 |

## Config / Estado

- **DB:** `DB_HOST=mysql` / `DB_PORT=3306` (não localhost:3307)
- **Validação JSON:** `bootstrap/app.php` → `shouldRenderJsonWhen` inclui `|| $request->wantsJson()`
- **axios:** `Accept: application/json` + `X-Requested-With` configurados globalmente em `app.js`
- **Ziggy:** `@routes` obrigatório em `app.blade.php`
- **Middleware:** `HandleInertiaRequests` (share props) + `TrackPageView` (registra em `page_views`)
- **Panelas:** `stores/panelas.js` → `useLocalStorage('bier_panelas_v1', [])` — campos: `id`, `nome`, `diametro`, `alturaPanela`, `perda`

## PHP

- `declare(strict_types=1)` em todos os arquivos
- Type hints obrigatórios em parâmetros e retorno; nullable explícito (`?float`)
- Services: métodos `static`, retornam `array` ou scalar, nomeados `calcular*()` / `converter*()` / `corrigir*()`
- Controllers: sem lógica de negócio, sem `dd()`/`var_dump()`
- Migrations: sempre com `down()` funcional

## Vue

- `<script setup>` obrigatório; sem Options API
- `reactive()` para form, `ref()` para escalares (resultado, loading, erro)
- `key` obrigatório em `v-for`

## Code Review Obrigatório

**Todas as implementações DEVEM passar por code-review antes de serem consideradas completas.**

### Quando Executar

- ✅ Após implementar uma nova funcionalidade (nova ferramenta de cálculo)
- ✅ Antes de fazer commit de mudanças em `app/Services/`, `app/Http/Controllers/`, `resources/js/`
- ✅ Antes de abrir PR para `main`

### Como Executar

```bash
# Revisar todas as mudanças no branch atual
/code-review

# Revisar com nível alto (mais detalhado)
/code-review high

# Revisar PR específica (se já aberta)
/code-review ultra 123
```

### O Que o Agent Valida

O agente `code-review-bier-agent` verifica:

**Backend (PHP 8.4):**
- `declare(strict_types=1)` obrigatório
- Type hints completos (parâmetros + retorno)
- Arquitetura: Controller → Service (static) → Cálculos
- **SG precision: 3 casas decimais, sem arredondamento**
- Validação sempre em FormRequest (PT-BR)
- Segurança: logs seguros, validação em boundary

**Frontend (Vue 3 + Inertia):**
- Vue 3 com `<script setup>` (nunca Options API)
- Inertia responses (GET → render, POST → json)
- useDecimalInput para campos decimais PT-BR
- Componentes reutilizáveis (CalculadoraCard, SgInput)

**Resultado:**
- ✅ APROVADO — todos os padrões atendidos
- ⚠️ APROVADO COM RESSALVAS — aprovado após correções automáticas
- ❌ REJEITADO — violações críticas que precisam correção manual

## Preferências

- Respostas em **português brasileiro**
- Sem comentários de código desnecessários
- `GOOGLE_ANALYTICS_ID` via `.env` (atualmente vazia)