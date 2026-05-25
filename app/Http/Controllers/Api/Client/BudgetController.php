<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Budget::query();

        if (!$user->isAdminGeral()) {
            $query->where('client_id', $user->client_id);
        }

        $budgets = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => BudgetResource::collection($budgets->items()),
            'meta' => ['current_page' => $budgets->currentPage(), 'last_page' => $budgets->lastPage(), 'total' => $budgets->total()],
        ]);
    }
}
