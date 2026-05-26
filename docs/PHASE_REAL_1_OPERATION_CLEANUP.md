# Real Phase 1 — Limpeza Operacional

## Objetivo

Remover ruído demo/mock da interface e focar nas funcionalidades reais de operação da agência:

- Blog da agência
- Posts Instagram + carrosséis
- Aprovações
- Funcionários IA: Social Media IA, Copywriter IA, Blog Writer IA
- Fila de publicação
- Logs operacionais

---

## Feature Flags

Arquivo: `config/features.php`

| Flag                  | Status  | Descrição                                   |
|-----------------------|---------|---------------------------------------------|
| `blog_pipeline`       | `true`  | Blog da agência visível no sidebar          |
| `instagram_pipeline`  | `true`  | Posts Instagram + fila + calendário         |
| `ai_social_media`     | `true`  | Funcionário IA Social Media ativo           |
| `ai_copywriter`       | `true`  | Funcionário IA Copywriter ativo             |
| `ai_blog_writer`      | `true`  | Funcionário IA Blog Writer ativo            |
| `ads_module`          | `false` | Módulo de Ads ocultado                      |
| `proposals_module`    | `false` | Módulo de Propostas ocultado                |
| `budgets_module`      | `false` | Módulo de Orçamentos ocultado               |
| `contracts_module`    | `false` | Módulo de Contratos ocultado                |
| `crm_module`          | `false` | Módulo CRM ocultado                         |
| `sdr_module`          | `false` | SDR IA ocultado                             |
| `reports_fake_module` | `false` | Relatórios fake ocultados                   |
| `cases_demo_module`   | `false` | Cases demo ocultados                        |
| `demo_mode`           | `false` | Modo demo desativado                        |

Para reativar um módulo, altere o valor para `true` em `config/features.php`.

---

## Módulos Ativos

- `/admin/operation` — Central de Operação (dashboard operacional real)
- `/admin/publishing-queue` — Fila de publicação de posts e blog
- `/admin/blog/posts` — Blog da agência
- `/admin/social/posts` — Posts Instagram
- `/admin/social/calendar` — Calendário de publicações
- `/admin/approvals` — Aprovações
- `/admin/ai-employees` — Funcionários IA
- `/admin/ai-tasks` — Tarefas IA
- `/admin/ai-logs` — Logs IA
- `/admin/activity-logs` — Logs operacionais
- `/admin/system-health` — System Health

---

## Módulos Desabilitados (Feature Flags = false)

Estes módulos não aparecem no sidebar mas suas rotas e dados permanecem intactos:

- Campanhas Ads
- Propostas
- Orçamentos
- Contratos
- CRM
- SDR IA
- Cases Demo
- Relatórios Fake

Para reativar, editar `config/features.php` e limpar o cache de config:

```bash
php artisan config:clear
php artisan config:cache
```

---

## Funcionários IA

| Nome                  | Status  | Função              |
|-----------------------|---------|---------------------|
| Social Media IA       | active  | Posts e Instagram   |
| Copywriter IA         | active  | Textos e legendas   |
| Blog Writer IA        | active  | Posts de blog       |
| Gestor de Tráfego IA  | paused  | —                   |
| SEO IA                | paused  | —                   |
| Designer IA           | paused  | —                   |
| SDR IA                | paused  | —                   |
| Analista IA           | paused  | —                   |
| Gerente de Projeto IA | paused  | —                   |

---

## Limpeza de Dados Demo

O comando abaixo remove posts, campanhas, aprovações e logs com palavras-chave demo/teste/mock:

```bash
# Dry run — apenas conta os registros
php artisan system:cleanup-demo-data

# Executar sem confirmação
php artisan system:cleanup-demo-data --force
```

**Preservado pelo cleanup:**
- Usuários admin, agência e clientes
- Funcionários IA e skills
- Clients reais
- Posts/campanhas sem palavras-chave demo

---

## Aviso: Provedor de IA

O sistema está configurado com `AI_PROVIDER=mock`. Neste modo:

- O conteúdo gerado é simulado e não usa APIs externas.
- Todos os outros fluxos (aprovação, agendamento, logs) funcionam normalmente.
- Para gerar conteúdo real, configure `AI_PROVIDER=openai` (ou `claude` / `gemini`) e adicione a `AI_API_KEY` no `.env`.

O sidebar exibe um badge amber "IA: Modo Mock" quando o provider é mock.

Quando configurado com provedor real, o badge fica verde com o nome do provider.

---

## Provider Real — Como configurar

```env
AI_PROVIDER=openai
AI_API_KEY=sk-...
AI_MODEL=gpt-4o-mini
```

Ou para Anthropic/Claude:
```env
AI_PROVIDER=claude
AI_API_KEY=sk-ant-...
AI_MODEL=claude-haiku-4-5-20251001
```

Após alterar:
```bash
php artisan config:clear
php artisan config:cache
php artisan queue:restart
```

---

## Próximos Passos (Phase Real 2)

1. Configurar provedor de IA real (OpenAI / Anthropic / Gemini).
2. Publicação real no Instagram via API.
3. Publicação real no blog com categorias e SEO.
4. Notificações de aprovação por e-mail.
5. Backups automáticos do banco.
6. SSL Let's Encrypt (após remover registro AAAA do DNS).
