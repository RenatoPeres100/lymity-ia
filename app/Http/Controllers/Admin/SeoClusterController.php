<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeoClusterRequest;
use App\Models\Client;
use App\Models\SeoCluster;
use App\Services\Seo\SeoContentService;
use App\Traits\HasBulkDestroy;

class SeoClusterController extends Controller
{
    use HasBulkDestroy;

    protected string $bulkDestroyModel  = SeoCluster::class;

    public function __construct(private SeoContentService $service) {}

    public function index()
    {
        $clusters = SeoCluster::with('client')
            ->when(request('client_id'), fn ($q) => $q->where('client_id', request('client_id')))
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->latest()
            ->paginate(20);

        $clients = Client::orderBy('name')->get();

        return view('admin.seo.clusters.index', compact('clusters', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.seo.clusters.create', compact('clients'));
    }

    public function store(StoreSeoClusterRequest $request)
    {
        $this->service->createCluster($request->validated());

        return redirect()->route('admin.seo.clusters.index')
            ->with('success', 'Cluster SEO criado com sucesso.');
    }

    public function edit(SeoCluster $seoCluster)
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.seo.clusters.edit', compact('seoCluster', 'clients'));
    }

    public function update(StoreSeoClusterRequest $request, SeoCluster $seoCluster)
    {
        $seoCluster->update($request->validated());

        return redirect()->route('admin.seo.clusters.index')
            ->with('success', 'Cluster SEO atualizado com sucesso.');
    }

    public function destroy(SeoCluster $seoCluster)
    {
        $seoCluster->delete();

        return redirect()->route('admin.seo.clusters.index')
            ->with('success', 'Cluster removido.');
    }
}
