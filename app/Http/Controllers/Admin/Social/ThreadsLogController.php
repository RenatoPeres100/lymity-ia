<?php

namespace App\Http\Controllers\Admin\Social;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ThreadsLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::where('module', 'threads')
            ->latest()
            ->paginate(50);

        return view('admin.social.threads.logs', compact('logs'));
    }
}
