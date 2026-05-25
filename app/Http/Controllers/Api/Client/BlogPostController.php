<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Models\ClientBlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = ClientBlogPost::query();

        if (!$user->isAdminGeral()) {
            $query->where('client_id', $user->client_id);
        }

        $posts = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => BlogPostResource::collection($posts->items()),
            'meta' => ['current_page' => $posts->currentPage(), 'last_page' => $posts->lastPage(), 'total' => $posts->total()],
        ]);
    }
}
