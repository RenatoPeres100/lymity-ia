<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialPostResource;
use App\Models\SocialPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = SocialPost::query();

        if (!$user->isAdminGeral()) {
            $query->where('client_id', $user->client_id);
        }

        $posts = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => SocialPostResource::collection($posts->items()),
            'meta' => ['current_page' => $posts->currentPage(), 'last_page' => $posts->lastPage(), 'total' => $posts->total()],
        ]);
    }
}
