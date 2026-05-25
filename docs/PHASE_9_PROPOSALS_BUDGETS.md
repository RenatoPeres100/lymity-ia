# Phase 9 — Módulo Comercial: Propostas, Orçamentos e Contratos

## Visão geral

Módulo comercial completo para gestão de propostas, orçamentos e contratos de clientes, com fluxo de aprovação interna, aceite pelo cliente e geração de conteúdo por IA.

**Nenhuma cobrança real é executada nesta fase.** Todos os registros são internos.

---

## Funcionalidades implementadas

### Propostas (`proposals`)

- Criação manual e via IA (MockAiProvider)
- Itens com cálculo automático de `total_price = quantity × unit_price`
- Recálculo automático do total da proposta
- Fluxo de status: `draft → pending_approval → approved → sent → accepted / rejected`
- Envio para aprovação interna (cria `ApprovalRequest` com `approval_type = 'proposal'`)
- Aprovação integrada com `ApprovalService::approve()`
- Envio ao cliente após aprovação interna
- Cliente pode aceitar ou rejeitar (com comentário)
- Arquivamento manual

### Orçamentos (`budgets`)

- Criação manual por mês/ano
- Itens com categorias: `media`, `production`, `service`, `tool`, `other`
- Status de item: `active`, `paused`, `archived`
- Recálculo de total (apenas itens `active`)
- Fluxo de status: `draft → pending_approval → approved → active → archived`
- Envio para aprovação (cria `ApprovalRequest` com `approval_type = 'budget'`)
- Cliente pode aprovar ou comentar

### Contratos (`client_contracts`)

- Criação e edição de contratos como documentos internos
- Fluxo de status: `draft → pending_signature → signed → canceled / archived`
- Ações: enviar para assinatura, marcar como assinado, cancelar
- Sem integração de assinatura digital (apenas registro)

---

## Rotas registradas

### Admin

| Método | Rota | Nome |
|--------|------|------|
| GET | `/admin/proposals` | `admin.proposals.index` |
| GET | `/admin/proposals/create` | `admin.proposals.create` |
| POST | `/admin/proposals` | `admin.proposals.store` |
| GET | `/admin/proposals/{id}` | `admin.proposals.show` |
| GET | `/admin/proposals/{id}/edit` | `admin.proposals.edit` |
| PUT | `/admin/proposals/{id}` | `admin.proposals.update` |
| DELETE | `/admin/proposals/{id}` | `admin.proposals.destroy` |
| POST | `/admin/proposals/generate-ai` | `admin.proposals.generate-ai` |
| POST | `/admin/proposals/{id}/send-approval` | `admin.proposals.send-approval` |
| POST | `/admin/proposals/{id}/send-client` | `admin.proposals.send-client` |
| GET | `/admin/budgets` | `admin.budgets.index` |
| POST | `/admin/budgets/{id}/send-approval` | `admin.budgets.send-approval` |
| GET | `/admin/contracts` | `admin.contracts.index` |
| POST | `/admin/contracts/{id}/pending-signature` | `admin.contracts.pending-signature` |
| POST | `/admin/contracts/{id}/mark-signed` | `admin.contracts.mark-signed` |
| POST | `/admin/contracts/{id}/cancel` | `admin.contracts.cancel` |

### Cliente

| Método | Rota | Nome |
|--------|------|------|
| GET | `/client/proposals` | `client.proposals.index` |
| GET | `/client/proposals/{id}` | `client.proposals.show` |
| POST | `/client/proposals/{id}/accept` | `client.proposals.accept` |
| POST | `/client/proposals/{id}/reject` | `client.proposals.reject` |
| POST | `/client/proposals/{id}/comment` | `client.proposals.comment` |
| GET | `/client/budgets` | `client.budgets.index` |
| GET | `/client/budgets/{id}` | `client.budgets.show` |
| POST | `/client/budgets/{id}/approve` | `client.budgets.approve` |
| POST | `/client/budgets/{id}/reject` | `client.budgets.reject` |
| POST | `/client/budgets/{id}/comment` | `client.budgets.comment` |
| GET | `/client/contracts` | `client.contracts.index` |
| GET | `/client/contracts/{id}` | `client.contracts.show` |

