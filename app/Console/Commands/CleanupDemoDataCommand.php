<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AdCampaign;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\Budget;
use App\Models\Proposal;
use App\Models\SocialPost;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CleanupDemoDataCommand extends Command
{
    protected $signature   = 'system:cleanup-demo-data {--force : Remove sem confirmação}';
    protected $description = 'Remove dados demo/teste/mock do sistema. Preserva usuários admin, agência e funcionários IA.';

    private array $demoKeywords = ['demo', 'teste', 'mock', 'fase', 'test', 'sample', 'example', 'exemplo', 'placeholder'];

    public function handle(): int
    {
        $this->warn('=== LYMITY: Limpeza de Dados Demo ===');
        $this->newLine();

        $counts = $this->countDemoData();
        $this->renderTable($counts);

        $total = array_sum($counts);
        if ($total === 0) {
            $this->info('Nenhum dado demo encontrado. Sistema já está limpo.');
            return self::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Remover {$total} registros demo/teste/mock? Esta ação não pode ser desfeita.")) {
                $this->info('Operação cancelada.');
                return self::SUCCESS;
            }
        }

        $this->cleanup();

        $this->newLine();
        $this->info('Limpeza concluída.');
        return self::SUCCESS;
    }

    private function countDemoData(): array
    {
        return [
            'SocialPost (demo/teste)'   => $this->demoQuery(SocialPost::class, 'title')->count(),
            'BlogPost (demo/teste)'     => $this->demoQuery(BlogPost::class, 'title')->count(),
            'AdCampaign (demo/teste)'   => $this->demoQuery(AdCampaign::class, 'name')->count(),
            'Proposal (demo/teste)'     => $this->demoQuery(Proposal::class, 'title')->count(),
            'Budget (demo/teste)'       => $this->demoQuery(Budget::class, 'title')->count(),
            'ApprovalRequest (demo)'    => $this->demoQuery(ApprovalRequest::class, 'title')->count(),
            'ActivityLog (demo)'        => $this->demoQuery(ActivityLog::class, 'description')->count(),
        ];
    }

    private function renderTable(array $counts): void
    {
        $rows = array_map(fn($model, $count) => [$model, $count], array_keys($counts), $counts);
        $this->table(['Modelo', 'Registros demo'], $rows);
    }

    private function cleanup(): void
    {
        $removed = [];

        $removed['SocialPost']     = $this->demoQuery(SocialPost::class, 'title')->delete();
        $removed['BlogPost']       = $this->demoQuery(BlogPost::class, 'title')->delete();
        $removed['AdCampaign']     = $this->demoQuery(AdCampaign::class, 'name')->delete();
        $removed['Proposal']       = $this->demoQuery(Proposal::class, 'title')->delete();
        $removed['Budget']         = $this->demoQuery(Budget::class, 'title')->delete();
        $removed['ApprovalRequest']= $this->demoQuery(ApprovalRequest::class, 'title')->delete();
        $removed['ActivityLog']    = $this->demoQuery(ActivityLog::class, 'description')->delete();

        foreach ($removed as $model => $count) {
            if ($count > 0) {
                $this->line("  Removidos: {$model} — {$count} registros");
            }
        }
    }

    private function demoQuery(string $model, string $column = 'caption'): \Illuminate\Database\Eloquent\Builder
    {
        $query = $model::query();
        $first = true;

        foreach ($this->demoKeywords as $keyword) {
            if ($first) {
                $query->where($column, 'like', "%{$keyword}%");
                $first = false;
            } else {
                $query->orWhere($column, 'like', "%{$keyword}%");
            }
        }

        return $query;
    }
}
