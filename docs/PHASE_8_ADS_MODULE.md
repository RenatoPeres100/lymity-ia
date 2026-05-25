# Fase 8 — Módulo de Campanhas de Mídia Paga (Ads)

## Objetivo

Criar um módulo completo para planejar, gerar, aprovar e acompanhar campanhas de mídia paga (Google Ads, Meta Ads, LinkedIn Ads, TikTok Ads) para a agência e seus clientes.

**Esta fase opera em modo sandbox/planejamento. Nenhuma ação real é executada nas plataformas externas.**

---

## Models e Migrations

| Model               | Tabela                   | Descrição |
|---------------------|--------------------------|-----------|
| `AdAccount`         | `ad_accounts`            | Conta de anúncio (por plataforma) |
| `AdCampaign`        | `ad_campaigns`           | Campanha de anúncio |
| `AdGroup`           | `ad_groups`              | Grupo de anúncio dentro da campanha |
| `AdCreative`        | `ad_creatives`           | Criativos/anúncios |
| `AdKeyword`         | `ad_keywords`            | Palavras-chave |
| `AdAudience`        | `ad_audiences`           | Públicos/audiências |
| `CampaignMetric`    | `campaign_metrics`       | Métricas diárias (mock) |
| `CampaignBudgetChange` | `campaign_budget_changes` | Solicitações de alteração de orçamento |

---

## Rotas Admin

| Método | URL | Nome da Rota |
|--------|-----|-------------|
| GET | `/admin/ads` | `admin.ads.index` |
| GET/POST | `/admin/ads/accounts` | `admin.ads.accounts.*` |
| GET/POST | `/admin/ads/campaigns` | `admin.ads.campaigns.*` |
| POST | `/admin/ads/campaigns/{id}/send-approval` | `admin.ads.campaigns.send-approval` |
| POST | `/admin/ads/campaigns/{id}/schedule` | `admin.ads.campaigns.schedule` |
| POST | `/admin/ads/campaigns/{id}/pause-sandbox` | `admin.ads.campaigns.pause-sandbox` |
| POST | `/admin/ads/campaigns/{id}/generate-mock-metrics` | `admin.ads.campaigns.generate-mock-metrics` |
| POST | `/admin/ads/campaigns/generate-ai` | `admin.ads.campaigns.generate-ai` |
| GET | `/admin/ads/metrics` | `admin.ads.metrics.index` |
| GET/POST | `/admin/ads/budget-approvals` | `admin.ads.budget-approvals.*` |
| POST | `/admin/ads/budget-approvals/{id}/apply` | `admin.ads.budget-approvals.apply` |

---

## Rotas Cliente

| Método | URL | Nome da Rota |
|--------|-----|-------------|
| GET | `/client/ads` | `client.ads.index` |
| GET | `/client/ads/campaigns` | `client.ads.campaigns.index` |
| GET | `/client/ads/campaigns/{id}` | `client.ads.campaigns.show` |
| GET | `/client/ads/approvals` | `client.ads.approvals.index` |
| GET | `/client/ads/reports` | `client.ads.reports.index` |

---

## Services

### `AdsPlanningService`
Serviço central do módulo. Gerencia criação de contas, campanhas, envio para aprovação, geração por IA e alterações de orçamento.

### `GoogleAdsService`
- `sandboxGenerateCampaign()` — Gera estrutura mock de campanha Google Ads
- `validateSandboxAccount()` — Valida conta Google Ads (sandbox)
- `realPublish()` — **Lança exceção.** Publicação real não habilitada nesta fase.

### `MetaAdsService`
- `sandboxGenerateCampaign()` — Gera estrutura mock de campanha Meta Ads
- `validateSandboxAccount()` — Valida conta Meta Ads (sandbox)
- `realPublish()` — **Lança exceção.** Publicação real não habilitada nesta fase.

### `CampaignMetricService`
- `createMockMetrics()` — Gera métricas simuladas para N dias
- `calculateDerivedMetrics()` — Calcula CTR, CPC, CPA, ROAS
- `summarizeCampaign()` — Retorna resumo agregado da campanha

### `CampaignApprovalService`
- `createCampaignApproval()` — Cria `ApprovalRequest` para campanha
- `createBudgetApproval()` — Cria `ApprovalRequest` para alteração de orçamento
- `syncApprovalResult()` — Sincroniza resultado da aprovação com o approvable

