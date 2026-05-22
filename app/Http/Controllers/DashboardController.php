<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_clients'       => Client::where('status', 'active')->count(),
            'total_users'         => User::where('status', 'active')->where('user_type', '!=', 'ai')->count(),
            'ai_employees'        => User::where('user_type', 'ai')->count(),
            'pending_approvals'   => 0,
            'planned_posts'       => 0,
            'campaigns_analysis'  => 0,
        ];

        $recent_clients = Client::with('company')
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recent_clients'));
    }
}
