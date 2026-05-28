# Real Blog Automation — Gemini Text Only

## Visão Geral

Pipeline real de automação de blog para a Lymity IA usando Google Gemini. Esta fase cobre exclusivamente **texto**. Imagens estão desativadas.

---

## Variáveis de Ambiente

```env
AI_PROVIDER=google
GEMINI_API_KEY=           # Sua chave da Google AI Studio
GEMINI_TEXT_MODEL=gemini-2.5-flash
GEMINI_TEXT_FALLBACK_MODEL=gemini-2.5-flash-lite
AI_ENABLE_GROUNDING=false
AI_ENABLE_CONTEXT_CACHE=false
AI_REQUIRE_APPROVAL=true
AI_DAILY_REQUEST_LIMIT=20
AI_MONTHLY_BUDGET_USD=20
AI_MAX_OUTPUT_TOKENS=3500
AI_TEMPERATURE=0.7
AI_TEXT_ONLY_MODE=true
```

---

## Fluxo do Blog

```
1. Admin configura BrandContext (/admin/agency/brand-context)
2. Blog Planner IA gera ContentBrief (pauta/briefing)
3. Blog Writer IA gera BlogPost (artigo completo)
4. BlogPost fica status: pending_approval
5. ApprovalRequest é criada automaticamente
6. Admin revisa, edita se quiser e aprova
7. Admin agenda data e horário
8. blog:publish-due publica automaticamente no horário
9. Post aparece em /blog/{slug}
10. Logs registrados em ai_task_logs, ai_usage_records, activity_logs
```

---

## BrandContext

Tabela: `agency_brand_contexts`
Model: `App\Models\AgencyBrandContext`
URL: `/admin/agency/brand-context`

Campos chave: `brand_name`, `positioning`, `target_audience`, `tone_of_voice`, `main_services`, `cta_default`, `content_guidelines`, `seo_rules`, `forbidden_terms`, `preferred_terms`, `active`

O `BrandContextService` fornece `getActiveAgencyContext()` e `getCompactContext()` para montar o contexto enviado ao Gemini.

---

## Blog Planner IA

- Slug: `blog-planner-ia`
- Provider: Google Gemini
- Função: gerar pauta, briefing, palavra-chave, outline, CTA
- Output: JSON com `title`, `topic`, `goal`, `audience`, `primary_keyword`, `secondary_keywords`, `funnel_stage`, `search_intent`, `outline[]`, `cta_suggestion`
- Não escreve artigo, não publica, não gera imagem

---

## Blog Writer IA

- Slug: `blog-writer-ia`
- Provider: Google Gemini
- Função: escrever artigo completo em português
- Output: JSON com `title`, `slug`, `subtitle`, `excerpt`, `seo_title`, `seo_description`, `focus_keyword`, `secondary_keywords`, `content_html`, `cta_final`
- Não publica, não gera imagem, artigo vai para `pending_approval`

---

## ContentBrief

Tabela: `content_briefs`
Model: `App\Models\ContentBrief`
URL: `/admin/blog/briefs`

Representa a pauta estratégica gerada pelo Planner. Contém outline, intenção de busca, funil, palavra-chave.

---

## Como Gerar Briefing

### Via painel
1. Acesse `/admin/blog/automation`
2. Preencha tópico, keyword e objetivo
3. Clique "Gerar Briefing com IA"

### Via command line
```bash
php artisan blog:generate-brief \
  --topic="Como a IA está mudando o marketing" \
  --keyword="agência digital com IA" \
  --goal="posicionar a Lymity IA como autoridade"
```

---

## Como Gerar Artigo

### Via painel
1. Acesse `/admin/blog/briefs`
2. Clique no briefing desejado
3. Clique "Gerar Artigo com IA"

### Via command line
```bash
# Pegar o ID do briefing
php artisan tinker --execute="echo App\Models\ContentBrief::latest('id')->first()->id;"

# Gerar artigo
php artisan blog:generate-draft {BRIEF_ID}
```

---

## Como Aprovar

1. Acesse `/admin/blog/approvals`
2. Clique em "Revisar" no artigo desejado
3. Leia o preview e os metadados SEO
4. Clique "Aprovar Artigo"

Ou via service:
```bash
php artisan tinker --execute="
\$admin = App\Models\User::where('email','admin@lymity.local')->first();
\$post = App\Models\BlogPost::latest('id')->first();
app(App\Services\Blog\BlogPipelineService::class)->approvePost(\$post, \$admin);
"
```

---

## Como Agendar

Após aprovar, no `/admin/blog/approvals/{approval}`:
- Selecione data e hora de publicação
- Clique "Agendar"

Ou via service:
```bash
php artisan tinker --execute="
\$admin = App\Models\User::where('email','admin@lymity.local')->first();
\$post = App\Models\BlogPost::latest('id')->first();
app(App\Services\Blog\BlogPipelineService::class)->schedulePost(\$post, '2026-06-01 09:00:00', \$admin);
"
```

---

## Como Publicar

Publicação automática via scheduler (roda a cada minuto):
```bash
php artisan blog:publish-due
```

Para publicar imediatamente (post aprovado):
```bash
# Via painel: /admin/blog/pipeline -> Publicar agora
# Via service (agendado no passado)
php artisan tinker --execute="
\$post = App\Models\BlogPost::latest('id')->first();
app(App\Services\Blog\BlogPipelineService::class)->schedulePost(\$post, now()->subMinute(), auth()->user() ?? App\Models\User::first());
"
php artisan blog:publish-due
```

---

## Como Diagnosticar

```bash
php artisan blog:diagnose-ai
```

Verifica: AI_PROVIDER, GEMINI_API_KEY, modelos, BrandContext, agentes, limites, image pipeline.

---

## Como Controlar Custo

O `AICostGuardService` verifica antes de cada geração:
- `AI_DAILY_REQUEST_LIMIT`: máximo de chamadas por dia (padrão: 20)
- `AI_MONTHLY_BUDGET_USD`: orçamento mensal em USD (padrão: $20)

Se excedido: erro controlado com mensagem clara. Nunca falha silenciosamente.

---

## O Que Está Desativado Nesta Fase

| Feature | Status |
|---------|--------|
| Geração de imagem | Desativado |
| Cover Image IA | Desativado |
| Featured Image IA | Desativado |
| Instagram pipeline | Desativado |
| Google Ads | Desativado |
| Meta Ads | Desativado |
| Propostas / Orçamentos | Desativado |
| CRM / SDR | Desativado |
| Demo mode | Desativado |

### Por que imagens estão desativadas?
Esta fase tem foco exclusivo em qualidade de texto, custo controlado e pipeline de aprovação. A geração de imagens requer uma segunda chamada de API (Imagen), aumenta custo e complexidade. Será implementada na fase seguinte após validação do pipeline de texto.

---

## Próximos Passos para Imagem (não implementar agora)

1. Adicionar `Cover Image IA` como agente
2. Integrar com Google Imagen API
3. Adicionar campo `featured_image` ao fluxo de aprovação
4. Criar step "Gerar imagem de capa" após aprovação do texto
5. Feature flag: `blog_image_pipeline=true`