---

## Arquitetura

### Models

- `App\Models\Proposal` — morphMany ApprovalRequest, hasMany ProposalItem, recalculateTotal()
- `App\Models\ProposalItem` — booted() auto-calcula total_price no save
- `App\Models\Budget` — morphMany ApprovalRequest, hasMany BudgetItem, recalculateTotal()
- `App\Models\BudgetItem` — categoria com label accessor
- `App\Models\ClientContract` — statusLabel, statusColor accessors

### Services

- `App\Services\Commercial\ProposalService` — CRUD, approval, sendToClient, acceptByClient, rejectByClient, generateWithAi
- `App\Services\Commercial\BudgetService` — CRUD, sendToApproval, approveByClient
- `App\Services\Commercial\ContractService` — CRUD, markPendingSignature, markSigned, cancel
- `App\Services\Commercial\CommercialAiService` — delegate para ProposalService e BudgetService com IA

### Integração com ApprovalService

`ApprovalService::syncApprovableStatus()` foi estendido com handlers para:
- `App\Models\Proposal` → `syncProposalStatus()`: atualiza status, approved_by, approved_at
- `App\Models\Budget` → `syncBudgetStatus()`: atualiza status

### MockAiProvider

Novos casos adicionados ao `match()`:
- `generate_proposal` — retorna proposta mock com 5 itens (~R$ 7.600)
- `generate_budget` — retorna orçamento mock com 5 itens (~R$ 10.600)
- `improve_proposal` — retorna versão melhorada da proposta
- `summarize_budget` — retorna resumo executivo do orçamento

### Isolamento de dados do cliente

Todos os controllers `Client\*` verificam:
```php
abort_unless($model->client_id === auth()->user()->client_id, 403);
```

---

## Migrações

| Arquivo | Tabela |
|---------|--------|
| `2026_05_25_900001_create_proposals_table.php` | `proposals` |
| `2026_05_25_900002_create_proposal_items_table.php` | `proposal_items` |
| `2026_05_25_900003_create_budgets_table.php` | `budgets` |
| `2026_05_25_900004_create_budget_items_table.php` | `budget_items` |
| `2026_05_25_900005_create_client_contracts_table.php` | `client_contracts` |

---

## Seeders

- `ProposalSeeder` — proposta demo com 3 itens, total R$ 5.800
- `BudgetSeeder` — orçamento demo com 5 itens, total R$ 9.000
- `ClientContractSeeder` — contrato demo em rascunho

---

## Testes realizados (tinker)

Todos os critérios de aceitação foram validados:

- [x] Proposta manual com 2 itens calcula total correto (R$ 2.200)
- [x] `ProposalItem.total_price` calculado automaticamente via `booted()`
- [x] `sendToInternalApproval()` cria `ApprovalRequest` com `approval_type = 'proposal'`
- [x] `ApprovalService::approve()` muda status para `approved`
- [x] `sendToClient()` muda status para `sent`
- [x] `acceptByClient()` muda status para `accepted`
- [x] Orçamento com 3 itens calcula total correto (R$ 5.500)
- [x] `BudgetService::sendToApproval()` cria `ApprovalRequest` com `approval_type = 'budget'`
- [x] `ApprovalService::approve()` muda orçamento para `approved`
- [x] Contrato demo criado pelo seeder (status `draft`)
- [x] `CommercialAiService::generateProposal()` retorna `Proposal` (mock)
- [x] Nenhuma cobrança real executada
- [x] Rotas admin e cliente retornam 200/302 (zero 500s)
- [x] Regressão das fases 1–8 aprovada (zero 500s)
