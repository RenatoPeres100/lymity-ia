<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\SocialPost;
use App\Services\Approval\ApprovalService;
use Illuminate\Http\Request;

class SocialApprovalController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request)
    {
        $clientId = $request->user()->client_id;

        $approvals = ApprovalRequest::where('client_id', $clientId)
            ->where('approval_type', 'post')
            ->with('approvable')
            ->latest()
            ->paginate(15);

        return view('client.social.approvals.index', compact('approvals'));
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        if ($approvalRequest->client_id !== $request->user()->client_id) {
            abort(403);
        }

        $this->approvalService->approve($approvalRequest, $request->user(), $request->input('notes'));

        return back()->with('success', 'Aprovação confirmada.');
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        if ($approvalRequest->client_id !== $request->user()->client_id) {
            abort(403);
        }

        $this->approvalService->reject($approvalRequest, $request->user(), $request->input('notes'));

        return back()->with('success', 'Post rejeitado.');
    }
}
