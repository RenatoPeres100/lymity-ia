<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\SocialChannel;
use Illuminate\Console\Command;

class InstagramDisconnectExpiredCommand extends Command
{
    protected $signature   = 'instagram:disconnect-expired';
    protected $description = 'Marca canais Instagram com token expirado/revogado como needs_reconnect, sem apagar histórico de posts.';

    public function handle(): int
    {
        $candidates = SocialChannel::where('platform', 'instagram')
            ->whereIn('status', ['error', 'expired'])
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Nenhum canal Instagram expirado ou com erro encontrado.');
            return self::SUCCESS;
        }

        $this->info("Canais candidatos: {$candidates->count()}");

        $marked = 0;

        foreach ($candidates as $channel) {
            $lastError = strtolower($channel->last_error ?? '');
            $isExpiredByDate = $channel->token_expires_at && $channel->token_expires_at->isPast();
            $isExpiredByError = str_contains($lastError, 'session has expired')
                || str_contains($lastError, 'token has expired')
                || str_contains($lastError, 'error validating access token')
                || str_contains($lastError, 'token revoked')
                || str_contains($lastError, 'token is invalid');

            if ($isExpiredByDate || $isExpiredByError || $channel->status === 'expired') {
                $channel->update([
                    'status'     => 'needs_reconnect',
                    'last_error' => $channel->last_error ?? 'Token expirado ou revogado pela Meta.',
                ]);

                $this->line("  Marcado needs_reconnect: Canal #{$channel->id} ({$channel->account_name})");

                try {
                    ActivityLog::create([
                        'user_id'     => null,
                        'action'      => 'instagram_marked_needs_reconnect',
                        'module'      => 'instagram',
                        'level'       => 'warning',
                        'description' => "Canal #{$channel->id} marcado como needs_reconnect por token expirado.",
                        'metadata'    => [
                            'channel_id'   => $channel->id,
                            'account_name' => $channel->account_name,
                            'reason'       => 'disconnect-expired command',
                        ],
                    ]);
                } catch (\Throwable) {}

                $marked++;
            }
        }

        $this->info("MARKED_NEEDS_RECONNECT={$marked}");

        return self::SUCCESS;
    }
}
