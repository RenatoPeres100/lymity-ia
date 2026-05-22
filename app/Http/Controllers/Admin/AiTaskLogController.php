<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiEmployee;
use App\Models\AiTaskLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiTaskLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AiTaskLog::with(['task', 'employee', 'client'])->latest();

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        if ($request->filled('employee')) {
            $query->where('ai_employee_id', $request->employee);
        }

        $logs      = $query->paginate(50)->withQueryString();
        $employees = AiEmployee::orderBy('name')->get();

        return view('admin.ai-logs.index', compact('logs', 'employees'));
    }
}
