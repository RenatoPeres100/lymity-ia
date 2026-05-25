<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovalResource;
use App\Models\ApprovalRequest;
use App\Services\Approval\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = ApprovalRequest::with(['client', 'requestedBy']);

        if (!$user->isAdminGeral()) {
            $query->where('client_id', $user->client_id);
        }

        $approvals = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => ApprovalResource::collection($approvals->items()),
            'meta' => ['current_page' => $approvals->currentPage(), 'last_page' => $approvals->lastPage(), 'total' => $approvals->total()],
        ]);
    }

    public function show(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $this->authorizeAccess($request, $approvalRequest);

        return response()->json(['data' => new ApprovalResource($approvalRequest->load(['client', 'requestedBy', 'actions', 'comments']))]);
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $this->authorizeAccess($request, $approvalRequest);

        $request->validate(['notes' => 'nullable|string|max:1000']);

        if ($approvalRequest->status !== 'pending') {
            return response()->json(['message' => 'Esta aprovação não está pendente.'], 422);
        }

        $this->approvalService->approve($approvalRequest, $request->user(), $request->input('notes'));

        return response()->json(['message' => 'Aprovação realizada com sucesso.', 'data' => new ApprovalResource($approvalRequest->fresh())]);
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $this->authorizeAccess($request, $approvalRequest);

        $request->validate(['reason' => 'required|string|max:1000']);

        if ($approvalRequest->status !== 'pending') {
            return response()->json(['message' => 'Esta aprovação não está pendente.'], 422);
        }

        $this->approvalService->reject($approvalRequest, $request->user(), $request->input('reason'));

        return response()->json(['message' => 'Reprovação registrada com sucesso.', 'data' => new ApprovalResource($approvalRequest->fresh())]);
    }

    public function requestChanges(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $this->authorizeAccess($request, $approvalRequest);

        $request->validate(['notes' => 'required|string|max:1000']);

        if ($approvalRequest->status !== 'pending') {
            return response()->json(['message' => 'Esta aprovação não está pendente.'], 422);
        }

        $this->approvalService->requestChanges($approvalRequest, $request->user(), $request->input('notes'));

        return response()->json(['message' => 'Solicitação de ajuste registrada.', 'data' => new ApprovalResource($approvalRequest->fresh())]);
    }

    private function authorizeAccess(Request $request, ApprovalRequest $approvalRequest): void
    {
        $user = $request->user();
        if (!$user->isAdminGeral() && $approvalRequest->client_id !== $user->client_id) {
            abort(403, 'Acesso não autorizado.');
        }
    }
}
