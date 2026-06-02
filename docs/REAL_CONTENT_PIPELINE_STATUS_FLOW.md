# Content Pipeline — Status Flow

## BlogPost

| Transição | Condição | Resultado |
|---|---|---|
| pending_approval → aprovação | `scheduled_at` futuro | `scheduled` |
| pending_approval → aprovação | sem `scheduled_at` | `approved` |
| approved → agendar | botão "Agendar" | `scheduled` |
| scheduled → publicar | `blog:publish-due` no horário | `published` |
| qualquer → rejeitar | botão "Reprovar" | `rejected` / `draft` |

### Serviços envolvidos

- `BlogPipelineService::approve()` — lógica direta via pipeline
- `ApprovalService::syncContentStatus()` — via fluxo de ApprovalRequest
- Ambos implementam a mesma regra

## SocialPost

| Transição | Condição | Resultado |
|---|---|---|
| pending_approval → aprovação | `scheduled_at` futuro + imagem `valid` + `public_image_url` | `scheduled` |
| pending_approval → aprovação | `scheduled_at` futuro + imagem inválida | `approved` + log de aviso |
| pending_approval → aprovação | sem `scheduled_at` | `approved` |
| approved → agendar | botão "Agendar" (imagem válida obrigatória) | `scheduled` |
| scheduled → publicar | `social:publish-due` no horário | `published` |

### Serviços envolvidos

- `SocialPostService::approve()` — aprovação via service
- `ApprovalService::syncSocialPostStatus()` — via ApprovalRequest

## Comandos de correção

```bash
# Corrige conteúdos aprovados com scheduled_at futuro
php artisan content:fix-approved-scheduled

# Apenas blog
php artisan blog:fix-approved-scheduled

# Diagnóstico do pipeline social
php artisan social:pipeline-diagnose
```

## Content Cycle (order)

```
1. agents:run-due-routines    — gera conteúdos via agentes IA
2. content:fix-approved-scheduled — corrige aprovados → scheduled
3. blog:publish-due           — publica blogs vencidos
4. social:publish-due         — publica socials vencidos
```
