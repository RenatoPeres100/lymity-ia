<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private MobileDashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->getClientSummary($request->user())]);
    }
}
