# Guia do Usuário — Administrador da Agência

## Acesso

URL: `/login`  
E-mail: `agencia@lymity.local` (ou `admin@lymity.local` para super admin)  
Senha: `password`

## Dashboard

O dashboard (`/admin/dashboard`) exibe:
- Resumo de clientes, usuários, tarefas IA e aprovações pendentes
- Cards com métricas dos últimos 30 dias
- Lista das aprovações mais recentes aguardando ação

## Gerenciar Clientes

**Menu:** Clientes > Lista de Clientes  
**URL:** `/admin/clients`

- Criar, editar e arquivar clientes
- Ver perfil de marca, sites, posts, arquivos e contratos por cliente
- Acessar arquivos do cliente: `/admin/clients/{id}/files`

## Funcionários IA

**Menu:** IA > Funcionários IA  
**URL:** `/admin/ai-employees`

Cada funcionário IA tem:
- Tipo (social_media, seo, copywriter, etc.)
- Status (active/paused)
- Limite de execuções por dia
- Histórico de tarefas

Para acionar manualmente: edite o funcionário e altere o status.

## Social Media

**Menu:** Social Media > Posts / Calendário  
**URL:** `/admin/social-posts`, `/admin/social-calendar`

Fluxo de post:
1. Criar post (draft)
2. Submeter para aprovação (pending_approval)
3. Cliente aprova via `/client/approvals`
4. Agendar (`scheduled`)
5. Publicar (`published`)

## Aprovações

**Menu:** Aprovações  
**URL:** `/admin/approvals`

- Visualizar todas as aprovações (pending/approved/rejected)
- Ver o conteúdo vinculado (post, campanha, etc.)
- Aprovar ou rejeitar com comentário

## Relatórios

**Menu:** Relatórios  
**URLs:** `/admin/reports/executive`, `/admin/reports/ai`, `/admin/reports/activity`

- Relatório executivo: métricas consolidadas
- Relatório IA: tarefas e eficiência dos funcionários IA
- Log de atividades: todas as ações do sistema

## Arquivos

**Menu:** Arquivos  
**URL:** `/admin/files`

- Upload de arquivos (imagem, PDF, doc, vídeo, áudio)
- Filtros por tipo, cliente, fonte
- Arquivos ficam em `storage/app/public/`

## System Health

**Menu:** Sistema > System Health  
**URL:** `/admin/system-health`

Painel de saúde do sistema: banco, Redis, storage, queue, configurações.

## Segurança

- Nunca compartilhe credenciais de produção
- Ações sensíveis exigem aprovação
- Tokens de integração são ocultos no sistema
- APP_DEBUG deve ser `false` em produção
