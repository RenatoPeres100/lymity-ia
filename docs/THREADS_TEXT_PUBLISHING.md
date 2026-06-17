# Threads Text Publishing — Lymity IA

## Variáveis .env

Duas opções disponíveis (ver `docs/THREADS_CONNECTION_SETUP.md` para detalhes):

**Opção A — Reaproveitar app Meta/Instagram:**
```dotenv
THREADS_USE_META_APP=true
THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
THREADS_GRAPH_VERSION=v1.0
THREADS_BASE_URL=https://graph.threads.net
THREADS_OAUTH_BASE_URL=https://threads.net/oauth/authorize
THREADS_TOKEN_URL=https://graph.threads.net/oauth/access_token
THREADS_PUBLISHING_ENABLED=false
THREADS_SCOPES=threads_basic,threads_content_publish
# Usa META_APP_ID / META_APP_SECRET já configurados
```

**Opção B — App dedicado Threads:**
```dotenv
THREADS_USE_META_APP=false
THREADS_APP_ID=               # ID do app Threads no Meta Developers
THREADS_APP_SECRET=           # Secret do app Threads
THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
THREADS_GRAPH_VERSION=v1.0
THREADS_BASE_URL=https://graph.threads.net
THREADS_OAUTH_BASE_URL=https://threads.net/oauth/authorize
THREADS_TOKEN_URL=https://graph.threads.net/oauth/access_token
THREADS_PUBLISHING_ENABLED=false
THREADS_SCOPES=threads_basic,threads_content_publish
```

## Permissões necessárias

| Permissão | Papéis |
|---|---|
| social.threads.view | admin_geral, agencia_admin, social_media |
| social.threads.connect | admin_geral, agencia_admin |
| social.threads.disconnect | admin_geral, agencia_admin |
| social.threads.create | admin_geral, agencia_admin, social_media, copywriter |
| social.threads.update | admin_geral, agencia_admin, social_media, copywriter |
| social.threads.approve | admin_geral, agencia_admin |
| social.threads.schedule | admin_geral, agencia_admin, social_media |
| social.threads.publish | admin_geral, agencia_admin |
| social.threads.logs.view | admin_geral, agencia_admin, social_media |

Clientes não têm acesso ao Threads nesta fase.

## Como configurar o app Meta/Threads

1. Acesse [developers.facebook.com](https://developers.facebook.com)
2. Crie um app do tipo **Threads** (ou adicione o produto Threads a um app existente)
3. Configure a URI de callback: `https://ia.lymity.com.br/admin/social/threads/callback`
4. Scopes necessários: `threads_basic`, `threads_content_publish`
5. Copie o App ID e App Secret para o `.env`
6. Rode `php artisan config:clear`

## Como conectar Threads

1. Acesse `/admin/social/threads`
2. Verifique o checklist de configuração
3. Clique em **"Conectar Threads"**
4. Autorize o app no popup do Threads/Meta
5. O callback salva o canal com `platform=threads` e `status=connected`
6. Verifique o `threads_user_id` na tela de conexão

## Como criar post manual

1. Acesse `/admin/social/threads/posts/create`
2. Preencha: título (interno), texto do post (10–1800 chars), CTA (opcional), hashtags (máx 5)
3. **Não há campo de imagem** — posts Threads desta fase são somente texto
4. Clique em **Criar Post** → status `draft`

## Como criar tarefa recorrente com Social Media IA

No painel `/admin/agent-tasks`, crie uma nova tarefa:

```
Título: Criar posts no Threads da Lymity IA
Funcionário: Social Media IA
Tipo: threads_text_post_recurring
Canal: threads
Formato: threads_text
Frequência: daily (ou manual para testes)
Requer aprovação: sim
Requer imagem: não
Status: active

Instruções operacionais:
Criar posts curtos e estratégicos para o Threads sobre IA, automação, 
tecnologia, negócios e crescimento. Usar Brand Context da Lymity IA.
Tom especialista, humano e direto. Não ser genérico. 
Trazer ponto de vista claro, insight ou provocação.
```

O AgentTask gera:
- `SocialPost` com `platform=threads`, `content_type=threads_text`, `status=pending_approval`
- `GeneratedContentPackage`
- `ApprovalRequest`

## Como aprovar

1. Acesse `/admin/approvals` ou o link direto no e-mail de notificação
2. Na aprovação do post Threads, revise: texto, CTA, hashtags, ângulo estratégico
3. Clique em **Aprovar** → `SocialPost.status = approved`, `approved_by`, `approved_at` preenchidos
4. Ou **Reprovar** → `status = rejected`

## Como agendar

No show do post aprovado (`/admin/social/threads/posts/{id}`):
1. Na sidebar, preencha a data/hora de publicação
2. Clique em **Agendar Publicação** → `status = scheduled`

## Como publicar manualmente

No show do post aprovado:
- Clique em **"🧵 Publicar agora"** (aparece apenas se todas as condições estiverem OK)
- Condições: `THREADS_PUBLISHING_ENABLED=true`, canal conectado, post aprovado, texto presente

## Como publicar via scheduler

O comando `threads:publish-due` roda a cada minuto e publica posts com:
- `platform=threads`, `content_type=threads_text`
- `status=scheduled`, `scheduled_at <= now()`
- `approved_by` preenchido
- `THREADS_PUBLISHING_ENABLED=true`

## Como diagnosticar erros

```bash
# Diagnóstico geral (configuração, canal, posts)
php artisan threads:diagnose

# Diagnóstico de post específico
php artisan threads:diagnose --post=ID

# Executar publicação manualmente
php artisan threads:publish-due

# Verificar logs
tail -n 200 storage/logs/laravel.log | grep -i threads

# Ver logs no painel
/admin/social/threads/logs
```

## Comandos

```bash
php artisan threads:diagnose               # Diagnóstico completo
php artisan threads:diagnose --post=ID     # Diagnóstico por post
php artisan threads:publish-due            # Publicar posts devidos
```

## Isolamento — Threads não quebra outros módulos

Threads usa dupla proteção para não impactar Blog, Social, Instagram, AgentTasks ou o Scheduler:

| Proteção | Padrão seguro |
|----------|--------------|
| `features.threads_publishing_scheduler` em `config/features.php` | `false` — command não entra no scheduler |
| `THREADS_PUBLISHING_ENABLED` em `.env` | `false` — command retorna SUCCESS sem publicar |
| `features.threads_text_publishing` | `true` — mas só permite criação, não publica |

Se `threads_publishing_scheduler=false`:
- `threads:publish-due` não aparece em `schedule:list`
- `content:run-publishing-cycle` pula a etapa Threads

Se `THREADS_PUBLISHING_ENABLED=false`:
- `threads:publish-due` retorna `SUCCESS` com aviso, sem publicar nada
- Não lança exception
- Não afeta agendamentos de Blog ou Social

**Para habilitar o scheduler do Threads** (após canal conectado e testado):
```php
// config/features.php
'threads_publishing_scheduler' => true,
```
```dotenv
# .env
THREADS_PUBLISHING_ENABLED=true
```

---

## O que está FORA desta fase

- Imagens no Threads
- Carrossel no Threads
- Vídeo no Threads
- Respostas automáticas
- Leitura de comentários
- Insights e métricas
- Publicação em massa
- Publicação sem aprovação
- Acesso pelo painel do cliente
