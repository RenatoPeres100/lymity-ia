<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApprovalEmailActionController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    /**
     * Show confirmation page (GET — never executes action).
     */
    public function confirm(Request $request, int $approval, string $action): View|RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return $this->expiredPage('Link inválido ou expirado.');
        }

        $allowed = ['approve', 'reject', 'request_changes'];
        if (!in_array($action, $allowed)) {
            abort(400, 'Ação inválida.');
        }

        $approvalRequest = ApprovalRequest::with(['client', 'aiEmployee', 'approvable'])->find($approval);

        if (!$approvalRequest) {
            abort(404, 'Solicitação não encontrada.');
        }

        if (!$approvalRequest->isPending()) {
            return view('approval-email.already-resolved', [
                'approval' => $approvalRequest,
                'action'   => $action,
            ]);
        }

        $uid  = (int) $request->query('uid');
        $user = $this->resolveUser($uid);

        // Log that the link was opened
        $this->logActivity('approval_email_action_opened', $approvalRequest, $user, [
            'action'    => $action,
            'source'    => 'email_link',
            'via_login' => Auth::check(),
        ]);

        return view('approval-email.confirm', [
            'approval'  => $approvalRequest,
            'action'    => $action,
            'user'      => $user,
            'uid'       => $uid,
            'signature' => $request->query('signature'),
        ]);
    }

    /**
     * Execute action after confirmation (POST).
     */
    public function submit(Request $request, int $approval, string $action): View|RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return $this->expiredPage('Link inválido ou expirado. A ação não foi executada.');
        }

        $allowed = ['approve', 'reject', 'request_changes'];
        if (!in_array($action, $allowed)) {
            abort(400, 'Ação inválida.');
        }

        $approvalRequest = ApprovalRequest::with(['client', 'aiEmployee', 'approvable'])->find($approval);

        if (!$approvalRequest) {
            abort(404, 'Solicitação não encontrada.');
        }

        if (!$approvalRequest->isPending()) {
            return view('approval-email.already-resolved', [
                'approval' => $approvalRequest,
                'action'   => $action,
            ]);
        }

        // Validate notes requirement for request_changes
        if ($action === 'request_changes') {
            $request->validate(['notes' => ['required', 'string', 'min:3', 'max:1000']]);
        } elseif ($action === 'reject') {
            $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);
        }

        $uid  = (int) $request->query('uid');
        $user = $this->resolveUser($uid);

        if (!$user) {
            return $this->expiredPage('Usuário não identificado. Faça login no painel para executar esta ação.');
        }

        $notes = $request->input('notes');

        try {
            match ($action) {
                'approve'         => $this->approvalService->approve($approvalRequest, $user, $notes ?? 'Aprovado via link de e-mail.'),
                'reject'          => $this->approvalService->reject($approvalRequest, $user, $notes),
                'request_changes' => $this->approvalService->requestChanges($approvalRequest, $user, $notes),
            };

            $this->logActivity('approval_email_action_confirmed', $approvalRequest, $user, [
                'action' => $action,
                'source' => 'email_link',
            ]);

            return view('approval-email.success', [
                'approval'  => $approvalRequest->fresh(),
                'action'    => $action,
                'user'      => $user,
                'panelUrl'  => $this->panelUrl($approvalRequest, $user),
            ]);
        } catch (\Throwable $e) {
            return view('approval-email.error', [
                'approval' => $approvalRequest,
                'action'   => $action,
                'message'  => 'Não foi possível executar a ação. Tente novamente pelo painel.',
                'panelUrl' => $this->panelUrl($approvalRequest, $user),
            ]);
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function resolveUser(int $uid): ?User
    {
        // If user is authenticated, use the logged-in user
        if (Auth::check()) {
            return Auth::user();
        }

        // Otherwise resolve by uid from signed URL
        if ($uid > 0) {
            return User::find($uid);
        }

        return null;
    }

    private function panelUrl(ApprovalRequest $approval, ?User $user): string
    {
        $isAdmin = $user && in_array($user->role, ['admin_geral', 'agencia_admin', 'agencia_operador']);
        return $approval->getPanelUrl($isAdmin);
    }

    private function expiredPage(string $message): View
    {
        return view('approval-email.expired', compact('message'));
    }

    private function logActivity(string $action, ApprovalRequest $approval, ?User $user, array $extra = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'client_id'   => $approval->client_id,
                'action'      => $action,
                'module'      => 'approvals',
                'level'       => 'info',
                'description' => "Ação de e-mail: {$action} — #{$approval->id}",
                'metadata'    => array_merge(['approval_request_id' => $approval->id], $extra),
            ]);
        } catch (\Throwable) {}
    }
}
