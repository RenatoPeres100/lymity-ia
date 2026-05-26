<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\SocialPost;

class PublishingQueueController extends Controller
{
    public function index()
    {
        $socialPending = SocialPost::whereIn('status', ['pending_approval', 'scheduled'])
            ->with('client')
            ->orderBy('scheduled_at')
            ->paginate(20, ['*'], 'social_page');

        $blogPending = BlogPost::whereIn('status', ['draft', 'pending_review', 'scheduled'])
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'blog_page');

        $totalPending = SocialPost::where('status', 'pending_approval')->count()
            + BlogPost::whereIn('status', ['draft', 'pending_review'])->count();

        $totalScheduled = SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->count()
            + BlogPost::where('status', 'scheduled')->count();

        return view('admin.publishing-queue.index', compact(
            'socialPending',
            'blogPending',
            'totalPending',
            'totalScheduled'
        ));
    }
}
