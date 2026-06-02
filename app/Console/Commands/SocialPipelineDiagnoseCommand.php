<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use Illuminate\Console\Command;

class SocialPipelineDiagnoseCommand extends Command
{
    protected $signature   = 'social:pipeline-diagnose';
    protected $description = 'Diagnóstico do pipeline de posts sociais.';

    public function handle(): int
    {
        $this->info('=== SOCIAL PIPELINE DIAGNOSE ===');

        $all = SocialPost::all();

        $byStatus = $all->groupBy('status');
        $statuses = ['draft', 'pending_approval', 'approved', 'scheduled', 'published', 'failed', 'rejected', 'archived'];

        $this->line('');
        $this->info('[Status counts]');
        foreach ($statuses as $s) {
            $count = isset($byStatus[$s]) ? $byStatus[$s]->count() : 0;
            $this->line("  {$s}: {$count}");
        }

        $approvedFutureScheduled = SocialPost::where('status', 'approved')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->count();

        $approvedInvalidImage = SocialPost::where('status', 'approved')
            ->where(function ($q) {
                $q->where('image_validation_status', '!=', 'valid')
                  ->orWhereNull('public_image_url');
            })->count();

        $scheduledInvalidImage = SocialPost::where('status', 'scheduled')
            ->where(function ($q) {
                $q->where('image_validation_status', '!=', 'valid')
                  ->orWhereNull('public_image_url');
            })->count();

        $dueNow = SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->count();

        $this->line('');
        $this->info('[Alertas]');
        $this->line("  approved com scheduled_at futuro (não agendados): {$approvedFutureScheduled}");
        $this->line("  approved com imagem inválida: {$approvedInvalidImage}");
        $this->line("  scheduled sem imagem válida: {$scheduledInvalidImage}");
        $this->line("  due agora (deveriam publicar): {$dueNow}");

        if ($approvedFutureScheduled > 0) {
            $this->warn("  ⚠ Rode: php artisan content:fix-approved-scheduled");
        }
        if ($scheduledInvalidImage > 0) {
            $this->warn("  ⚠ Posts agendados sem imagem válida — publicação vai falhar!");
        }
        if ($dueNow > 0) {
            $this->warn("  ⚠ Posts vencidos — rode: php artisan social:publish-due");
        }

        $this->line('');
        $this->info('=== FIM DO DIAGNÓSTICO ===');

        return self::SUCCESS;
    }
}
