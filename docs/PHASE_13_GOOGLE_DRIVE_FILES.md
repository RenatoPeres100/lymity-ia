# Fase 13 — Arquivos e Google Drive

## Objetivo

Criar um módulo completo de gestão de arquivos para a agência e para os clientes, com armazenamento local funcional e estrutura preparada para futura integração com Google Drive via OAuth.

## Models

### StorageIntegration
Representa uma integração de armazenamento (local ou Google Drive) associada a uma empresa ou cliente.

```
storage_integrations
├── id
├── provider (google_drive | local)
├── client_id nullable → clients
├── company_id nullable → companies
├── status (active | disconnected | error)
├── access_token nullable (oculto)
├── refresh_token nullable (oculto)
├── token_expires_at nullable
├── root_folder_id nullable
├── metadata json nullable
└── timestamps
```

### ExternalFile
Representa um arquivo armazenado (local ou externo), com suporte a polimorfismo para vincular a outros módulos.

```
external_files
├── id
├── client_id nullable → clients
├── company_id nullable → companies
├── storage_integration_id nullable → storage_integrations
├── name
├── file_type (image | pdf | document | video | audio | other)
├── mime_type nullable
├── size nullable (bytes)
├── local_path nullable (relativo a storage/app/public)
├── external_url nullable
├── external_file_id nullable (Google Drive file ID)
├── source (upload | google_drive | generated | imported)
├── related_type nullable (polimorfismo)
├── related_id nullable (polimorfismo)
├── uploaded_by nullable → users
├── notes nullable
└── timestamps
```

### ClientFolder
Pastas lógicas de organização por cliente.

```
client_folders
├── id
├── client_id → clients (cascade delete)
├── name
├── description nullable
├── external_folder_id nullable
├── local_path nullable
└── timestamps
```

## Migrations

- `2026_05_25_1300001_create_storage_integrations_table.php`
- `2026_05_25_1300002_create_external_files_table.php`
- `2026_05_25_1300003_create_client_folders_table.php`

## Storage Local

Uploads são armazenados em `storage/app/public/`:
- Arquivos de clientes: `client-files/{client_id}/`
- Arquivos da agência: `company-files/{company_id}/`

O link simbólico é criado via:
```bash
php artisan storage:link
```

Após o link, os arquivos ficam acessíveis em `public/storage/`.

## Rotas Admin

| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| GET | /admin/files | admin.files.index | Listagem geral |
| GET | /admin/files/create | admin.files.create | Formulário de upload |
| POST | /admin/files | admin.files.store | Salvar upload |
| GET | /admin/files/{file} | admin.files.show | Detalhe do arquivo |
| DELETE | /admin/files/{file} | admin.files.destroy | Remover arquivo |
| POST | /admin/files/{file}/relate | admin.files.relate | Vincular a módulo |
| GET | /admin/files/google-drive/connect | admin.files.google-drive | Placeholder Google Drive |
| GET | /admin/clients/{client}/files | admin.clients.files.index | Arquivos do cliente |
| GET | /admin/clients/{client}/files/create | admin.clients.files.create | Upload para cliente |
| POST | /admin/clients/{client}/files | admin.clients.files.store | Salvar upload cliente |
| GET | /admin/clients/{client}/files/{file} | admin.clients.files.show | Detalhe |
| DELETE | /admin/clients/{client}/files/{file} | admin.clients.files.destroy | Remover |
| POST | /admin/clients/{client}/folders | admin.clients.folders.store | Criar pasta |
| GET | /admin/clients/{client}/files/google-drive | admin.clients.files.google-drive | Placeholder |

## Rotas Cliente

| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| GET | /client/files | client.files.index | Meus arquivos |
| GET | /client/files/create | client.files.create | Upload |
| POST | /client/files | client.files.store | Salvar upload |
| GET | /client/files/{file} | client.files.show | Detalhe |
| DELETE | /client/files/{file} | client.files.destroy | Remover |
| POST | /client/folders | client.folders.store | Criar pasta |
| GET | /client/files/google-drive/connect | client.files.google-drive | Placeholder |

## Services

### FileStorageService (`app/Services/Files/FileStorageService.php`)

