# AI Execution Standardization

## Regra Central

**Nenhuma geração de IA acontece fora deste fluxo:**

```
AgentTask ativa
→ AiEmployee ativo (status='active')
→ Brand Context ativo e completo
→ AIExecutionGuardService (valida tudo)
→ AiExecutionContext (snapshot)
→ StructuredPromptBuilderService (prompt com Brand Context + instruções JSON-only)
→ GoogleGeminiProvider (JSON mode, temperature=0.35)
→ AIJsonResponseNormalizerService (limpa control chars, fences, BOM, aspas curvas)
→ Retry se JSON inválido (1x com temperatura 0.1)
→ AIContentPayloadValidatorService (valida e normaliza aliases de conteúdo)
→ GeneratedContentPackage (pacote completo)
→ ApprovalRequest (aprovação obrigatória)
→ BlogPost/SocialPost com status=pending_approval
```

---

## AIJsonResponseNormalizerService

**Arquivo:** `app/Services/AI/Response/AIJsonResponseNormalizerService.php`

Resolve os problemas mais comuns da resposta do Gemini:

| Problema | Solução |
|---|---|
| JSON dentro de ` ```json ` | `stripCodeFences()` |
| Texto antes/depois do JSON | `extractJsonCandidate()` |
| BOM UTF-8 | `fixCommonEncodingIssues()` |
| Caracteres de controle literais (0x00-0x1F) | `removeInvalidControlCharacters()` |
| Aspas curvas " " ' ' | `fixCommonEncodingIssues()` |
| Zero-width spaces | `fixCommonEncodingIssues()` |
| JSON irrecuperável | Lança `AIInvalidJsonResponseException` |

### Teste

```bash
php artisan ai:test-json-normalizer
```

Casos 1-6: PASS (JSON normalizado). Caso 7 (irrecuperável): falha controlada.

---

## AIContentPayloadValidatorService

**Arquivo:** `app/Services/AI/Response/AIContentPayloadValidatorService.php`

### Aliases aceitos para conteúdo de blog post

O Gemini pode retornar o conteúdo em qualquer campo abaixo (em ordem de preferência):

1. `content_markdown` ← **preferido e exigido no prompt**
2. `content_html`
3. `content`
4. `body`
5. `article`
6. `article_content`
7. `text`
8. `html`
9. `markdown`
10. `post_content`

### Saída garantida

Independente do alias recebido, o validator **sempre retorna** `content_html` e `content_markdown` preenchidos:

- Se `content_markdown` → gera `content_html` via markdown-to-html
- Se `content_html` / `html` → mantém `content_html`, gera `content_markdown` via `strip_tags`
- Outros aliases → trata como markdown, gera ambos

### Teste

```bash
php artisan ai:test-payload-validator
```

Todos os 10 aliases passam e retornam ambos os campos.

---

## StructuredPromptBuilderService

**Arquivo:** `app/Services/AI/Prompt/StructuredPromptBuilderService.php`

O prompt de blog inclui bloco de instrução explícito:

```
RESPONDA APENAS JSON VÁLIDO.
NÃO use markdown fora do JSON.
NÃO use bloco ```json.
NÃO escreva explicações antes ou depois do JSON.
O campo content_markdown é obrigatório.
Escape corretamente quebras de linha dentro das strings como \n.
Não use caracteres de controle.
Não use vírgulas finais.
```

---

## AIExecutionGuardService

**Arquivo:** `app/Services/AI/Execution/AIExecutionGuardService.php`

Valida antes de toda execução:

- `AgentTask` obrigatória e não arquivada/desativada
- `AiEmployee` presente e com `status=active`
- `BrandContext` presente, ativo e completo

### Mensagens de bloqueio

| Situação | Mensagem |
|---|---|
| Sem AgentTask | "Toda geração de IA precisa estar vinculada a uma tarefa operacional." |
| Sem Brand Context | "Brand Context ativo é obrigatório para gerar conteúdo com IA." |
| Funcionário inativo | "Funcionário IA '{nome}' não está ativo (status: ...)." |

---

## Retry de JSON Inválido

Implementado em `AgentTaskExecutionService::executeTextGeneration()`:

1. Primeira chamada ao Gemini → `AIJsonResponseNormalizerService`
2. Se falhar: loga `ai.generation.failed_json`, envia retry com mensagem de correção
3. Se retry falhar: lança `RuntimeException` e marca `AgentTaskRun.status=failed`
4. Não cria BlogPost/SocialPost com JSON quebrado

---

## Geração Solta — Bloqueada

Os seguintes controllers foram atualizados para bloquear geração sem AgentTask:

- `BlogAiController::store()` → redireciona para `/admin/agent-tasks` com aviso
- `SocialAiController::generate()` → redireciona para `/admin/agent-tasks` com aviso

---

## Feature Flags — Menu

| Flag | Padrão | Descrição |
|---|---|---|
| `agent_tasks_menu` | `true` | Tarefas Operacionais no menu |
| `ai_memory_menu` | `true` | Memória IA no menu |
| `ai_usage_menu` | `true` | Uso IA no menu |
| `ai_execution_logs_menu` | `true` | Logs IA no menu Funcionários IA |
| `legacy_ai_tasks_menu` | `false` | AiTasks antigas — ocultas |
| `legacy_ai_schedules_menu` | `false` | Schedules legados — ocultos |
| `legacy_agent_routines_menu` | `false` | Rotinas legadas — ocultas |

---

## Commands de Diagnóstico

```bash
# Diagnóstico completo do motor
php artisan agents:diagnose-execution-engine

# Reparo de consistência (dry-run por padrão)
php artisan agents:repair-operational-consistency
php artisan agents:repair-operational-consistency --fix

# Testar normalizador JSON
php artisan ai:test-json-normalizer

# Testar validator de payload
php artisan ai:test-payload-validator
```

---

## Como resolver erro JSON do Gemini

Se aparecer `content_html ou content_markdown obrigatório` nos logs:

1. Verifique `storage/logs/laravel.log` — campo `[PayloadValidator]` mostra os campos recebidos
2. Execute `php artisan ai:test-payload-validator` para confirmar que o validator está OK
3. Verifique se o prompt contém o bloco de instruções JSON-only (`php artisan agents:diagnose-task <id>`)
4. Se o Gemini retornar JSON inválido, o retry automático tenta corrigir — verifique se `AgentTaskRun.error_message` contém "retry"
5. Ajuste `StructuredPromptBuilderService::buildBlogOutputRules()` se necessário
