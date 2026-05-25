<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\CampaignBudgetChange;
use App\Services\Ads\AdsPlanningService;
use Illuminate\Http\Request;

class CampaignBudgetChangeController extends Controller
{
    public function __construct(protected AdsPlanningService $planning) {}

    public function index()
    {
        $changes = CampaignBudgetChange::with(['campaign.client', 'requester', 'approver'])
            ->latest()->paginate(20);

        return view('admin.ads.budget-approvals.index', compact('changes'));
    }

    public function create()
    {
        $campaigns = AdCampaign::with('client')->whereIn('status', ['approved', 'active', 'scheduled'])->get();
        return view('admin.ads.budget-approvals.create', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ad_campaign_id' => 'required|exists:ad_campaigns,id',
            'new_budget'     => 'required|numeric|min:1',
            'reason'         => 'required|string',
        ]);

        $change = $this->planning->createBudgetChange($data, $request->user());

        return redirect()->route('admin.ads.budget-approvals.index')
            ->with('success', "Alteração de orçamento criada e enviada para aprovação. ID: {$change->id}");
    }

    public function apply(CampaignBudgetChange $campaignBudgetChange, Request $request)
    {
        $this->planning->applyApprovedBudgetChange($campaignBudgetChange, $request->user());
        return back()->with('success', 'Orçamento aplicado com sucesso.');
    }
}
