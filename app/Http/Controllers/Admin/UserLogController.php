<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\View\View;

class UserLogController extends Controller
{
    public function show(User $user): View
    {
        $this->authorize('viewLogs', $user);

        $logs = ActivityLog::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.users.logs', compact('user', 'logs'));
    }
}
