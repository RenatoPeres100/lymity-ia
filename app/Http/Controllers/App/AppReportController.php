<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppReportController extends Controller
{
    public function __construct(private MobileDashboardService $service) {}

    public function __invoke(Request $request): View
    {
        $reports = $this->service->getReportsSummary($request->user());

        return view('app.reports', compact('reports'));
    }
}
