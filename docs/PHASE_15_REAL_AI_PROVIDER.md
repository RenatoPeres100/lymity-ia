# Phase 15 — Real AI Provider Integration

## Objetivo

Integrar providers reais de IA (OpenAI e Anthropic/Claude) à plataforma, mantendo compatibilidade total com o mock provider já existente. Adicionar controles de custo, limites de uso, logging granular e telas administrativas.

## Arquitetura

### Interface `AiProviderInterface`

```
app/Services/Ai/AiProviderInterface.php
```

Contrato que todos os providers devem implementar:

| Método | Retorno | Descrição |
|--------|---------|-----------|
| `generate(array $payload)` | `array` | Gera texto; retorna resultado padronizado |
| `testConnection()` | `array` | Testa conectividade com o provider |
| `supportsStructuredOutput()` | `bool` | Suporte a JSON estruturado |
| `providerName()` | `string` | Nome do provider (mock/openai/claude) |

### Resultado padronizado (todos providers)

```php
[
    'success'          => bool,
    'provider'         => string,
    'model'            => string,
    'prompt_preview'   => string|null,   // 100 chars, nunca a chave API
    'response'         => string|null,
    'response_summary' => string|null,   // 300 chars
    'input_tokens'     => int|null,
    'output_tokens'    => int|null,
    'total_tokens'     => int|null,
    'estimated_cost'   => float|null,    // USD
    'error_message'    => string|null,
    'raw_response'     => array|null,    // metadata, sem secrets
]
```

### Providers Implementados

| Provider | Arquivo | Endpoint |
|----------|---------|----------|
| Mock | `MockAiProvider.php` | Nenhum (local) |
| OpenAI | `OpenAiProvider.php` | `https://api.openai.com/v1/chat/completions` |
| Claude/Anthropic | `ClaudeProvider.php` | `https://api.anthropic.com/v1/messages` |

### AiProviderManager

`app/Services/Ai/AiProviderManager.php`

Resolve o provider ativo via `AI_PROVIDER` no `.env`:

```php
public function provider(): AiProviderInterface {
    return match (strtolower(config('ai.provider', 'mock'))) {
        'openai' => new OpenAiProvider(),
        'claude' => new ClaudeProvider(),
        default  => new MockAiProvider(),
    };
}
```

### AiGenerationService

`app/Services/Ai/AiGenerationService.php`

Serviço central de geração:

- `generateForTask(AiTask $task)`: verifica limites, constrói payload rico (brand profile, memórias, knowledge base), chama provider, registra `AiProviderCall`, atualiza task
- `generateDirect(array $payload)`: chamada direta para testes e integrações ad-hoc

### AiCostService

`app/Services/Ai/AiCostService.php`

Controle de limites e custos:

- `canRunTask()`: verifica limite diário e orçamento mensal antes de chamar o provider
- `getUsageSummary()`: retorna estatísticas consolidadas para o dashboard
- `isDailyLimitExceeded()`, `isMonthlyBudgetExceeded()`

## Banco de Dados

### Tabela `ai_provider_calls`

Registra cada chamada ao provider (real ou mock):

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | bigint | PK |
| `ai_employee_id` | FK nullable | Funcionário IA que originou a chamada |
| `ai_task_id` | FK nullable | Tarefa associada |
| `client_id` | FK nullable | Cliente |
| `user_id` | FK nullable | Usuário que disparou |
| `provider` | string(30) | mock/openai/claude |
| `model` | string(100) | Nome do modelo usado |
| `prompt_hash` | string(64) | SHA-256 do prompt completo |
| `prompt_preview` | text | Primeiros 500 chars (sem secrets) |
| `response_summary` | text | Primeiros 300 chars da resposta |
| `status` | enum | success/failed |
| `input_tokens` | uint | Tokens de entrada |
| `output_tokens` | uint | Tokens de saída |
| `total_tokens` | uint | Total de tokens |
| `estimated_cost` | decimal(12,6) | Custo estimado em USD |
| `error_message` | text | Mensagem de erro (se failed) |
| `metadata` | json | Dados adicionais |
| `created_at` | timestamp | Sem `updated_at` |

## Configuração via `.env`

```bash
# Provider ativo
AI_PROVIDER=mock            # mock | openai | claude
AI_API_KEY=sk-...           # Nunca commitar

# Modelo e parâmetros
AI_MODEL=gpt-4o-mini        # ou claude-haiku-4-5-20251001
AI_MAX_TOKENS=1200
AI_TEMPERATURE=0.7

# Controle de custo
AI_MONTHLY_BUDGET_LIMIT=100 # USD, 0 = ilimitado
AI_DAILY_TASK_LIMIT=50      # chamadas reais/dia, 0 = ilimitado
AI_REAL_ENABLED=false       # Habilitar provider real

# Fallback
AI_FALLBACK_TO_MOCK=false   # Fallback para mock se real falhar
```

## Telas Administrativas

| Rota | URL | Descrição |
|------|-----|-----------|
| `admin.ai-settings.index` | `/admin/ai-settings` | Status do provider e configuração de limites |
| `admin.ai-settings.update` | `POST /admin/ai-settings` | Salva limites de uso |
| `admin.ai-settings.test` | `/admin/ai-settings/test` | Formulário de teste |
| `admin.ai-settings.test.run` | `POST /admin/ai-settings/test` | Executa teste de conexão/geração |
| `admin.ai-usage.index` | `/admin/ai-usage` | Histórico de chamadas com filtros e paginação |

## Segurança

- API key nunca é exibida em views, logs ou exceções
- `prompt_hash` usa SHA-256 (não reversível)
- `prompt_preview` truncado a 500 chars; instruções do sistema excluídas
- Limites diários e mensais bloqueiam chamadas antes de chegar ao provider
- Client isolation mantida: cada call associada ao client correto

## Ativação do Provider Real

1. Editar `.env` no servidor:
   ```bash
   AI_PROVIDER=openai
   AI_API_KEY=sk-proj-...
   AI_MODEL=gpt-4o-mini
   AI_REAL_ENABLED=true
   ```
2. Rodar `php artisan optimize:clear`
3. Acessar `/admin/ai-settings/test` → "Testar Conexão"
4. Se sucesso, "Testar Geração" para validar chamada completa
5. Monitorar `/admin/ai-usage` para acompanhar consumo e custo

## Estimativa de Custo por Modelo

| Provider | Modelo | Input (1K tokens) | Output (1K tokens) |
|----------|--------|-------------------|--------------------|
| OpenAI | gpt-4o-mini | $0.00015 | $0.00060 |
| OpenAI | gpt-4o | $0.00250 | $0.01000 |
| OpenAI | gpt-4-turbo | $0.01000 | $0.03000 |
| Claude | claude-haiku-4-5 | $0.00025 | $0.00125 |
| Claude | claude-sonnet-4 | $0.00300 | $0.01500 |
| Claude | claude-opus-4 | $0.01500 | $0.07500 |
| Mock | qualquer | $0 | $0 |
