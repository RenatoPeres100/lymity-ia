<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProspectPipeline;
use App\Models\ProspectStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProspectingSeeder extends Seeder
{
    public static array $defaultStages = [
        ['name' => 'Novo lead',              'key' => 'new_lead',         'position' => 1,  'color' => '#64748b', 'is_won' => false, 'is_lost' => false],
        ['name' => 'Pesquisado',             'key' => 'researched',       'position' => 2,  'color' => '#3b82f6', 'is_won' => false, 'is_lost' => false],
        ['name' => 'Primeiro contato feito', 'key' => 'first_contact',    'position' => 3,  'color' => '#8b5cf6', 'is_won' => false, 'is_lost' => false],
        ['name' => 'Respondeu',              'key' => 'responded',        'position' => 4,  'color' => '#06b6d4', 'is_won' => false, 'is_lost' => false],
        ['name' => 'Qualificado',            'key' => 'qualified',        'position' => 5,  'color' => '#f59e0b', 'is_won' => false, 'is_lost' => false],
        ['name' => 'Reunião agendada',       'key' => 'meeting_scheduled','position' => 6,  'color' => '#f97316', 'is_won' => false, 'is_lost' => false],
        ['name' => 'Proposta enviada',       'key' => 'proposal_sent',    'position' => 7,  'color' => '#ec4899', 'is_won' => false, 'is_lost' => false],
        ['name' => 'Negociação',             'key' => 'negotiation',      'position' => 8,  'color' => '#ef4444', 'is_won' => false, 'is_lost' => false],
        ['name' => 'Fechado',                'key' => 'won',              'position' => 9,  'color' => '#22c55e', 'is_won' => true,  'is_lost' => false],
        ['name' => 'Perdido',                'key' => 'lost',             'position' => 10, 'color' => '#94a3b8', 'is_won' => false, 'is_lost' => true],
    ];

    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            $this->command->warn('Nenhuma company encontrada. Pulando ProspectingSeeder.');
            return;
        }

        $this->installForCompany($company);
    }

    public function installForCompany(Company $company, bool $force = false): ProspectPipeline
    {
        $existing = ProspectPipeline::where('company_id', $company->id)
            ->where('is_default', true)
            ->first();

        if ($existing && !$force) {
            return $existing;
        }

        $pipeline = ProspectPipeline::updateOrCreate(
            ['company_id' => $company->id, 'is_default' => true],
            [
                'name'        => 'Pipeline Comercial Lymity',
                'description' => 'Pipeline padrão de prospecção comercial da Lymity IA',
                'status'      => 'active',
            ]
        );

        $created = 0;
        foreach (self::$defaultStages as $stageData) {
            $exists = ProspectStage::where('prospect_pipeline_id', $pipeline->id)
                ->where('key', $stageData['key'])
                ->exists();

            if (!$exists || $force) {
                ProspectStage::updateOrCreate(
                    ['prospect_pipeline_id' => $pipeline->id, 'key' => $stageData['key']],
                    array_merge($stageData, ['status' => 'active'])
                );
                $created++;
            }
        }

        Log::info("[ProspectingSeeder] Pipeline instalado para company #{$company->id}. Stages criados: {$created}");
        return $pipeline;
    }
}
