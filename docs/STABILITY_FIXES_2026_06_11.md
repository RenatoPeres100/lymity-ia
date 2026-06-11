# Stability Fixes — 2026-06-11

Fase de estabilização executada após o commit `e907e0d` (Threads text publishing).

---

## 1. Threads sem configuração — tratado

**Problema:** As variáveis `THREADS_APP_ID`, `THREADS_APP_SECRET` e `THREADS_REDIRECT_URI` não estão definidas no `.env` de produção. A tela `/admin/social/threads` mostrava "não configurado" corretamente, mas o diagnose não orientava o operador.

**Correção:**
- `threads:diagnose` agora exibe orientação completa quando config ausente:
  ```
  Defina no .env: THREADS_APP_ID, THREADS_APP_SECRET, THREADS_REDIRECT_URI
  Em seguida rode: php artisan config:clear
  ```
- Tela `/admin/social/threads` exibe aviso claro sem tentar OAuth.
- `THREADS_PUBLISHING_ENABLED=false` bloqueia publicação mesmo se canal conectado.

**Para ativar Threads:**
1. Criar app no [Meta for Developers](https://developers.facebook.com) com produto Threads.
2. Adicionar no `.env`:
   ```env
   THREADS_APP_ID=<seu_app_id>
   THREADS_APP_SECRET=<seu_app_secret>
   THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
   THREADS_GRAPH_VERSION=v1.0
   THREADS_BASE_URL=https://graph.threads.net
   THREADS_PUBLISHING_ENABLED=false
   THREADS_SCOPES=threads_basic,threads_content_publish
   ```
3. `php artisan config:clear`
4. Acessar `/admin/social/threads` e clicar "Conectar Threads".
5. Após testar, mudar para `THREADS_PUBLISHING_ENABLED=true`.

---

## 2. Blade $slot undefined — corrigido

**Problema:** As 6 views criadas no commit `e907e0d` usavam sintaxe `@extends('components.layouts.app')` + `@section('content')`. O layout `app.blade.php` usa `{{ $slot }}` (componente anônimo), que é incompatível com herança Blade. Resultado: HTTP 500 em todas as rotas Threads.

**Views corrigidas:**
- `resources/views/admin/social/threads/index.blade.php`
- `resources/views/admin/social/threads/logs.blade.php`
- `resources/views/admin/social/threads/posts/index.blade.php`
- `resources/views/admin/social/threads/posts/create.blade.php`
- `resources/views/admin/social/threads/posts/show.blade.php`
- `resources/views/admin/social/threads/posts/edit.blade.php`

**Padrão correto** (igual ao resto do projeto):
```blade
<x-layouts.app title="Título da Página">
    <!-- conteúdo aqui -->
</x-layouts.app>
```

**Regra:** Nunca usar `@extends('components.layouts.app')` — sempre usar `<x-layouts.app>`.

---

## 3. Blog payload parcial — tratado

**Problema anterior:** Quando o Gemini retornava JSON com apenas `content_type` e `title` (sem artigo), ou com content como array (ex: `sections`, `paragraphs`), a validação lançava `TypeError: markdownToHtml(): Argument #1 must be string, array given`.

**Correções em `AIContentPayloadValidatorService`:**
- Adicionados aliases `sections` e `paragraphs` à lista de campos de conteúdo.
- Criado helper `stringifyContentValue(mixed $value, string $fieldName)` que:
  - String → retorna diretamente (trim).
  - Array de sections `[{heading, body}]` → converte para markdown com `## Heading`.
  - Array de parágrafos `['string', ...]` → une com `\n\n`.
  - Array genérico → tenta sub-keys conhecidos ou JSON fallback.
  - `null` → string vazia.
- `markdownToHtml(string $markdown)` continua com type hint `string` (seguro).
- Payload parcial (só `title` + `content_type`) falha com `AIInvalidJsonResponseException` controlada.
- Artigo com menos de `AI_BLOG_MIN_WORDS` (default 500) falha controlado.

---

## 4. markdownToHtml array — corrigido

Antes, o array-flattening estava na chamada de `validateBlogPostPayload`, mas não cobria todos os aliases. Com o `stringifyContentValue()`, qualquer alias converte para string antes de chegar em `markdownToHtml`.

---

## 5. AI Employee Skills FK — sem problema atual

Verificação realizada:
- `ai_employee_skill`: 28 pivots, 0 órfãos.
- `ai_employees`: 10 registros.
- `ai_skills`: 24 registros.

**Prevenção:** Criado `php artisan ai:repair-employee-skills [--dry-run] [--fix]`:
- Detecta pivots com employee ou skill inexistente.
- `--dry-run` (default) lista problemas.
- `--fix` remove pivots órfãos e cria skills obrigatórias ausentes.

---

## 6. Redis Connection Refused — falso alarme

Redis estava e está rodando (`redis-cli ping` retorna `PONG`). O erro visto nos logs era de sessão anterior, não do estado atual.

**Verificação:**
```bash
redis-cli ping        # deve retornar PONG
systemctl status redis-server
```

**Se cair:** `sudo systemctl start redis-server && sudo systemctl enable redis-server`

**Alternativa temporária** (sem redis): `QUEUE_CONNECTION=database` no `.env`.

---

## 7. --columns option does not exist

**Problema:** Chamada manual `php artisan route:list --columns=name,uri` durante diagnose. A opção `--columns` não existe nesta versão do Laravel.

**Correto:**
```bash
php artisan route:list              # lista completa
php artisan route:list --name=foo   # filtrar por nome
grep "threads" <<< $(php artisan route:list)  # filtrar externamente
```

---

## 8. Blog — configuração de tokens

O `.env` real tem `AI_MAX_TOKENS=1200` (limite genérico legado). Para blog, o limite correto é:

```env
AI_BLOG_MAX_OUTPUT_TOKENS=6000
GEMINI_BLOG_MAX_OUTPUT_TOKENS=6000   # alias documentado
AI_BLOG_MIN_WORDS=500
```

`config/ai.php` lê `AI_BLOG_MAX_OUTPUT_TOKENS` com fallback para `GEMINI_BLOG_MAX_OUTPUT_TOKENS`, default 6000. `GoogleGeminiProvider` usa `config('ai.blog_max_output_tokens')` para tasks de blog.

---

## 9. Comandos criados/ajustados

| Comando | Descrição |
|---------|-----------|
| `php artisan lymity:stability-check` | Verifica 30 pontos críticos de estabilidade |
| `php artisan ai:repair-employee-skills --dry-run` | Lista orphan pivots e skills ausentes |
| `php artisan ai:repair-employee-skills --fix` | Corrige orphan pivots |
| `php artisan threads:diagnose` | Ajustado com orientação quando config ausente |

---

## 10. Próximos passos

1. **Configurar Threads:** ver seção 1 acima.
2. **Adicionar ao .env real:**
   ```env
   AI_BLOG_MAX_OUTPUT_TOKENS=6000
   GEMINI_BLOG_MAX_OUTPUT_TOKENS=6000
   AI_BLOG_MIN_WORDS=500
   ```
   Depois: `php artisan optimize:clear && php artisan queue:restart`
3. **Monitorar blog writer:** `php artisan agents:diagnose-execution-engine` após próxima execução de task blog.
4. **Verificar failed jobs:** `php artisan queue:retry all` para reprocessar.

---

## Resultado lymity:stability-check (2026-06-11)

```
26 OK | 4 avisos (Threads sem config — esperado) | 0 erros | 30 verificações
```
