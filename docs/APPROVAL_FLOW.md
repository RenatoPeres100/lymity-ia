# Fluxo de Aprovações — Documentação Técnica

## Visão Geral

O módulo de aprovações garante que nenhuma ação sensível seja executada sem revisão humana. Isso inclui publicações em redes sociais, ativação de campanhas, envio de propostas e execução de tarefas IA sensíveis.

## Models

### ApprovalRequest

Tabela: `approval_requests`

```
├── client_id          → clients (quem o conteúdo pertence)
├── requested_by       → users (quem solicitou)
├── ai_employee_id     → ai_employees (se gerado por IA)
├── approvable_type    → tipo do modelo relacionado (polimorfismo)
├── approvable_id      → ID do modelo relacionado
├── title              → título descritivo
├── description        → descrição detalhada
├── approval_type      → social_post | campaign | proposal | contract | ai_task | other
├── status             → pending | approved | rejected | cancelled
├── sensitive_level    → low | medium | high | critical
├── payload            → JSON com dados extras
├── due_at             → prazo para aprovação
├── approved_by        → users (quem aprovou)
├── approved_at        → timestamp da aprovação
├── rejected_by        → users (quem rejeitou)
└── rejected_at        → timestamp da rejeição
```

### ApprovalAction

Registra cada ação tomada em uma aprovação (aprovação parcial, comentários, solicitação de revisão).

### ApprovalComment

Comentários vinculados a uma aprovação.

### AiApproval

Aprovações específicas para tarefas IA que requerem validação humana antes de executar uma ação.

## Fluxo Padrão

```
1. Conteúdo criado (draft)
           ↓
2. Submetido para aprovação (pending_approval)
           ↓
3. ApprovalRequest criada (status: pending)
           ↓
4. Notificação para aprovador (admin/cliente)
           ↓
5a. APROVADO → status: approved
    - SocialPost: status → scheduled → published
    - Campaign: status → active
    - Proposal: status → sent
    - Contract: status → signed
           ↓
5b. REJEITADO → status: rejected
    - Item volta para revisão (draft ou rejected)
    - Comentário de rejeição registrado
```

## Tipos de Aprovação

| `approval_type` | Modelo Vinculado | Quem Aprova |
|-----------------|------------------|-------------|
| `social_post` | SocialPost | Cliente ou Admin |
| `campaign` | AdCampaign | Admin Agência |
| `proposal` | Proposal | Cliente |
| `contract` | ClientContract | Cliente |
| `ai_task` | AiTask | Admin Agência |
| `other` | Qualquer model | Admin |

## Níveis de Sensibilidade

| Nível | Quando Usar |
|-------|-------------|
| `low` | Posts de redes sociais, atualizações de blog |
| `medium` | Campanhas pagas, propostas comerciais |
| `high` | Contratos, integrações externas |
| `critical` | Acesso a dados, ações irreversíveis |

## Rotas

### Admin
- `GET /admin/approvals` — Listagem completa
- `GET /admin/approvals/{id}` — Detalhe
- `POST /admin/approvals/{id}/approve` — Aprovar
- `POST /admin/approvals/{id}/reject` — Rejeitar

### Cliente
- `GET /client/approvals` — Minhas aprovações pendentes
- `GET /client/approvals/{id}` — Detalhe
- `POST /client/approvals/{id}/approve` — Aprovar
- `POST /client/approvals/{id}/reject` — Rejeitar

## Isolamento

- Cliente só visualiza aprovações com `client_id === seu_client_id`
- Admin visualiza aprovações de todos os clientes da empresa
- `abort_if($approval->client_id !== $client->id, 403)` em todos os métodos do ClientController

## ActivityLog

Toda aprovação gera entrada em `ActivityLog`:
```php
ActivityLog::create([
    'action'       => 'approval_approved', // ou approval_rejected
    'subject_type' => ApprovalRequest::class,
    'subject_id'   => $approval->id,
    'description'  => "Aprovação #{$approval->id} aprovada por {$user->name}",
]);
```
