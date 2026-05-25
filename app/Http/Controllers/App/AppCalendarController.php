<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppCalendarController extends Controller
{
    public function __construct(private MobileDashboardService $service) {}

    public function __invoke(Request $request): View
    {
        $items = $this->service->getCalendarItems($request->user());

        return view('app.calendar', compact('items'));
    }
}
