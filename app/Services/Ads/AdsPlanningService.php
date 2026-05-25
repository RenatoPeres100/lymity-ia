<?php

namespace App\Services\Ads;

use App\Models\ActivityLog;
use App\Models\AdAccount;
use App\Models\AdAudience;
use App\Models\AdCampaign;
use App\Models\AdCreative;
use App\Models\AdGroup;
use App\Models\AdKeyword;
use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\CampaignBudgetChange;
use App\Models\User;
use App\Services\Ai\MockAiProvider;

class AdsPlanningService
{
    public function __construct(
        protected MockAiProvider          $mockAi,
        protected CampaignApprovalService $approvalService,
        protected GoogleAdsService        $googleAds,
        protected MetaAdsService          $metaAds,
    ) {}

    public function createAccount(array $data): AdAccount
    {
        $account = AdAccount::create($data);
        $this->log(null, 'Conta de anúncio criada: ' . $account->account_name, 'ads');
        return $account;
    }

    public function createCampaignPlan(array $data, User $user): AdCampaign
    {
        $campaign = AdCampaign::create(array_merge($data, ['created_by' => $user->id]));
        $this->log($user, "Campanha criada: {$campaign->name}", 'ads', $campaign->client_id);
        return $campaign;
    }

    public function generateCampaignWithAi(array $data, User $user): AdCampaign|AiTask
    {
        $platform   = $data['platform'] ?? 'google_ads';
        $clientId   = $data['client_id'] ?? null;
        $objective  = $data['objective'] ?? 'leads';
        $budget     = $data['daily_budget'] ?? 50;

        $client     = $clientId ? \App\Models\Client::find($clientId) : null;
        $clientName = $client?->name ?? 'Cliente';

        $aiEmployee = AiEmployee::where('role_key', 'traffic_manager')
            ->orWhere('name', 'like', '%Tráfego%')
            ->first()
            ?? AiEmployee::first();

        $context = [
            'platform'    => $platform,
            'objective'   => $objective,
            'daily_budget' => $budget,
            'client_name' => $clientName,
            'prompt'      => $data['prompt'] ?? null,
        ];

        $sandboxData = $platform === 'meta_ads'
            ? $this->metaAds->sandboxGenerateCampaign($context)
            : $this->googleAds->sandboxGenerateCampaign($context);

        $account = AdAccount::where('client_id', $clientId)
            ->where('platform', $platform)
            ->where('status', 'active')
            ->first()
            ?? AdAccount::where('platform', $platform)->where('status', 'active')->first();

        if (!$account) {
            $account = AdAccount::create([
                'client_id'    => $clientId,
                'platform'     => $platform,
                'account_name' => "Conta {$sandboxData['platform']} — {$clientName}",
                'status'       => 'active',
            ]);
        }

        $strategy = $sandboxData['strategy'] ?? "Campanha {$objective} gerada por IA em modo sandbox.";

        $campaign = AdCampaign::create([
            'ad_account_id'    => $account->id,
            'client_id'        => $clientId,
            'created_by'       => $user->id,
            'ai_employee_id'   => $aiEmployee?->id,
            'platform'         => $platform,
            'name'             => $sandboxData['campaign']['name'] ?? "Campanha {$objective} — {$clientName}",
            'objective'        => $objective,
            'status'           => 'draft',
            'daily_budget'     => $budget,
            'requires_approval' => true,
            'strategy_summary' => $strategy,
        ]);

        $this->createGroupsAndCreatives($campaign, $sandboxData, $platform);

        $this->log($user, "Campanha IA gerada: {$campaign->name}", 'ads', $clientId);

        if ($campaign->requires_approval) {
            $campaign->update(['status' => 'pending_approval']);
            $this->approvalService->createCampaignApproval($campaign, $user);
        }

        return $campaign;
    }