---

## Modo Sandbox

- **Nenhuma ação real é executada** em Google Ads, Meta Ads ou qualquer plataforma externa.
- Tokens de acesso (`access_token`, `refresh_token`) não são exibidos em tela.
- Campanhas geradas por IA usam o `MockAiProvider`.
- Métricas são simuladas aleatoriamente via `CampaignMetricService::createMockMetrics()`.
- Ativação/publicação real de campanhas requer integração futura segura.

---

## Task Types de IA (MockAiProvider)

| Task Type | Descrição |
|-----------|-----------|
| `generate_google_ads_campaign` | Gera estrutura completa de campanha Google Search |
| `generate_meta_ads_campaign` | Gera estrutura completa de campanha Meta Ads |
| `generate_ad_creatives` | Gera criativos e copies para anúncios |
| `generate_keywords` | Gera lista de palavras-chave com match type |
| `generate_audience_suggestions` | Sugere públicos e segmentações |
| `analyze_campaign_metrics` | Analisa métricas e gera insights |
| `suggest_budget_change` | Sugere alteração de orçamento com justificativa |

---

## Fluxo de Aprovação de Campanha

1. Campanha criada com `status = draft`
2. Administrador envia para aprovação → `status = pending_approval`
3. `ApprovalRequest` criada com `approval_type = campaign`
4. Admin geral ou agência aprova via painel de aprovações
5. `ApprovalService::syncApprovableStatus()` atualiza `AdCampaign.status = approved`
6. Campanha pode ser agendada (sandbox)

---

## Fluxo de Aprovação de Orçamento

1. Usuário solicita alteração via `AdsPlanningService::createBudgetChange()`
2. `CampaignBudgetChange` criado com `status = pending_approval`
3. `ApprovalRequest` criada com `approval_type = budget` e `sensitive_level = high`
4. Após aprovação, `CampaignBudgetChange.status = approved`
5. Admin aplica via `AdsPlanningService::applyApprovedBudgetChange()`
6. `AdCampaign.daily_budget` é atualizado e `CampaignBudgetChange.status = applied`

**Orçamento só é aplicado após aprovação explícita. Nunca automaticamente.**

---

## Métricas Mock

As métricas são geradas aleatoriamente com valores dentro de benchmarks realistas:
- Impressões: 500–5.000/dia
- CTR: 2–6%
- CPC: R$ 1,50–8,00
- CPA: R$ 40–150
- ROAS: 2x–6x

---

## Segurança e Tokens

- Tokens de acesso são armazenados mas **nunca exibidos** em tela (campo `$hidden` no model).
- Nenhuma requisição HTTP é feita para APIs externas nesta fase.
- `GoogleAdsService::realPublish()` e `MetaAdsService::realPublish()` lançam `RuntimeException`.
- Isolamento de cliente: usuários cliente só veem campanhas do próprio `client_id`.

---

## Isolamento de Cliente

- `Client\AdCampaignController` filtra por `client_id = auth()->user()->client_id`.
- `Client\AdsApprovalController` filtra aprovações por `client_id`.
- `Client\AdsReportController` filtra métricas por campanhas do cliente.
- Acesso a campanha de outro cliente retorna `abort(403)`.

---

## Seeders

| Seeder | Dados |
|--------|-------|
| `AdAccountSeeder` | 2 contas demo (Google Ads + Meta Ads) para o primeiro cliente |
| `AdCampaignSeeder` | 1 campanha demo com grupo, 2 criativos, 5 keywords, 1 audiência |
| `CampaignMetricSeeder` | 7 dias de métricas simuladas para a campanha demo |

---

## Regressão

Esta fase não altera funcionalidades de fases anteriores. Os seguintes módulos foram mantidos intactos:
- Fases 1–7: site, dashboard, clientes, funcionários IA, aprovações, social media, SEO/blog

---

## Próximos Passos

- Fase 9: Integração real com Google Ads API (OAuth2, campanhas reais)
- Fase 9: Integração real com Meta Marketing API
- Fase 9: Webhook de conversão (Google Tag Manager, Meta Pixel)
- Fase 9: Relatórios automáticos por e-mail
- Fase 9: Otimização automática por IA com aprovação
