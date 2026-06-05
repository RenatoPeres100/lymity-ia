# AI Execution Standardization

## Regra Central

**Nenhuma geração de IA acontece fora deste fluxo:**

```
AgentTask ativa
→ AiEmployee ativo (status='active')
→ Brand Context ativo e completo
→ AIExecutionGuardService (valida tudo)
→ AiExecutionContext (snapshot)
→ StructuredPromptBuilderService (prompt com Brand Context + instruções completas)
→ GoogleGeminiProvider (JSON mode, temperature=0.35)
→ AIJsonResponseNormalizerService (limpa control chars, fences, BOM)
→ Retry se JSON inválido (1x com temperatura 0.1)
→ AIContentPayloadValidatorService (valida campos obrigatórios)
→ GeneratedContentPackage (pacote completo)
→ ApprovalRequest (aprovação obrigatória)
→ BlogPost/SocialPost com status=pending_approval
```

---

## AIJsonResponseNormalizerService

**Arquivo:** `app/Services/AI/Response/AIJsonResponseNormalizerService.php`

Resolve os problemas mais comuns da resposta do Gemini:

| Problema | Solução |
|----------|---------|
| ` ```json ... ``` ` | `stripCodeFences()` |
| Texto antes/depois do JSON | `extractJsonCandidate()` |
| Newlines literais dentro de strings | `removeInvalidControlCharacters()` |
| BOM UTF-8, aspas curvas | `fixCommonEncodingIssues()` |
| JSON irrecuperável | Lança `AIInvalidJsonResponseException` |

## Retry de JSON Inválido

No `AgentTaskExecutionService::executeTextGeneration()`:

1. Gera texto → tenta normalizar
2. Se falhar → 1 retry pedindo correção do JSON (temperature=0.1)
3. Se retry falhar → marca AgentTaskRun como `failed` com mensagem clara
4. Nunca entra em loop

## AIExecutionGuardService

**Arquivo:** `app/Services/AI/Execution/AIExecutionGuardService.php`

Garante antes de qualquer geração:
- Task existe e não está archived/disabled
- Employee existe e `status='active'`
- Brand Context existe, ativo e completo
- Lança RuntimeException com mensagem clara se qualquer check falhar

## Feature Flags

```php
// config/features.php
'agent_tasks_menu'           => true,   // Menu Tarefas Operacionais
'ai_memory_menu'             => true,   // Menu Memória IA
'ai_usage_menu'              => true,   // Menu Uso/Consumo
'legacy_ai_tasks_menu'       => false,  // Oculta AiTasks antigas
'legacy_ai_schedules_menu'   => false,  // Oculta AiWorkSchedules
'legacy_agent_routines_menu' => false,  // Oculta Rotinas legadas
'auto_publish_without_approval' => false,  // NUNCA publicar sem aprovação
'mock_content_generation'    => false,  // NUNCA conteúdo fake
```

## Comandos

```bash
# Diagnosticar o motor completo
php artisan agents:diagnose-execution-engine

# Diagnosticar uma tarefa específica
php artisan agents:diagnose-task {id}

# Verificar e corrigir consistência
php artisan agents:repair-operational-consistency --dry-run
php artisan agents:repair-operational-consistency --fix

# Reconstruir contextos compactos
php artisan agents:rebuild-compact-contexts

# Testar o normalizador de JSON
php artisan ai:test-json-normalizer

# Executar tarefas vencidas
php artisan agents:run-due-tasks
php artisan agents:run-due-tasks --task=ID --force
```

## Erros Comuns

| Erro | Causa | Solução |
|------|-------|---------|
| "Control character error" | HTML com newlines literais no JSON | Corrigido pelo normalizer + retry |
| "Brand Context ativo é obrigatório" | Brand Context não configurado | Criar em /admin/agency/brand-context |
| "Toda geração precisa de tarefa" | Tentativa de geração sem AgentTask | Criar AgentTask antes |
| "Funcionário IA não está ativo" | Employee com status != active | Ativar o funcionário IA |
| "type ENUM truncated" | Valor errado no INSERT | Corrigido: usa 'agency'/'client' |
| "approval_type ENUM truncated" | Valor errado no INSERT | Corrigido: usa 'ai_task' |

## Validadores de Payload

`AIContentPayloadValidatorService` garante campos obrigatórios:

- **Blog Post**: title, content_html OU content_markdown, slug, excerpt, seo_title, seo_description, focus_keyword
- **Instagram Post**: title, caption, hashtags, cta, image_prompt
- **Carousel**: title, caption, slides[] com headline por slide

Normaliza automaticamente: slugs, hashtags string→array, markdown→HTML.
