<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeoAuditRequest;
use App\Models\Client;
use App\Models\SeoAudit;
use App\Services\Seo\SeoAuditService;

class SeoAuditController extends Controller
{
    public function __construct(private SeoAuditService $service) {}

    public function index()
    {
        $audits = SeoAudit::with('client')
            ->when(request('client_id'), fn ($q) => $q->where('client_id', request('client_id')))
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->latest()
            ->paginate(20);

        $clients = Client::orderBy('name')->get();

        return view('admin.seo.audits.index', compact('audits', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.seo.audits.create', compact('clients'));
    }

    public function store(StoreSeoAuditRequest $request)
    {
        $audit = $this->service->createAudit($request->validated());

        return redirect()->route('admin.seo.audits.show', $audit)
            ->with('success', 'Auditoria SEO criada com sucesso.');
    }

    public function show(SeoAudit $seoAudit)
    {
        $seoAudit->load('client', 'recommendations');
        return view('admin.seo.audits.show', compact('seoAudit'));
    }

    public function generateMock(SeoAudit $seoAudit)
    {
        $this->service->generateMockRecommendations($seoAudit);

        return redirect()->route('admin.seo.audits.show', $seoAudit)
            ->with('success', 'Recomendações mock geradas com sucesso.');
    }
}
