# Lymity IA — Visão Geral Final (MVP)

## O que é

A Lymity IA é uma plataforma completa de agência digital automatizada por IA. Ela permite que uma agência de marketing opere clientes, conteúdo, aprovações, campanhas e relatórios com suporte de funcionários IA especializados.

## Módulos Implementados

| Fase | Módulo | Status |
|------|--------|--------|
| 1 | Autenticação, Usuários, Clientes, Permissões | ✅ |
| 2 | IA Employees, Blog da Agência, Leads | ✅ |
| 3 | Perfil de Marca, Sites de Clientes, Assets, Blog do Cliente | ✅ |
| 4 | Agenda IA, Memória IA | ✅ |
| 5 | Módulo de Aprovações | ✅ |
| 6 | Social Media (Canais, Calendário, Posts) | ✅ |
| 7 | SEO (Keywords, Clusters, Content Plan, Audits) | ✅ |
| 8 | Ads (Ad Accounts, Campanhas, Métricas) | ✅ |
| 9 | Comercial (Propostas, Orçamentos, Contratos) | ✅ |
| 10 | API Mobile + PWA | ✅ |
| 11 | Logs, Relatórios e Dashboards | ✅ |
| 12 | Produção VPS (Supervisor, Health Check, Scripts) | ✅ |
| 13 | Arquivos e Google Drive (placeholder) | ✅ |
| 14 | Polish Final, Demo Flow, Validação MVP | ✅ |

## Credenciais Demo

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Super Admin | admin@lymity.local | password |
| Admin Agência | agencia@lymity.local | password |
| Social Media | social@lymity.local | password |
| Gestor de Tráfego | trafego@lymity.local | password |
| SEO Specialist | seo@lymity.local | password |
| Copywriter | copy@lymity.local | password |
| Designer | designer@lymity.local | password |
| Cliente Demo 1 | cliente@lymity.local | password |
| Cliente Demo B2B | cliente2@lymity.local | password |
| Usuário Inativo | inativo@lymity.local | password |

## Stack Técnica

- **Backend:** PHP 8.3 + Laravel 13
- **Frontend:** Blade + Tailwind CSS
- **Banco de dados:** MySQL 8
- **Cache/Queue:** Redis
- **Servidor web:** PHP built-in dev / Nginx produção
- **Storage:** Local (storage/app/public) + symlink public/storage
- **IA:** Mock provider (extensível para OpenAI/Claude API)

## URLs Principais

| Área | URL |
|------|-----|
| Site institucional | / |
| Login | /login |
| Dashboard Admin | /admin/dashboard |
| Área do Cliente | /client/dashboard |
| API Mobile | /api/v1/mobile/... |

## Arquitetura

```
app/
├── Console/Commands/       # Artisan commands
├── Http/
│   ├── Controllers/Admin/  # 30+ controllers admin
│   ├── Controllers/Client/ # Controllers área do cliente
│   ├── Controllers/Api/    # API mobile
│   └── Middleware/         # auth, active, agency, client_access
├── Models/                 # 40+ Eloquent models
├── Services/               # Domain services por módulo
│   ├── Ai/
│   ├── Demo/
│   ├── Files/
│   └── System/
└── Jobs/                   # Queue jobs
```

## Dados de Demo

Após `php artisan migrate:fresh --seed`, o banco conterá:

- 2 clientes ativos com dados completos
- 10 usuários demo (6 da agência + 2 clientes + admin + inativo)
- Posts de social media em todos os status
- Aprovações pendentes e resolvidas
- Campanhas Google Ads e Meta Ads com métricas
- Propostas, orçamentos e contratos
- Keywords SEO, clusters e audits
- Arquivos e pastas de cliente
- Logs de atividade
- Funcionários IA com tarefas executadas
