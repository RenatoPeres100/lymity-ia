# Lymity AI Agency — Project Overview

## Visão Geral

**Lymity AI Agency** é uma plataforma SaaS de agência digital totalmente automatizada por IA. A plataforma permite que uma agência digital opere com funcionários artificiais especializados, gerenciando clientes, criando conteúdo, executando campanhas e gerando relatórios — tudo com aprovação humana em ações sensíveis.

---

## Stack Técnica

| Camada | Tecnologia | Versão |
|---|---|---|
| Framework | Laravel | 13.x |
| Linguagem | PHP | 8.3 |
| Banco de dados | MySQL | 8.0 |
| Cache / Filas | Redis | 7.x |
| Frontend template | Blade + Tailwind CSS | - |
| Queue workers | Laravel Queue (Redis) | - |
| Scheduler | Laravel Scheduler | - |
| IA principal | Anthropic Claude API | claude-sonnet-4-6 |
| Servidor | Ubuntu 24.04 LTS (VPS Hostinger KVM 2) | - |

---

## Servidor

- **Provedor:** Hostinger VPS KVM 2
- **IP:** 187.124.133.195
- **CPU:** 2 vCPU
- **RAM:** 8 GB
- **Disco:** 100 GB NVMe
- **Banda:** 8 TB/mês
- **OS:** Ubuntu 24.04 LTS

---

## Arquitetura Geral

```
┌─────────────────────────────────────────────────────┐
│                    NGINX (porta 80/443)              │
└────────────────────┬────────────────────────────────┘
                     │
         ┌───────────┴───────────┐
         │                       │
   Site Público             Laravel App
   (Blade SSR)         ┌────────┴────────┐
                        │                │
                   API Routes      Web Routes
                   (/api/v1)       (Blade)
                        │
              ┌─────────┴────────┐
              │                  │
          MySQL 8.0          Redis 7.x
          (dados)          (cache/fila)
                                  │
                        ┌─────────┴────────┐
                        │                  │
                   Queue Workers       Scheduler
                   (Celery-like)    (cron tasks)
                        │
               ┌────────┴────────┐
               │                 │
         Claude API          Integrações
         (IA Agents)       (Ads/SEO/etc)
```

---

## Módulos do Sistema

### Fase 0 — Setup Base (atual)
- [x] Instalação do Laravel 13
- [x] Configuração MySQL
- [x] Configuração Redis
- [x] Variáveis de ambiente
- [x] Git configurado
- [x] Documentação inicial

### Fase 1 — Autenticação e Usuários
- [ ] Sistema de login/registro
- [ ] Roles e permissões (Spatie Permission)
- [ ] 2FA opcional
- [ ] Área do admin geral
- [ ] Área do cliente

### Fase 2 — Dashboard e Módulos Core
- [ ] Dashboard admin com métricas
- [ ] Módulo de clientes (tenancy)
- [ ] Módulo de usuários
- [ ] Módulo de logs de auditoria
- [ ] Módulo de aprovações

### Fase 3 — Funcionários IA
- [ ] Base dos agentes IA
- [ ] Social Media IA
- [ ] Copywriter IA
- [ ] SEO IA
- [ ] Gestor de Tráfego IA
- [ ] Designer IA
- [ ] SDR IA
- [ ] Analista IA
- [ ] Gerente de Projeto IA

### Fase 4 — Conteúdo e Publicações
- [ ] Blog da agência
- [ ] Blog dos clientes
- [ ] Posts para redes sociais
- [ ] Aprovação antes de publicação
- [ ] Agendamento de publicações

### Fase 5 — Campanhas e Integrações
- [ ] Google Ads integration
- [ ] Meta Ads integration
- [ ] Google Analytics
- [ ] Google Search Console

### Fase 6 — Site Institucional
- [ ] Landing page premium
- [ ] Páginas de serviços
- [ ] Cases / portfolio
- [ ] Contato e lead capture

### Fase 7 — API Mobile
- [ ] Sanctum tokens
- [ ] API RESTful v1
- [ ] Documentação Swagger/OpenAPI

---

## Níveis de Usuário

| Role | Descrição |
|---|---|
| `admin_geral` | Acesso total ao sistema |
| `agencia_admin` | Administrador da agência |
| `agencia_operador` | Operador da agência |
| `social_media` | Responsável por redes sociais |
| `gestor_trafego` | Gestor de campanhas de tráfego |
| `seo` | Especialista em SEO |
| `copywriter` | Criador de conteúdo |
| `designer` | Designer visual |
| `cliente_admin` | Admin do cliente |
| `cliente_colaborador` | Colaborador do cliente |
| `viewer` | Visualizador (somente leitura) |
| `ai_employee` | Funcionário IA (sistema) |

---

## Funcionários IA

Cada funcionário IA é uma instância do Claude configurada com:
- **Prompt de sistema** específico para sua função
- **Permissões** limitadas ao seu escopo
- **Histórico de execuções** registrado no banco
- **Aprovação obrigatória** antes de publicações externas
- **Limites de tokens** e custos monitorados

| Funcionário IA | Função Principal |
|---|---|
| Social Media IA | Criar e agendar posts para redes sociais |
| Copywriter IA | Criar textos, artigos, e-mails e anúncios |
| SEO IA | Otimização de conteúdo, keywords, meta tags |
| Gestor de Tráfego IA | Apoio em campanhas Google Ads e Meta Ads |
| Designer IA | Briefings visuais, prompts para geração de imagem |
| SDR IA | Prospecção e qualificação de leads |
| Analista IA | Relatórios, métricas e insights |
| Gerente de Projeto IA | Coordenação de tarefas e prazos |

---

## Regras de Segurança

1. **Nenhuma ação destrutiva** sem confirmação do admin_geral.
2. **Toda publicação externa** requer aprovação antes de ir ao ar.
3. **Toda ação sensível** gera registro no log de auditoria.
4. **Senhas e chaves de API** nunca são expostas em logs ou respostas.
5. **Rate limiting** em todas as rotas da API.
6. **Sanitização de inputs** em todos os formulários.

---

## Estrutura de Pastas (Laravel)

```
/var/www/lymity-ia/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Painel administrativo
│   │   │   ├── Client/         # Área do cliente
│   │   │   ├── Api/V1/         # API endpoints
│   │   │   └── Site/           # Site institucional
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/               # Lógica de negócio
│   ├── AI/                     # Funcionários IA
│   │   ├── BaseAgent.php
│   │   ├── SocialMediaAgent.php
│   │   └── ...
│   ├── Jobs/                   # Queue jobs
│   ├── Policies/
│   └── Providers/
├── database/
│   ├── migrations/
│   └── seeders/
├── docs/                       # Documentação do projeto
├── resources/
│   ├── views/
│   │   ├── admin/
│   │   ├── client/
│   │   ├── site/
│   │   └── layouts/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── admin.php
└── tests/
```

---

## Comandos Úteis

```bash
# Servidor de desenvolvimento
php artisan serve --host=0.0.0.0 --port=8000

# Rodar migrations
php artisan migrate

# Rodar seeders
php artisan db:seed

# Fila de jobs
php artisan queue:work redis --sleep=3 --tries=3

# Scheduler (em produção via cron)
php artisan schedule:run

# Limpar caches
php artisan optimize:clear

# Gerar nova chave
php artisan key:generate
```

---

## Contato e Administrador

- **Projeto:** Lymity AI Agency
- **Admin:** Renato Peres
- **E-mail:** renatoperes300@gmail.com
- **Data de início:** 2026-05-22

---

*Documentação mantida pelo Claude Code — Fase 0*
