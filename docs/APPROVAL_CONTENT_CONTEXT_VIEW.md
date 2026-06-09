# Approval Content & Context View

## Visão geral

A tela de aprovação (`/admin/approvals/{id}`) exibe o conteúdo gerado pelos Funcionários IA de forma legível e operacional, dividida em 5 abas:

1. **Conteúdo Gerado** — artigo/post/carousel completo, SEO, CTA, palavras-chave
2. **Imagem / Criativo** — preview da imagem gerada, assets, status
3. **Contexto Usado** — Brand Context, Tarefa IA, Funcionário, Memórias, Fontes, Prompt preview
4. **Execução e Logs** — provider, modelo, tokens, custo, duração, logs de atividade
5. **Payload Técnico** — JSON bruto colapsado, apenas para debug

---

## Como a aprovação exibe conteúdo

O `ApprovalDisplayService` (em `app/Services/Approvals/ApprovalDisplayService.php`) resolve todos os relacionamentos e formata os dados antes de enviar para a view.

O controller injeta o service:

```php
$display = app(ApprovalDisplayService::class)->build($approvalRequest);
return view('admin.approvals.show', compact('approvalRequest', 'display'));
```

O array `$display` contém:

| Chave       | Descrição |
|-------------|-----------|
| `package`   | `GeneratedContentPackage` resolvido, ou null |
| `entity`    | `BlogPost` ou `SocialPost` relacionado, ou null |
| `task`      | `AgentTask` usado, ou null |
| `employee`  | `AiEmployee` responsável, ou null |
| `run`       | `AgentTaskRun` correspondente, ou null |
| `context`   | `AiExecutionContext` da execução, ou null |
| `content`   | Campos formatados do conteúdo (título, body, SEO, CTA...) |
| `visual`    | Imagens, assets, status visual |
| `sources`   | Fontes externas usadas |
| `ctx_data`  | Brand Context, Task Context, memórias, prompt preview |
| `usage`     | Provider, modelo, tokens, custo, duração |
| `debug`     | Payloads brutos para debug |

---

## Como o contexto é resolvido

O `GeneratedContentPackage` é localizado por 4 estratégias, em ordem:

1. `approvable_type = GeneratedContentPackage` → usa `approvable` diretamente
2. `payload['generated_content_package_id']` ou `payload['package_id']` → busca por ID
3. `approvable` é BlogPost ou SocialPost → busca package por `generated_entity_type/id`
4. `packages.approval_request_id = approval.id` → busca direta

A `AiExecutionContext` é localizada via `AgentTaskRun.execution_context_id`.

---

## Diferença entre conteúdo, contexto e payload técnico

- **Conteúdo**: o que foi gerado (artigo, post, carousel) — exibido como texto legível
- **Contexto**: como foi gerado (Brand Context, instruções da tarefa, memórias, prompt) — exibido como seções formatadas
- **Payload técnico**: os dados brutos em JSON — disponível apenas na aba de debug, colapsado por padrão

---

## O que admin vê

- Tudo: conteúdo completo, contexto técnico, memórias, prompt preview sanitizado, custo, logs internos, payload bruto

## O que cliente vê

A view cliente (`resources/views/client/approvals/show.blade.php`) é uma versão simplificada:

- Conteúdo gerado completo (artigo, legenda, hashtags, CTA)
- Imagem gerada (se disponível)
- Contexto resumido (funcionário IA, tarefa)
- Botões de decisão
- Comentários

O cliente **NÃO** vê: prompt completo, custo técnico detalhado, payload bruto, logs internos sensíveis.

---

## Diagnóstico: package sem conteúdo

Se a tela mostrar "Conteúdo não encontrado no pacote gerado":

1. Verifique `GeneratedContentPackage.content_payload`:
```sql
SELECT id, title, content_payload IS NOT NULL as has_content FROM generated_content_packages ORDER BY id DESC LIMIT 5;
```

2. Se `content_payload` for nulo, o problema ocorreu durante a geração. Verifique `AgentTaskRun`:
```sql
SELECT id, status, error_message FROM agent_task_runs ORDER BY id DESC LIMIT 5;
```

3. Verifique o log da execução:
```bash
tail -n 100 storage/logs/laravel.log | grep "RunAgentTaskJob\|GenerateContent"
```

---

## Diagnóstico: execution context ausente

Se a aba "Contexto Usado" mostrar "Contexto de execução não encontrado":

1. Verifique se o `AgentTaskRun` tem `execution_context_id`:
```sql
SELECT id, execution_context_id FROM agent_task_runs ORDER BY id DESC LIMIT 5;
```

2. Se `execution_context_id` for nulo, o contexto não foi criado durante a execução. Isso pode ocorrer em execuções antigas ou com falha no `PromptBuilderService`.

3. Verifique `ai_execution_contexts`:
```sql
SELECT id, agent_task_id, provider, model, status FROM ai_execution_contexts ORDER BY id DESC LIMIT 5;
```