| Método | Descrição |
|--------|-----------|
| `uploadLocal(file, data, user)` | Faz upload local e cria registro ExternalFile |
| `listForAdmin(filters)` | Lista todos os arquivos com filtros |
| `listForClient(client, filters)` | Lista apenas arquivos do cliente (isolamento) |
| `deleteFile(file, user)` | Remove arquivo físico e registro |
| `createClientFolder(client, data)` | Cria pasta lógica e diretório físico |
| `getPublicUrl(file)` | Retorna URL pública do arquivo |
| `relateFile(file, type, id)` | Vincula arquivo a outro modelo |

### GoogleDriveService (`app/Services/Files/GoogleDriveService.php`)

| Método | Descrição |
|--------|-----------|
| `isConfigured()` | Verifica se credenciais estão definidas no .env |
| `connectPlaceholder(client?)` | Retorna mensagem informativa sem OAuth real |
| `uploadPlaceholder()` | Placeholder para upload |
| `listPlaceholder()` | Placeholder para listagem |
| `getAuthUrlPlaceholder()` | Placeholder para URL OAuth |

## Placeholder Google Drive

Enquanto as credenciais OAuth não estiverem configuradas, o sistema exibe uma mensagem clara sem quebrar:

> "Google Drive ainda não configurado. Configure GOOGLE_DRIVE_CLIENT_ID, GOOGLE_DRIVE_CLIENT_SECRET e GOOGLE_DRIVE_REDIRECT_URI."

## Variáveis .env

```
GOOGLE_DRIVE_ENABLED=false
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REDIRECT_URI=
```

Arquivo de configuração: `config/google-drive.php`

## Regras de Segurança

- Cliente só acessa arquivos com `client_id === seu_client_id`
- `abort(403)` se cliente tenta acessar arquivo de outro cliente
- `access_token` e `refresh_token` estão em `$hidden` no model
- Nunca expor tokens no front-end ou em logs
- Arquivos ficam em `storage/app/public` — nunca dentro de `public/` diretamente
- Nginx deve bloquear acesso a `storage/app/` (apenas `public/storage/` via symlink)

## Isolamento do Cliente

- `FileStorageService::listForClient()` força `where('client_id', $client->id)`
- `ClientFileController::show()` e `destroy()` verificam `abort_if($file->client_id !== $client->id, 403)`
- `Client\FileController` obtém o cliente via `$request->user()->client` — nunca via parâmetro de rota

## storage:link

```bash
php artisan storage:link
```

Cria `public/storage -> storage/app/public`. Se já existir, não é erro.

## Testes

```bash
# Migrations e seeds
php artisan migrate
php artisan db:seed

# storage:link
php artisan storage:link

# Verificar banco
php artisan tinker --execute="echo App\Models\StorageIntegration::count(); echo App\Models\ExternalFile::count(); echo App\Models\ClientFolder::count();"

# Testar Google Drive sem credenciais
php artisan tinker --execute="\$s=app(App\Services\Files\GoogleDriveService::class); var_dump(\$s->isConfigured(), \$s->connectPlaceholder());"

# Fazer upload via UI
# http://IP:8000/admin/files/create
```

## Regressão

Todas as rotas das fases anteriores continuam funcionando. Verificar:
```bash
curl -I http://127.0.0.1:8000/admin/files    # 302
curl -I http://127.0.0.1:8000/client/files   # 302
```

## Próximos Passos — OAuth Google Drive

Para implementar o fluxo OAuth real:

1. Criar projeto no Google Cloud Console
2. Ativar Google Drive API
3. Configurar credenciais OAuth 2.0 (Web application)
4. Adicionar URI de redirecionamento autorizado
5. Preencher `.env` com `GOOGLE_DRIVE_CLIENT_ID`, `GOOGLE_DRIVE_CLIENT_SECRET`, `GOOGLE_DRIVE_REDIRECT_URI`
6. Implementar fluxo em `GoogleDriveService`: `getAuthUrl()`, `handleCallback()`, `storeTokens()`
7. Usar `StorageIntegration` para persistir tokens (criptografados)
8. Implementar `uploadToGoogleDrive()`, `listFromGoogleDrive()`, `downloadFromGoogleDrive()`
