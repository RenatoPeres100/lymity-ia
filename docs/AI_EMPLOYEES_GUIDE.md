# Guia dos Funcionários IA

## Visão Geral

Os funcionários IA são agentes especializados que executam tarefas de forma automática ou semi-automática dentro da plataforma Lymity IA.

## Tipos de Funcionários

| Tipo | Função | Tarefas Típicas |
|------|--------|-----------------|
| `social_media` | Social Media IA | Geração de posts, legendas, hashtags |
| `gestor_trafego` | Gestor de Tráfego IA | Análise de campanhas, sugestões de otimização |
| `seo` | SEO IA | Keywords, clusters, sugestões de conteúdo SEO |
| `copywriter` | Copywriter IA | Textos para landing pages, emails, anúncios |
| `designer` | Designer IA | Briefings visuais, especificações de arte |
| `sdr` | SDR IA | Qualificação de leads, follow-up automático |
| `analyst` | Analista IA | Relatórios, insights, análise de métricas |
| `project_manager` | Gerente de Projeto IA | Organização de tarefas, cronogramas |

## Configuração

Cada funcionário IA tem:

```
ai_employees
├── name          — Nome do funcionário
├── type          — Tipo (social_media, seo, etc.)
├── status        — active | paused | maintenance
├── model         — Modelo de IA (mock, gpt-4, claude-3, etc.)
├── max_daily_tasks — Limite de tarefas por dia
├── requires_approval — Se ações precisam de aprovação humana
└── skills        — Habilidades associadas (ai_skills)
```

## Ciclo de Execução

```
AiWorkSchedule (agendado)
        ↓
AiEmployee.executeTask()
        ↓
AiTask criada (status: pending)
        ↓
Task executada → status: completed | failed
        ↓
Se requires_approval = true → ApprovalRequest criada
        ↓
Admin/Cliente aprova → ação executada
        ↓
AiTaskLog + ActivityLog registrados
```

## Agenda de Trabalho

Configurada em `AiWorkSchedule`:
- `schedule_type`: daily | weekly | monthly | on_demand
- `cron_expression`: expressão cron para execução automática
- `priority`: prioridade da tarefa (1-10)

O scheduler Laravel executa `php artisan ai:run-schedules` a cada 5 minutos.

## Memória IA

`AiMemory` permite que funcionários armazenem contexto entre execuções:
- `memory_type`: preference | context | fact | instruction
- `content`: conteúdo da memória
- `expires_at`: expiração opcional

## Limites e Segurança

- `max_daily_tasks` previne uso excessivo de tokens de IA
- Toda tarefa gera `AiTaskLog` para rastreabilidade
- Ações sensíveis (`requires_approval = true`) nunca são executadas sem aprovação humana
- O provider mock (`AI_PROVIDER=mock`) simula respostas sem consumir API real

## Provider Mock

Para desenvolvimento/demo, o provider mock está ativo por padrão:

```env
AI_PROVIDER=mock
```

Para produção, substitua por:
```env
AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
```

ou

```env
AI_PROVIDER=claude
ANTHROPIC_API_KEY=sk-ant-...
```

## Administração

- Listar funcionários: `/admin/ai-employees`
- Ver tarefas: `/admin/ai-tasks`
- Ver agenda: `/admin/ai-schedules`
- Ver memória: `/admin/ai-memories`
- Ver logs de tarefas: `/admin/reports/ai`
