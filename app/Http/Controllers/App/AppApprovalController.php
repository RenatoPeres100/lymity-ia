<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Services\Approval\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppApprovalController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = ApprovalRequest::with(['client', 'requester']);

        if (!$user->isAdminGeral()) {
            $query->where('client_id', $user->client_id);
        }

        $approvals = $query->orderByDesc('created_at')->paginate(20);

        return view('app.approvals.index', compact('approvals'));
    }

    public function show(Request $request, ApprovalRequest $approvalRequest): View
    {
        $this->authorizeAccess($request, $approvalRequest);

        $approvalRequest->load(['client', 'requester', 'actions.user', 'comments.user']);

        return view('app.approvals.show', compact('approvalRequest'));
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $this->authorizeAccess($request, $approvalRequest);

        $request->validate(['notes' => 'nullable|string|max:1000']);

        if ($approvalRequest->status !== 'pending') {
            return back()->with('error', 'Esta aprovação não está pendente.');
        }

        $this->approvalService->approve($approvalRequest, $request->user(), $request->input('notes'));

        return redirect()->route('app.approvals.index')->with('success', 'Aprovação realizada com sucesso.');
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $this->authorizeAccess($request, $approvalRequest);

        $request->validate(['reason' => 'required|string|max:1000']);

        if ($approvalRequest->status !== 'pending') {
            return back()->with('error', 'Esta aprovação não está pendente.');
        }

        $this->approvalService->reject($approvalRequest, $request->user(), $request->input('reason'));

        return redirect()->route('app.approvals.index')->with('success', 'Reprovação registrada.');
    }

    public function requestChanges(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $this->authorizeAccess($request, $approvalRequest);

        $request->validate(['notes' => 'required|string|max:1000']);

        if ($approvalRequest->status !== 'pending') {
            return back()->with('error', 'Esta aprovação não está pendente.');
        }

        $this->approvalService->requestChanges($approvalRequest, $request->user(), $request->input('notes'));

        return back()->with('success', 'Solicitação de ajuste registrada.');
    }

    public function comment(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $this->authorizeAccess($request, $approvalRequest);

        $request->validate(['comment' => 'required|string|max:2000']);

        $approvalRequest->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $request->input('comment'),
        ]);

        return back()->with('success', 'Comentário adicionado.');
    }

    private function authorizeAccess(Request $request, ApprovalRequest $approvalRequest): void
    {
        $user = $request->user();
        if (!$user->isAdminGeral() && $approvalRequest->client_id !== $user->client_id) {
            abort(403, 'Acesso não autorizado.');
        }
    }
}
