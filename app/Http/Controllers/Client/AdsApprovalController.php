<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\ApprovalRequest;
use App\Models\CampaignBudgetChange;
use Illuminate\Http\Request;

class AdsApprovalController extends Controller
{
    public function index(Request $request)
    {
        $clientId = $request->user()->client_id;

        $approvals = ApprovalRequest::where('client_id', $clientId)
            ->whereIn('approval_type', ['campaign', 'budget'])
            ->with('approvable')
            ->latest()
            ->paginate(15);

        return view('client.ads.approvals.index', compact('approvals'));
    }
}
