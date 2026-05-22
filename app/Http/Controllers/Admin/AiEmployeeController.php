<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiEmployee;
use Illuminate\View\View;

class AiEmployeeController extends Controller
{
    public function index(): View
    {
        $employees = AiEmployee::with('skills')->orderBy('name')->get();

        return view('admin.ai-employees.index', compact('employees'));
    }

    public function show(AiEmployee $aiEmployee): View
    {
        $aiEmployee->load(['skills', 'creator', 'tasks' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('admin.ai-employees.show', compact('aiEmployee'));
    }
}
