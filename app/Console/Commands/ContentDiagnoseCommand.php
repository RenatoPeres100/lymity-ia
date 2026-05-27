<?php

namespace App\Console\Commands;

use App\Models\SocialChannel;
use App\Models\SocialPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContentDiagnoseCommand extends Command
{
    protected $signature   = 'content:diagnose';
    protected $description = 'Diagnóstico completo da operação de conteúdo e publicação Instagram.';

    public function handle(): int
    {
        $this->info('=== CONTENT & INSTAGRAM DIAGNOSE ===');
        $ok     = true;
        $checks = [];

        // META config
        $checks[] = $this->check('META_APP_ID configurado',      !empty(config('meta.app_id')));
        $checks[] = $this->check('META_APP_SECRET configurado',  !empty(config('meta.app_secret')));
        $checks[] = $this->check('META_REDIRECT_URI correto',    config('meta.redirect_uri') === 'https://ia.lymity.com.br/admin/social/instagram/callback');
        $checks[] = $this->check('META_GRAPH_VERSION configurado', !empty(config('meta.graph_version')));

        $publishingEnabled = config('meta.instagram_publishing_enabled', false);
        $this->line('  INSTAGRAM_PUBLISHING_ENABLED=' . ($publishingEnabled ? 'true' : 'false'));

        // APP_URL
        $appUrl = config('app.url');
        $this->line("  APP_URL={$appUrl}");
        $checks[] = $this->check('APP_URL is https://ia.lymity.com.br', $appUrl === 'https://ia.lymity.com.br');

        // Storage link
        $storageLinkExists = file_exists(public_path('storage'));
        $checks[] = $this->check('storage:link existe (public/storage)', $storageLinkExists);
        if (!$storageLinkExists) {
            $this->warn("    → Rode: php artisan storage:link");
        }

        // Instagram channel
        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        $checks[] = $this->check('Canal @lymity.ia existe', $channel !== null);

        if ($channel) {
            $checks[] = $this->check('Canal status=connected', $channel->status === 'connected');
            $checks[] = $this->check('instagram_user_id preenchido', !empty($channel->instagram_user_id));
            $checks[] = $this->check('facebook_page_id preenchido',  !empty($channel->facebook_page_id));

            if ($channel->token_expires_at) {
                $expired = $channel->token_expires_at->isPast();
                $checks[] = $this->check(
                    'Token válido (expira ' . $channel->token_expires_at->format('d/m/Y') . ')',
                    !$expired
                );
            } else {
                $this->warn('  token_expires_at não definido.');
            }

            if ($channel->last_error) {
                $this->warn("  Último erro: {$channel->last_error}");
            }
        }

        // Social posts
        $scheduledCount = SocialPost::whereNull('client_id')
            ->where('status', 'scheduled')
            ->count();
        $this->line("  SocialPost scheduled (agência): {$scheduledCount}");

        $withImage = SocialPost::whereNull('client_id')
            ->whereNotNull('public_image_url')
            ->where('public_image_url', 'like', 'https://%')
            ->count();
        $this->line("  SocialPost com public_image_url (HTTPS): {$withImage}");

        // Queue
        try {
            $pending = DB::table('jobs')->count();
            $this->line("  Jobs na fila: {$pending}");
            $checks[] = $this->check('Tabela jobs acessível', true);
        } catch (\Throwable) {
            $checks[] = $this->check('Tabela jobs acessível', false);
        }

        // Scheduler
        $this->line('  Scheduler: verifique se está registrado no cron (php artisan schedule:run)');

        // Summary
        $this->newLine();
        $passed = count(array_filter($checks));
        $total  = count($checks);
        $this->info("RESULTADO: {$passed}/{$total} verificações passaram.");

        if ($passed < $total) {
            $this->warn('Alguns itens precisam de atenção. Revise os avisos acima.');
        } else {
            $this->info('Tudo configurado corretamente!');
        }

        return self::SUCCESS;
    }

    private function check(string $label, bool $result): bool
    {
        if ($result) {
            $this->line("  <fg=green>✔</> {$label}");
        } else {
            $this->line("  <fg=red>✘</> {$label}");
        }
        return $result;
    }
}
