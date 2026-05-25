<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProposalResource;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Proposal::query();

        if (!$user->isAdminGeral()) {
            $query->where('client_id', $user->client_id);
        }

        $proposals = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => ProposalResource::collection($proposals->items()),
            'meta' => ['current_page' => $proposals->currentPage(), 'last_page' => $proposals->lastPage(), 'total' => $proposals->total()],
        ]);
    }
}
