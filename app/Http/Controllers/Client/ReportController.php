<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Reports\ExecutiveReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private ExecutiveReportService $report) {}

    private function getClient(Request $request)
    {
        $user = $request->user();
        if ($user->isAdminGeral()) {
            return null;
        }
        return $user->client;
    }

    public function index(Request $request): View
    {
        $client  = $this->getClient($request);
        $summary = $client ? $this->report->clientSummary($client) : $this->report->adminSummary();
        return view('client.reports.index', compact('summary', 'client'));
    }

    public function social(Request $request): View
    {
        $client = $this->getClient($request);
        $data   = $this->report->socialReport($client);
        return view('client.reports.social', compact('data', 'client'));
    }

    public function campaigns(Request $request): View
    {
        $client = $this->getClient($request);
        $data   = $this->report->campaignReport($client);
        return view('client.reports.campaigns', compact('data', 'client'));
    }

    public function seo(Request $request): View
    {
        $client = $this->getClient($request);
        $data   = $this->report->seoReport($client);
        return view('client.reports.seo', compact('data', 'client'));
    }

    public function approvals(Request $request): View
    {
        $client = $this->getClient($request);
        $data   = $this->report->approvalReport($client);
        return view('client.reports.approvals', compact('data', 'client'));
    }

    public function executive(Request $request): View
    {
        $client    = $this->getClient($request);
        $summary   = $client ? $this->report->clientSummary($client) : $this->report->adminSummary();
        $social    = $this->report->socialReport($client);
        $campaigns = $this->report->campaignReport($client);
        $approvals = $this->report->approvalReport($client);
        return view('client.reports.executive', compact('summary', 'social', 'campaigns', 'approvals', 'client'));
    }
}
