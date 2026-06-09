<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApprovalCommentRequest;
use App\Models\ApprovalComment;
use App\Models\ApprovalRequest;
use App\Services\Approval\ApprovalService;
use App\Services\Approvals\ApprovalDisplayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $service) {}

    public function index(Request $request): View
    {
        $user     = Auth::user();
        $clientId = $user->client_id;

        if (!$clientId && !$user->isAdminGeral()) {
            abort(403);
        }

        $query = ApprovalRequest::with(['requester', 'aiEmployee'])
            ->where('client_id', $clientId)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('approval_type', $request->type);
        }

        $approvals = $query->paginate(15)->withQueryString();

        $stats = [
            'pending'            => ApprovalRequest::where('client_id', $clientId)->where('status', 'pending')->count(),
            'changes_requested'  => ApprovalRequest::where('client_id', $clientId)->where('status', 'changes_requested')->count(),
            'approved'           => ApprovalRequest::where('client_id', $clientId)->where('status', 'approved')->count(),
            'total'              => ApprovalRequest::where('client_id', $clientId)->count(),
        ];

        return view('client.approvals.index', compact('approvals', 'stats'));
    }

    public function show(ApprovalRequest $approvalRequest): View
    {
        $user = Auth::user();

        if (!$user->isAdminGeral() && $approvalRequest->client_id !== $user->client_id) {
            abort(403, 'Acesso negado a aprovação de outro cliente.');
        }

        $approvalRequest->load(['requester', 'aiEmployee', 'approvedBy', 'rejectedBy', 'actions.user', 'comments.user', 'approvable']);

        $display = app(ApprovalDisplayService::class)->build($approvalRequest);

        return view('client.approvals.show', compact('approvalRequest', 'display'));
    }

    public function approve(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminGeral() && $approvalRequest->client_id !== $user->client_id) {
            abort(403);
        }

        if (!$this->service->canUserApprove($user, $approvalRequest)) {
            return back()->withErrors(['error' => 'Você não tem permissão para aprovar esta solicitação.']);
        }

        if (!$approvalRequest->isPending()) {
            return back()->withErrors(['error' => 'Esta solicitação não está pendente.']);
        }

        $this->service->approve($approvalRequest, $user, $request->notes);

        return back()->with('success', 'Aprovação realizada com sucesso.');
    }

    public function reject(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminGeral() && $approvalRequest->client_id !== $user->client_id) {
            abort(403);
        }

        if (!$this->service->canUserApprove($user, $approvalRequest)) {
            return back()->withErrors(['error' => 'Sem permissão.']);
        }

        if (!$approvalRequest->isPending()) {
            return back()->withErrors(['error' => 'Esta solicitação não está pendente.']);
        }

        $this->service->reject($approvalRequest, $user, $request->notes);

        return back()->with('success', 'Solicitação rejeitada.');
    }

    public function requestChanges(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminGeral() && $approvalRequest->client_id !== $user->client_id) {
            abort(403);
        }

        if (!$this->service->canUserApprove($user, $approvalRequest)) {
            return back()->withErrors(['error' => 'Sem permissão.']);
        }

        if (!$approvalRequest->isPending()) {
            return back()->withErrors(['error' => 'Esta solicitação não está pendente.']);
        }

        $this->service->requestChanges($approvalRequest, $user, $request->notes);

        return back()->with('success', 'Alterações solicitadas.');
    }

    public function storeComment(ApprovalRequest $approvalRequest, StoreApprovalCommentRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdminGeral() && $approvalRequest->client_id !== $user->client_id) {
            abort(403);
        }

        ApprovalComment::create([
            'approval_request_id' => $approvalRequest->id,
            'user_id'             => Auth::id(),
            'comment'             => $request->comment,
        ]);

        $this->service->logApprovalAction($approvalRequest, $user, 'commented', $request->comment);

        return back()->with('success', 'Comentário adicionado.');
    }
}
