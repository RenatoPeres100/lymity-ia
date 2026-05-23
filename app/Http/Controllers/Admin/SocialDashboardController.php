<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialCalendar;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use Carbon\Carbon;

class SocialDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_posts'     => SocialPost::count(),
            'pending'         => SocialPost::where('status', 'pending_approval')->count(),
            'scheduled'       => SocialPost::where('status', 'scheduled')->count(),
            'published'       => SocialPost::where('status', 'published')->count(),
            'channels'        => SocialChannel::where('status', 'active')->count(),
        ];

        $recentPosts = SocialPost::with(['client', 'aiEmployee'])
            ->latest()
            ->take(10)
            ->get();

        $upcomingPosts = SocialPost::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        $now = Carbon::now();
        $calendars = SocialCalendar::where('month', $now->month)
            ->where('year', $now->year)
            ->with('client')
            ->get();

        return view('admin.social.index', compact('stats', 'recentPosts', 'upcomingPosts', 'calendars'));
    }
}
