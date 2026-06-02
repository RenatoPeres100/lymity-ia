<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use Illuminate\Http\Request;

class SocialPipelineController extends Controller
{
    public function index(Request $request)
    {
        $query = SocialPost::with(['aiEmployee', 'creator', 'approver', 'aiImageGeneration'])
            ->orderBy('updated_at', 'desc');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $allPosts = $query->get();

        return view('admin.social.pipeline.index', [
            'drafts'          => $allPosts->where('status', 'draft')->values(),
            'pendingApproval' => $allPosts->where('status', 'pending_approval')->values(),
            'approved'        => $allPosts->where('status', 'approved')->values(),
            'scheduled'       => $allPosts->where('status', 'scheduled')->sortBy('scheduled_at')->values(),
            'published'       => $allPosts->where('status', 'published')->sortByDesc('published_at')->values(),
            'failed'          => $allPosts->where('status', 'failed')->values(),
        ]);
    }
}
