<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\System\SystemHealthService;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function __invoke(SystemHealthService $health): View
    {
        $checks = $health->check();

        return view('admin.system-health.index', compact('checks'));
    }
}
