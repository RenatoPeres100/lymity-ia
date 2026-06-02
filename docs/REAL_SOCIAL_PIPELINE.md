# Social Pipeline

## Rota

```
GET /admin/social/pipeline
name: admin.social.pipeline.index
```

## Controller

`App\Http\Controllers\Admin\SocialPipelineController`

## View

`resources/views/admin/social/pipeline/index.blade.php`
`resources/views/admin/social/pipeline/_column.blade.php`

## Colunas

| Coluna | Status |
|---|---|
| Rascunhos | `draft` |
| Aguardando Aprovação | `pending_approval` |
| Aprovados | `approved` |
| Agendados | `scheduled` |
| Publicados | `published` |
| Falhas | `failed` |

## Card do post social

Cada card mostra:
- Preview de imagem (se existir) com badge de status
- Título, objetivo, tipo, formato
- Agente IA, aprovador, data agendada
- Alertas: imagem inválida, imagem desatualizada

## Botões disponíveis por estado

| Estado | Botões disponíveis |
|---|---|
| draft | Editar, Gerar imagem IA (se caption preenchida), Enviar aprovação |
| pending_approval | Editar, Aprovar, Reprovar |
| approved | Editar, Agendar (se imagem válida), Publicar agora |
| scheduled | Editar, Publicar agora |
| published | Ver permalink |

## Alerta de imagem

- Sem imagem + sem caption: aviso "Sem legenda"
- Sem imagem + com caption: botão "Gerar imagem IA"
- Imagem desatualizada (texto alterado): botão "Regen. imagem" + overlay amarelo
- Imagem inválida em approved: aviso vermelho na coluna

## Diagnóstico

```bash
php artisan social:pipeline-diagnose
```

Mostra:
- Contagem por status
- Posts approved com scheduled_at futuro não agendados
- Posts approved com imagem inválida
- Posts scheduled sem imagem válida
- Posts due agora
