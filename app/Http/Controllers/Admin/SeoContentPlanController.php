<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeoContentPlanRequest;
use App\Models\Client;
use App\Models\SeoContentPlan;
use App\Services\Seo\SeoContentService;

class SeoContentPlanController extends Controller
{
    public function __construct(private SeoContentService $service) {}

    public function index()
    {
        $plans = SeoContentPlan::with('client')
            ->when(request('client_id'), fn ($q) => $q->where('client_id', request('client_id')))
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(20);

        $clients = Client::orderBy('name')->get();

        return view('admin.seo.content-plans.index', compact('plans', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.seo.content-plans.create', compact('clients'));
    }

    public function store(StoreSeoContentPlanRequest $request)
    {
        $plan = $this->service->createContentPlan($request->validated());

        return redirect()->route('admin.seo.content-plans.show', $plan)
            ->with('success', 'Plano de conteúdo criado com sucesso.');
    }

    public function show(SeoContentPlan $seoContentPlan)
    {
        $seoContentPlan->load('client');
        return view('admin.seo.content-plans.show', compact('seoContentPlan'));
    }

    public function edit(SeoContentPlan $seoContentPlan)
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.seo.content-plans.edit', compact('seoContentPlan', 'clients'));
    }

    public function update(StoreSeoContentPlanRequest $request, SeoContentPlan $seoContentPlan)
    {
        $seoContentPlan->update($request->validated());

        return redirect()->route('admin.seo.content-plans.show', $seoContentPlan)
            ->with('success', 'Plano de conteúdo atualizado.');
    }
}
