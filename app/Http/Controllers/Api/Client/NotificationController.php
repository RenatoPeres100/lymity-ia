<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = AppNotification::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if ($user->client_id) {
                $q->orWhere(fn ($s) => $s->where('client_id', $user->client_id)->whereNull('user_id'));
            }
        })
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json([
            'data' => NotificationResource::collection($notifications->items()),
            'meta' => ['current_page' => $notifications->currentPage(), 'last_page' => $notifications->lastPage(), 'total' => $notifications->total()],
        ]);
    }
}
