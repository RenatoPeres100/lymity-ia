<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reports\ExecutiveReportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private ExecutiveReportService $report) {}

    public function index(): View
    {
        $summary = $this->report->adminSummary();
        return view('admin.reports.index', compact('summary'));
    }

    public function social(): View
    {
        $data = $this->report->socialReport();
        return view('admin.reports.social', compact('data'));
    }

    public function campaigns(): View
    {
        $data = $this->report->campaignReport();
        return view('admin.reports.campaigns', compact('data'));
    }

    public function seo(): View
    {
        $data = $this->report->seoReport();
        return view('admin.reports.seo', compact('data'));
    }

    public function ai(): View
    {
        $data = $this->report->aiReport();
        return view('admin.reports.ai', compact('data'));
    }

    public function approvals(): View
    {
        $data = $this->report->approvalReport();
        return view('admin.reports.approvals', compact('data'));
    }

    public function executive(): View
    {
        $summary   = $this->report->adminSummary();
        $social    = $this->report->socialReport();
        $campaigns = $this->report->campaignReport();
        $seo       = $this->report->seoReport();
        $approvals = $this->report->approvalReport();
        $ai        = $this->report->aiReport();
        return view('admin.reports.executive', compact('summary', 'social', 'campaigns', 'seo', 'approvals', 'ai'));
    }
}