    private function createGroupsAndCreatives(AdCampaign $campaign, array $sandboxData, string $platform): void
    {
        if ($platform === 'google_ads') {
            $groupsData = $sandboxData['groups'] ?? [['name' => 'Grupo Principal', 'targeting' => 'Público principal']];

            foreach ($groupsData as $gData) {
                $group = AdGroup::create([
                    'ad_campaign_id'   => $campaign->id,
                    'name'             => $gData['name'],
                    'targeting_summary' => $gData['targeting'] ?? null,
                    'status'           => 'draft',
                ]);

                $keywords = [
                    ['keyword' => "agência digital {$campaign->client?->name}", 'match_type' => 'phrase'],
                    ['keyword' => "marketing digital com IA",                   'match_type' => 'exact'],
                    ['keyword' => "automação de marketing",                      'match_type' => 'broad'],
                    ['keyword' => "como captar leads online",                   'match_type' => 'phrase'],
                    ['keyword' => "agência barata",                             'match_type' => 'negative'],
                ];

                foreach ($keywords as $kw) {
                    AdKeyword::create([
                        'ad_campaign_id' => $campaign->id,
                        'ad_group_id'    => $group->id,
                        'keyword'        => $kw['keyword'],
                        'match_type'     => $kw['match_type'],
                        'status'         => $kw['match_type'] === 'negative' ? 'negative' : 'active',
                    ]);
                }

                AdCreative::create([
                    'ad_campaign_id' => $campaign->id,
                    'ad_group_id'    => $group->id,
                    'title'          => "Anúncio Busca — {$group->name}",
                    'headline'       => "Transforme sua empresa com IA",
                    'description'    => "Resultados reais para seu negócio. Aprovação humana em cada etapa.",
                    'cta'            => 'Saiba Mais',
                    'destination_url' => $campaign->client?->website ?? 'https://lymity.com.br',
                    'status'         => 'draft',
                ]);

                AdCreative::create([
                    'ad_campaign_id' => $campaign->id,
                    'ad_group_id'    => $group->id,
                    'title'          => "Anúncio Responsivo — {$group->name}",
                    'headline'       => "Agência Digital com Inteligência Artificial",
                    'description'    => "Funcionários IA especializados para o seu marketing. Solicite demonstração.",
                    'cta'            => 'Solicitar Demo',
                    'destination_url' => $campaign->client?->website ?? 'https://lymity.com.br',
                    'status'         => 'draft',
                ]);
            }

            AdAudience::create([
                'ad_campaign_id' => $campaign->id,
                'name'           => 'Remarketing — Visitantes do Site',
                'description'    => 'Usuários que visitaram o site nos últimos 30 dias',
                'targeting_data' => ['type' => 'remarketing', 'days' => 30],
                'status'         => 'active',
            ]);
        }

        if ($platform === 'meta_ads') {
            $group = AdGroup::create([
                'ad_campaign_id'   => $campaign->id,
                'name'             => 'Conjunto Principal',
                'targeting_summary' => 'Público frio + Lookalike',
                'status'           => 'draft',
            ]);

            $creativesData = $sandboxData['creatives'] ?? [];
            foreach ($creativesData as $cData) {
                AdCreative::create([
                    'ad_campaign_id' => $campaign->id,
                    'ad_group_id'    => $group->id,
                    'title'          => $cData['headline'] ?? 'Criativo Meta',
                    'headline'       => $cData['headline'] ?? null,
                    'primary_text'   => $cData['primary_text'] ?? null,
                    'cta'            => $cData['cta'] ?? 'Saiba Mais',
                    'status'         => 'draft',
                ]);
            }

            $audiencesData = $sandboxData['audiences'] ?? [];
            foreach ($audiencesData as $aData) {
                AdAudience::create([
                    'ad_campaign_id' => $campaign->id,
                    'name'           => $aData['name'],
                    'description'    => $aData['description'] ?? null,
                    'targeting_data' => $aData,
                    'status'         => 'active',
                ]);
            }
        }
    }

    public function sendCampaignToApproval(AdCampaign $campaign, User $user): void
    {
        $campaign->update(['status' => 'pending_approval']);
        $this->approvalService->createCampaignApproval($campaign, $user);
        $this->log($user, "Campanha enviada para aprovação: {$campaign->name}", 'ads', $campaign->client_id);
    }

    public function approveCampaign(AdCampaign $campaign, User $user): void
    {
        $campaign->update([
            'status'      => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
        $this->log($user, "Campanha aprovada: {$campaign->name}", 'ads', $campaign->client_id);
    }

    public function scheduleCampaign(AdCampaign $campaign, array $data, User $user): void
    {
        $campaign->update([
            'status'     => 'scheduled',
            'start_date' => $data['start_date'] ?? null,
            'end_date'   => $data['end_date'] ?? null,
        ]);
        $this->log($user, "Campanha agendada: {$campaign->name}", 'ads', $campaign->client_id);
    }

    public function pauseCampaignSandbox(AdCampaign $campaign, User $user): void
    {
        $campaign->update(['status' => 'paused']);
        $this->log($user, "Campanha pausada (sandbox): {$campaign->name}", 'ads', $campaign->client_id);
    }

    public function createBudgetChange(array $data, User $user): CampaignBudgetChange
    {
        $campaign = AdCampaign::findOrFail($data['ad_campaign_id']);

        $change = CampaignBudgetChange::create([
            'ad_campaign_id' => $campaign->id,
            'requested_by'   => $user->id,
            'old_budget'     => $campaign->daily_budget,
            'new_budget'     => $data['new_budget'],
            'reason'         => $data['reason'],
            'status'         => 'pending_approval',
        ]);

        $this->approvalService->createBudgetApproval($change, $user);
        $this->log($user, "Alteração de orçamento solicitada para: {$campaign->name}", 'ads', $campaign->client_id);

        return $change;
    }

    public function applyApprovedBudgetChange(CampaignBudgetChange $change, User $user): void
    {
        if ($change->status !== 'approved') {
            throw new \RuntimeException('Apenas alterações aprovadas podem ser aplicadas.');
        }

        $campaign = $change->campaign;
        $campaign->update(['daily_budget' => $change->new_budget]);
        $change->update(['status' => 'applied']);

        $this->log($user, "Orçamento aplicado: R$ {$change->new_budget} para {$campaign->name}", 'ads', $campaign->client_id);
    }

    private function log(?User $user, string $description, string $module = 'ads', ?int $clientId = null): void
    {
        if (!class_exists(ActivityLog::class)) {
            return;
        }

        ActivityLog::create([
            'user_id'     => $user?->id,
            'client_id'   => $clientId,
            'action'      => 'ads_action',
            'module'      => $module,
            'description' => $description,
        ]);
    }
}
