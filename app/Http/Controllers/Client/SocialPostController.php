<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use Illuminate\Http\Request;

class SocialPostController extends Controller
{
    public function index(Request $request)
    {
        $user     = $request->user();
        $clientId = $user->client_id;

        $query = SocialPost::where('client_id', $clientId)->with('aiEmployee')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->paginate(15)->withQueryString();

        return view('client.social.posts.index', compact('posts'));
    }

    public function show(Request $request, SocialPost $post)
    {
        $user = $request->user();

        if ($post->client_id !== $user->client_id) {
            abort(403);
        }

        $post->load(['aiEmployee', 'variants', 'approvalRequests']);

        return view('client.social.posts.show', compact('post'));
    }
}
