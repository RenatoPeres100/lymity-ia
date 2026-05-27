<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\SocialChannel;
use App\Models\SocialPost;

class ContentCommandCenterController extends Controller
{
    public function index()
    {
        $instagramChannel = SocialChannel::where('platform', 'instagram')
            ->whereNull('client_id')
            ->first();

        $publishingEnabled = config('meta.instagram_publishing_enabled', false);

        $scheduledSocialPosts = SocialPost::whereNull('client_id')
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        $scheduledBlogPosts = BlogPost::where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        $recentLogs = ActivityLog::whereIn('module', ['instagram', 'blog', 'agent'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.content-command-center', compact(
            'instagramChannel',
            'publishingEnabled',
            'scheduledSocialPosts',
            'scheduledBlogPosts',
            'recentLogs'
        ));
    }
}
