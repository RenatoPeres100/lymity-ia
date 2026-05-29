<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\SocialChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class InstagramImportTestedTokenCommand extends Command
{
    protected $signature   = 'instagram:import-tested-token
                                {--from-env=META_TEST_USER_TOKEN : Variável .env com o token}
                                {--page-id=1069242536283477 : Facebook Page ID}
                                {--ig-user-id=17841434234661171 : Instagram User ID}
                                {--username=lymity.ia : Username Instagram}';

    protected $description = 'Importa token testado manualmente para o canal @lymity.ia (ponte operacional — OAuth substitui no futuro).';

    public function handle(): int
    {
        $this->warn('');
        $this->warn('ATENÇÃO: Este comando é uma ponte operacional para testes validados.');
        $this->warn('O OAuth do painel (/admin/social/instagram) é o processo definitivo.');
        $this->warn('');

        $envVar   = $this->option('from-env');
        $pageId   = $this->option('page-id');
        $igUserId = $this->option('ig-user-id');
        $username = $this->option('username');

        $token = env($envVar);

        if (empty($token)) {
            $this->error("Variável .env '{$envVar}' não encontrada ou vazia.");
            $this->line("Adicione ao .env: {$envVar}=EAA...");
            return self::FAILURE;
        }

        $this->info("Variável: {$envVar} (token encontrado, não impresso por segurança)");
        $this->info("Page ID : {$pageId}");
        $this->info("IG User : {$igUserId}");
        $this->info("Username: @{$username}");
        $this->info('');

        // Validate token via /me
        $this->line('Validando token via /me...');
        try {
            $meResp = Http::withoutVerifying()->timeout(10)
                ->get('https://graph.facebook.com/v25.0/me', ['access_token' => $token]);

            if ($meResp->failed()) {
                $err = $meResp->json('error.message') ?? 'HTTP ' . $meResp->status();
                $this->error("Falha ao validar /me: {$err}");
                return self::FAILURE;
            }
            $me = $meResp->json();
            $this->info("  /me OK — id={$me['id']}, name={$me['name']}");
        } catch (\Throwable $e) {
            $this->error("Erro ao validar /me: {$e->getMessage()}");
            return self::FAILURE;
        }

        // Validate permissions
        $this->line('Verificando permissões...');
        try {
            $permResp = Http::withoutVerifying()->timeout(10)
                ->get("https://graph.facebook.com/v25.0/me/permissions", ['access_token' => $token]);

            if ($permResp->successful()) {
                $granted = array_column(
                    array_filter($permResp->json('data', []), fn($p) => $p['status'] === 'granted'),
                    'permission'
                );
                $this->info('  Permissões concedidas: ' . implode(', ', $granted));

                $required = ['pages_read_engagement', 'instagram_content_publish'];
                $missing  = array_diff($required, $granted);
                if (!empty($missing)) {
                    $this->warn('  Permissões ausentes: ' . implode(', ', $missing));
                }
            }
        } catch (\Throwable $e) {
            $this->warn("  Não foi possível verificar permissões: {$e->getMessage()}");
        }

        // Validate page → instagram account
        $this->line("Verificando page {$pageId} → instagram_business_account...");
        try {
            $pageResp = Http::withoutVerifying()->timeout(10)
                ->get("https://graph.facebook.com/v25.0/{$pageId}", [
                    'fields'       => 'id,name,instagram_business_account',
                    'access_token' => $token,
                ]);

            if ($pageResp->successful()) {
                $page   = $pageResp->json();
                $igData = $page['instagram_business_account'] ?? null;
                $this->info("  Página: id={$page['id']}, name={$page['name']}");
                if ($igData) {
                    $this->info("  IG Business Account: id={$igData['id']}");
                } else {
                    $this->warn('  instagram_business_account não encontrado nesta página.');
                }
            }
        } catch (\Throwable $e) {
            $this->warn("  Não foi possível verificar página: {$e->getMessage()}");
        }

        if (!$this->confirm('Salvar token criptografado no canal @lymity.ia (social_channels)?')) {
            $this->info('Cancelado pelo usuário.');
            return self::SUCCESS;
        }

        // Upsert channel
        $company = Company::first();

        $channel = SocialChannel::firstOrNew([
            'platform'   => 'instagram',
            'company_id' => $company?->id,
            'client_id'  => null,
        ]);

        $channel->fill([
            'account_name'        => "@{$username}",
            'account_url'         => "https://instagram.com/{$username}",
            'status'              => 'connected',
            'facebook_page_id'    => $pageId,
            'instagram_user_id'   => $igUserId,
            'external_account_id' => $igUserId,
            'last_checked_at'     => now(),
            'last_error'          => null,
            'permissions'         => 'pages_show_list,pages_read_engagement,business_management,instagram_basic,instagram_content_publish',
        ]);

        // The model's cast 'encrypted' handles encryption automatically
        // Do NOT pre-encrypt — just assign the raw token
        $channel->access_token = $token;

        $channel->save();

        $this->info('');
        $this->info("Canal salvo: #{$channel->id} — @{$username}");
        $this->info('  Status: connected');
        $this->info('  Token armazenado de forma segura (não impresso).');

        try {
            ActivityLog::create([
                'user_id'     => null,
                'action'      => 'instagram_token_imported',
                'module'      => 'instagram',
                'level'       => 'warning',
                'description' => "Token Instagram importado via comando para @{$username}",
                'metadata'    => [
                    'channel_id' => $channel->id,
                    'page_id'    => $pageId,
                    'ig_user_id' => $igUserId,
                    'username'   => $username,
                    'source'     => $envVar,
                ],
            ]);
        } catch (\Throwable) {}

        $this->info('');
        $this->info('Próximos passos:');
        $this->line("  php artisan instagram:diagnose-publishing");
        $this->line("  php artisan social:generate-test-post");

        return self::SUCCESS;
    }
}
