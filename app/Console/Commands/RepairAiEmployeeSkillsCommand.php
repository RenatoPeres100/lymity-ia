<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairAiEmployeeSkillsCommand extends Command
{
    protected $signature = 'ai:repair-employee-skills
                            {--fix : Remove orphan pivots and ensure default skills exist}
                            {--dry-run : Only list issues without fixing (default)}';

    protected $description = 'Diagnostica e repara pivots órfãos em ai_employee_skill';

    /** Core skill keys that must exist (matches ai_skills.key values in DB) */
    private array $requiredSkillKeys = [
        'copywriting', 'content.create', 'content.calendar',
        'social.strategy', 'seo.audit', 'keyword.research',
        'data.analysis', 'reports', 'task.planning',
    ];

    public function handle(): int
    {
        $isDryRun = !$this->option('fix');

        $this->info('');
        $this->info('=== ai:repair-employee-skills ===');
        $this->info($isDryRun ? 'Modo: DRY-RUN (use --fix para aplicar)' : 'Modo: FIX');
        $this->info('');

        // 1. Find orphan pivots (employee or skill missing)
        $orphans = DB::table('ai_employee_skill as p')
            ->leftJoin('ai_employees as e', 'p.ai_employee_id', '=', 'e.id')
            ->leftJoin('ai_skills as s', 'p.ai_skill_id', '=', 's.id')
            ->select('p.id', 'p.ai_employee_id', 'p.ai_skill_id')
            ->where(function ($q) {
                $q->whereNull('e.id')->orWhereNull('s.id');
            })
            ->get();

        if ($orphans->isEmpty()) {
            $this->line('  ✓ Nenhum pivot órfão encontrado.');
        } else {
            $this->warn("  ✗ {$orphans->count()} pivot(s) órfão(s) encontrado(s):");
            foreach ($orphans as $o) {
                $this->line("    id={$o->id} | ai_employee_id={$o->ai_employee_id} | ai_skill_id={$o->ai_skill_id}");
            }

            if (!$isDryRun) {
                $ids = $orphans->pluck('id')->toArray();
                DB::table('ai_employee_skill')->whereIn('id', $ids)->delete();
                $this->info("  → {$orphans->count()} pivot(s) órfão(s) removido(s).");
            }
        }

        // 2. Check required skills exist in ai_skills table (key column)
        $this->info('');
        $this->info('Skills obrigatórias (por key):');
        $requiredKeys = collect($this->requiredSkillKeys);
        $existingKeys = DB::table('ai_skills')->whereIn('key', $requiredKeys)->pluck('key');
        $missing = $requiredKeys->diff($existingKeys);

        if ($missing->isEmpty()) {
            $this->line('  ✓ Todas as skills obrigatórias existem (' . $requiredKeys->count() . ' verificadas).');
        } else {
            $this->warn("  ✗ Skills ausentes (key): " . $missing->implode(', '));
            if (!$isDryRun) {
                foreach ($missing as $key) {
                    DB::table('ai_skills')->insert([
                        'key'         => $key,
                        'name'        => ucwords(str_replace(['.', '_'], ' ', $key)),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $this->line("  → Skill criada: {$key}");
                }
            }
        }

        // 3. Summary counts
        $this->info('');
        $this->info('Contagens atuais:');
        $this->line('  ai_employees : ' . DB::table('ai_employees')->count());
        $this->line('  ai_skills    : ' . DB::table('ai_skills')->count());
        $this->line('  pivot total  : ' . DB::table('ai_employee_skill')->count());

        if ($isDryRun && (!$orphans->isEmpty() || $missing->isNotEmpty())) {
            $this->info('');
            $this->warn('Execute com --fix para corrigir os problemas acima.');
        }

        $this->info('');
        return self::SUCCESS;
    }
}
