---
name: code-review-bier-agent
description: Specialized code review agent for Teddy Bier Tools — validates PHP 8.4 patterns, architecture layers, SG precision, Vue 3 setup, FormRequest validation, and security.
metadata:
  type: reference
---

# Code Review Bier Agent Implementation

## Overview

Criado agente `code-review-bier-agent` com checklist estruturado para validar:

1. **PHP 8.4 + Type Hints** — `declare(strict_types=1)`, type hints obrigatórios
2. **Arquitetura de Camadas** — Controller → Service (static) → Cálculo
3. **SG Precision** — 3 casas decimais no backend, sem arredondamento
4. **Vue 3 & Inertia** — `<script setup>` obrigatório, Inertia responses
5. **Validação** — FormRequest com mensagens PT-BR, nunca inline
6. **useDecimalInput** — Composable para campos decimais PT-BR
7. **Segurança** — Validação em boundary, logs seguros, sem SQL injection
8. **Componentes Tailwind** — `CalculadoraCard.vue`, `SgInput.vue`

## Checklist Verificado

- ✅ PHP syntax via `php -l`
- ✅ Ausência de `declare(strict_types=1)` — corrige automaticamente
- ✅ Type hints ausentes — alerta ou corrige
- ✅ Lógica no Controller — rejeita
- ✅ Service com estado — rejeita
- ✅ SG com arredondamento — rejeita crítico
- ✅ Validação inline — refatora para FormRequest
- ✅ Vue Options API — converte para `<script setup>`
- ✅ Retorno Blade em lugar de Inertia — rejeita
- ✅ Comentários com issue numbers — remove

## Diferenciais vs. Modelo PNCP

| Aspecto | PNCP | Bier Tools |
|---------|------|-----------|
| **PHP Version** | 7.3 | 8.4 |
| **Type Hints** | Recomendado | OBRIGATÓRIO |
| **Camadas** | Controller → Trait → Service → Guzzle | Controller → Service → Calc |
| **State Management** | Polimorfismo `pncpable` | Pinia + localStorage |
| **Frontend** | Backpack 3.5 (admin) | Vue 3 + Inertia + Tailwind |
| **Foco Crítico** | JWT, compliance | SG precision, formatação PT-BR |

## Como Usar

```bash
# Revisar todas as mudanças
/code-review

# Revisar com nível custom (low/medium/high/ultra)
/code-review high
```

## Arquivos do Agente

- `.claude/agents/code-review-bier-agent.md` — definição completa com checklist