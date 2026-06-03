<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApprovalCommentRequest;
use App\Http\Requests\StoreApprovalRequestRequest;
use App\Jobs\SendApprovalEmailNotificationJob;
use App\Models\ActivityLog;
use App\Models\ApprovalComment;
use App\Models\ApprovalRequest;
use App\Models\Client;
use App\Services\Approval\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $service) {}

    public function index(Request $request): View
    {
        $user  = Auth::user();
        $query = ApprovalRequest::visibleTo($user)
            ->with(['client', 'requester', 'aiEmployee'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('approval_type', $request->type);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('level')) {
            $query->where('sensitive_level', $request->level);
        }

        $approvals = $query->paginate(20)->withQueryString();
        $clients   = Client::visibleTo($user)->where('status', 'active')->orderBy('name')->get();

        $stats = [
            'pending'   => ApprovalRequest::visibleTo($user)->where('status', 'pending')->count(),
            'critical'  => ApprovalRequest::visibleTo($user)->where('status', 'pending')->where('sensitive_level', 'critical')->count(),
            'overdue'   => ApprovalRequest::visibleTo($user)->where('status', 'pending')->where('due_at', '<', now())->count(),
            'approved'  => ApprovalRequest::visibleTo($user)->where('status', 'approved')->whereMonth('approved_at', now()->month)->count(),
        ];

        return view('admin.approvals.index', compact('approvals', 'clients', 'stats'));
    }

    public function create(): View
    {
        $clients = Client::visibleTo(Auth::user())->where('status', 'active')->orderBy('name')->get();

        return view('admin.approvals.create', compact('clients'));
    }

    public function store(StoreApprovalRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->filled('payload')) {
            $decoded = json_decode($request->payload, true);
            $data['payload'] = is_array($decoded) ? $decoded : ['notes' => $request->payload];
        }

        $approval = $this->service->createApproval($data);

        return redirect()
            ->route('admin.approvals.show', $approval->id)
            ->with('success', 'Solicitação de aprovação criada com sucesso.');
    }

    public function show(ApprovalRequest $approvalRequest): View
    {
        $approvalRequest->load(['client', 'requester', 'aiEmployee', 'approvedBy', 'rejectedBy', 'actions.user', 'comments.user', 'approvable']);

        return view('admin.approvals.show', compact('approvalRequest'));
    }

    public function approve(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$this->service->canUserApprove($user, $approvalRequest)) {
            return back()->withErrors(['error' => 'Você não tem permissão para aprovar esta solicitação.']);
        }

        if (!$approvalRequest->isPending()) {
            return back()->withErrors(['error' => 'Esta solicitação não está pendente.']);
        }

        $this->service->approve($approvalRequest, $user, $request->notes);

        return back()->with('success', 'Aprovação concluída com sucesso.');
    }

    public function reject(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$this->service->canUserApprove($user, $approvalRequest)) {
            return back()->withErrors(['error' => 'Você não tem permissão para rejeitar esta solicitação.']);
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

        if (!$this->service->canUserApprove($user, $approvalRequest)) {
            return back()->withErrors(['error' => 'Você não tem permissão para solicitar alterações.']);
        }

        if (!$approvalRequest->isPending()) {
            return back()->withErrors(['error' => 'Esta solicitação não está pendente.']);
        }

        $this->service->requestChanges($approvalRequest, $user, $request->notes);

        return back()->with('success', 'Alterações solicitadas com sucesso.');
    }

    public function cancel(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin_geral', 'agencia_admin'])) {
            return back()->withErrors(['error' => 'Sem permissão para cancelar.']);
        }

        $this->service->cancel($approvalRequest, $user, $request->notes);

        return back()->with('success', 'Solicitação cancelada.');
    }

    public function storeComment(ApprovalRequest $approvalRequest, StoreApprovalCommentRequest $request): RedirectResponse
    {
        ApprovalComment::create([
            'approval_request_id' => $approvalRequest->id,
            'user_id'             => Auth::id(),
            'comment'             => $request->comment,
        ]);

        $this->service->logApprovalAction($approvalRequest, Auth::user(), 'commented', $request->comment);

        return back()->with('success', 'Comentário adicionado.');
    }

    public function resendEmail(ApprovalRequest $approvalRequest): RedirectResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin_geral', 'agencia_admin'])) {
            return back()->withErrors(['error' => 'Sem permissão para reenviar e-mail.']);
        }

        SendApprovalEmailNotificationJob::dispatch($approvalRequest->id, 'created', true)
            ->onQueue('default');

        ActivityLog::create([
            'user_id'     => $user->id,
            'client_id'   => $approvalRequest->client_id,
            'action'      => 'approval_email_resend_requested',
            'module'      => 'approvals',
            'level'       => 'info',
            'description' => "Reenvio de e-mail solicitado — #{$approvalRequest->id}",
            'metadata'    => ['approval_request_id' => $approvalRequest->id, 'requested_by' => $user->id],
        ]);

        return back()->with('success', 'E-mail de aprovação reenviado com sucesso.');
    }
}
