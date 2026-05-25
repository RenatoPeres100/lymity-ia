<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdAccount;
use App\Models\AdCampaign;
use App\Models\AiEmployee;
use App\Models\Client;
use App\Models\Company;
use App\Services\Ads\AdsPlanningService;
use App\Services\Ads\CampaignMetricService;
use Illuminate\Http\Request;

class AdCampaignController extends Controller
{
    public function __construct(
        protected AdsPlanningService   $planning,
        protected CampaignMetricService $metrics,
    ) {}

    public function index(Request $request)
    {
        $query = AdCampaign::with(['client', 'account']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        if ($request->filled('objective')) {
            $query->where('objective', $request->objective);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->latest()->paginate(20)->withQueryString();
        $clients   = Client::where('status', 'active')->orderBy('name')->get();

        return view('admin.ads.campaigns.index', compact('campaigns', 'clients'));
    }

    public function create()
    {
        $accounts    = AdAccount::where('status', 'active')->with('client')->get();
        $clients     = Client::where('status', 'active')->orderBy('name')->get();
        $companies   = Company::orderBy('name')->get();
        $aiEmployees = AiEmployee::where('status', 'active')->get();
        return view('admin.ads.campaigns.create', compact('accounts', 'clients', 'companies', 'aiEmployees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ad_account_id'    => 'required|exists:ad_accounts,id',
            'client_id'        => 'nullable|exists:clients,id',
            'company_id'       => 'nullable|exists:companies,id',
            'name'             => 'required|string|max:255',
            'objective'        => 'required|in:leads,sales,traffic,awareness,engagement,app,remarketing',
            'daily_budget'     => 'nullable|numeric|min:0',
            'total_budget'     => 'nullable|numeric|min:0',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'strategy_summary' => 'nullable|string',
            'requires_approval' => 'boolean',
        ]);

        $account = AdAccount::findOrFail($data['ad_account_id']);
        $data['platform'] = $account->platform;

        $campaign = $this->planning->createCampaignPlan($data, $request->user());

        return redirect()->route('admin.ads.campaigns.show', $campaign)->with('success', 'Campanha criada com sucesso.');
    }

    public function show(AdCampaign $adCampaign)
    {
        $adCampaign->load(['account', 'client', 'creator', 'aiEmployee', 'approver', 'groups.creatives', 'groups.keywords', 'creatives', 'keywords', 'audiences', 'metrics', 'budgetChanges.requester', 'approvalRequests']);
        $summary = $this->metrics->summarizeCampaign($adCampaign);
        return view('admin.ads.campaigns.show', compact('adCampaign', 'summary'));
    }

    public function edit(AdCampaign $adCampaign)
    {
        $accounts    = AdAccount::where('status', 'active')->with('client')->get();
        $clients     = Client::where('status', 'active')->orderBy('name')->get();
        $companies   = Company::orderBy('name')->get();
        $aiEmployees = AiEmployee::where('status', 'active')->get();
        return view('admin.ads.campaigns.edit', compact('adCampaign', 'accounts', 'clients', 'companies', 'aiEmployees'));
    }

    public function update(Request $request, AdCampaign $adCampaign)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'objective'        => 'required|in:leads,sales,traffic,awareness,engagement,app,remarketing',
            'daily_budget'     => 'nullable|numeric|min:0',
            'total_budget'     => 'nullable|numeric|min:0',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'strategy_summary' => 'nullable|string',
        ]);

        $adCampaign->update($data);

        return redirect()->route('admin.ads.campaigns.show', $adCampaign)->with('success', 'Campanha atualizada com sucesso.');
    }

    public function sendApproval(AdCampaign $adCampaign, Request $request)
    {
        $this->planning->sendCampaignToApproval($adCampaign, $request->user());
        return back()->with('success', 'Campanha enviada para aprovação.');
    }

    public function schedule(AdCampaign $adCampaign, Request $request)
    {
        $data = $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);
        $this->planning->scheduleCampaign($adCampaign, $data, $request->user());
        return back()->with('success', 'Campanha agendada com sucesso.');
    }

    public function pauseSandbox(AdCampaign $adCampaign, Request $request)
    {
        $this->planning->pauseCampaignSandbox($adCampaign, $request->user());
        return back()->with('success', 'Campanha pausada (modo sandbox).');
    }

    public function generateMockMetrics(AdCampaign $adCampaign)
    {
        $this->metrics->createMockMetrics($adCampaign, 7);
        return back()->with('success', 'Métricas simuladas geradas com sucesso.');
    }
}
