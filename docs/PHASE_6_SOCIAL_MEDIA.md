# Fase 6 — Social Media e Calendário Editorial

## Resumo

Módulo completo de Social Media para a plataforma Lymity IA. Permite criar, aprovar, agendar e acompanhar posts de redes sociais, com geração de conteúdo por IA, calendário editorial visual e controle de canais.

## Modelos criados

| Modelo | Tabela | Descrição |
|---|---|---|
| `SocialChannel` | `social_channels` | Canais conectados (Instagram, LinkedIn, etc.) |
| `SocialPost` | `social_posts` | Posts com legenda, objetivo, formato e status |
| `SocialPostVariant` | `social_post_variants` | Versões por plataforma de cada post |
| `SocialCalendar` | `social_calendars` | Calendários editoriais mensais |
| `SocialContentBrief` | `social_content_briefs` | Briefings de conteúdo para a equipe/IA |

## Services

| Service | Responsabilidade |
|---|---|
| `SocialPostService` | CRUD de posts, aprovação, rejeição, variações |
| `SocialCalendarService` | Grid mensal, criação de calendários |
| `SocialPublishingService` | Agendamento, marcar como publicado |
| `SocialAiService` | Geração via IA, melhoria de posts, variações |

## Rotas Admin (`/admin/social/*`)

- `GET /admin/social` — Dashboard de Social Media
- `GET /admin/social/posts` — Listagem com filtros (status, cliente, objetivo)
- `GET /admin/social/posts/create` — Criar post manual
- `GET /admin/social/posts/{post}` — Visualizar post completo
- `GET /admin/social/posts/{post}/edit` — Editar post
- `POST /admin/social/posts/{post}/send-approval` — Enviar para aprovação
- `POST /admin/social/posts/{post}/approve` — Aprovar post
- `POST /admin/social/posts/{post}/reject` — Rejeitar post
- `PATCH /admin/social/posts/{post}/schedule` — Agendar data/hora
- `POST /admin/social/posts/{post}/mark-published` — Marcar como publicado
- `GET /admin/social/calendar` — Calendário visual mensal
- `GET /admin/social/channels` — Gerenciar canais sociais
- `GET /admin/social/briefs` — Briefings de conteúdo
- `GET /admin/social/ai/generate` — Gerar post com IA
- `GET /admin/social/ai/posts/{post}/variants` — Gerar variações por plataforma
- `GET /admin/social/ai/posts/{post}/improve` — Melhorar post com IA

## Rotas Cliente (`/client/social/*`)

- `GET /client/social/posts` — Meus Posts (filtráveis por status)
- `GET /client/social/posts/{post}` — Ver post com variações
- `GET /client/social/calendar` — Calendário mensal do cliente
- `GET /client/social/approvals` — Posts aguardando aprovação
- `POST /client/social/approvals/{id}/approve` — Aprovar post
- `POST /client/social/approvals/{id}/reject` — Rejeitar post

## Status de Posts

| Status | Label | Descrição |
|---|---|---|
| `draft` | Rascunho | Criado, ainda em edição |
| `pending_approval` | Aguardando aprovação | Enviado para revisão |
| `approved` | Aprovado | Pronto para agendar/publicar |
| `scheduled` | Agendado | Com data/hora definida |
| `published` | Publicado | Publicado (manual ou automático) |
| `rejected` | Rejeitado | Rejeitado, pode editar e reenviar |
| `archived` | Arquivado | Arquivado |

## Segurança — Tokens de Acesso

- `access_token` e `refresh_token` da tabela `social_channels` são protegidos por `protected $hidden` no modelo
- Seeders sempre deixam tokens como `null`
- Formulários de canal nunca exibem nem permitem editar tokens
- `SocialPublishingService::publish()` lança exceção proposital — integração real está planejada para Fase 7

## Geração IA

Três tipos de tarefa processados pelo `MockAiProvider`:
- `generate_social_post` — Gera legenda completa com hashtags e CTA
- `generate_social_variants` — Adapta caption para cada plataforma
- `improve_social_post` — Melhora texto existente com instruções

## Enums importantes

**Objective:** `awareness`, `engagement`, `leads`, `sales`, `authority`, `relationship`

**Content Type:** `feed`, `reels`, `story`, `carousel`, `short_video`, `article`, `thread`

**Channel Status:** `active`, `paused`, `disconnected`

**Platforms (variantes):** `instagram`, `facebook`, `linkedin`, `tiktok`, `threads`, `youtube`, `pinterest`

## Seeders

```bash
php artisan db:seed --class=SocialChannelSeeder
php artisan db:seed --class=SocialContentBriefSeeder
php artisan db:seed --class=SocialCalendarSeeder
php artisan db:seed --class=SocialPostSeeder
```

## Integração com Fase 5 (Aprovações)

- `SocialPostService::sendToApproval()` cria `ApprovalRequest` via `ApprovalService`
- `ApprovalService::syncSocialPostStatus()` sincroniza o status do post quando aprovação é atualizada
- `SocialAiService::createPostFromAiTask()` cria `ApprovalRequest` automaticamente quando `requires_approval=true`
- Cliente pode aprovar/rejeitar posts via `/client/social/approvals`

## Próxima Fase

**Fase 7** — Integração real com APIs de redes sociais (Meta API, LinkedIn API, TikTok API), publicação automática agendada via queue/cron, métricas de engajamento.
