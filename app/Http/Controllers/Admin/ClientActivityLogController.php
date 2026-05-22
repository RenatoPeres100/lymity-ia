<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\View\View;

class ClientActivityLogController extends Controller
{
    public function index(Client $client): View
    {
        $logs = $client->activityLogs()
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('admin.clients.logs.index', compact('client', 'logs'));
    }
}
