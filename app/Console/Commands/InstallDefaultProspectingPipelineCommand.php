<?php

namespace App\Console\Commands;

use App\Models\Company;
use Database\Seeders\ProspectingSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InstallDefaultProspectingPipelineCommand extends Command
{
    protected $signature = 'prospecting:install-default-pipeline
                            {--demo : Create demo leads}
                            {--force : Recreate pipeline even if exists}';

    protected $description = 'Instala o pipeline padrão de prospecção para a agência';

    public function handle(): int
    {
        $company = Company::first();
        if (!$company) {
            $this->error('Nenhuma company encontrada. Execute os seeders básicos primeiro.');
            return 1;
        }

        $seeder = new ProspectingSeeder();
        $pipeline = $seeder->installForCompany($company, (bool) $this->option('force'));

        $stageCount = $pipeline->stages()->count();

        $this->info("Pipeline padrão instalado para: {$company->name}");
        $this->info("Pipeline: {$pipeline->name} (ID: {$pipeline->id})");
        $this->info("Etapas: {$stageCount}");

        if ($this->option('demo') && (app()->environment('local', 'staging') || $this->option('force'))) {
            $this->createDemoLeads($pipeline, $company);
        }

        Log::info("[prospecting:install-default-pipeline] Pipeline ID {$pipeline->id} instalado. Stages: {$stageCount}");
        return 0;
    }

    private function createDemoLeads($pipeline, $company): void
    {
        $firstStage = $pipeline->stages()->orderBy('position')->first();
        if (!$firstStage) return;

        $demoLeads = [
            ['name' => 'Dr. Carlos Mendes', 'company_name' => 'Clínica Estética Vitalis', 'segment' => 'Clínica de estética', 'interest_level' => 'warm'],
            ['name' => 'Ana Beatriz Ferreira', 'company_name' => 'Restaurante Sabor Fusion', 'segment' => 'Alimentação', 'interest_level' => 'cold'],
            ['name' => 'Lucas Teixeira', 'company_name' => 'LT Advocacia', 'segment' => 'Jurídico', 'interest_level' => 'hot'],
        ];

        foreach ($demoLeads as $data) {
            \App\Models\ProspectLead::firstOrCreate(
                ['company_name' => $data['company_name'], 'company_id' => $company->id],
                array_merge($data, [
                    'company_id'          => $company->id,
                    'prospect_pipeline_id' => $pipeline->id,
                    'prospect_stage_id'   => $firstStage->id,
                    'source'              => 'demo',
                    'status'              => 'open',
                ])
            );
        }

        $this->info("Leads demo criados: " . count($demoLeads));
    }
}
