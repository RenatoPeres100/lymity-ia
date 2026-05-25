<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Models\AdCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = AdCampaign::query();

        if (!$user->isAdminGeral()) {
            $query->where('client_id', $user->client_id);
        }

        $campaigns = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => CampaignResource::collection($campaigns->items()),
            'meta' => ['current_page' => $campaigns->currentPage(), 'last_page' => $campaigns->lastPage(), 'total' => $campaigns->total()],
        ]);
    }
}
