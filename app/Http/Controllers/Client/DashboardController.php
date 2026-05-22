<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user   = Auth::user();
        $client = $user->client;

        $stats = [
            'contents_in_creation' => 0,
            'pending_approvals'    => 0,
            'active_campaigns'     => 0,
            'reports_available'    => 0,
        ];

        return view('client.dashboard', compact('stats', 'client', 'user'));
    }
}
