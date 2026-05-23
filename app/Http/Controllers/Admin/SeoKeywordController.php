<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeoKeywordRequest;
use App\Models\Client;
use App\Models\SeoKeyword;
use App\Services\Seo\SeoContentService;

class SeoKeywordController extends Controller
{
    public function __construct(private SeoContentService $service) {}

    public function index()
    {
        $keywords = SeoKeyword::with('client')
            ->when(request('client_id'), fn ($q) => $q->where('client_id', request('client_id')))
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->latest()
            ->paginate(20);

        $clients = Client::orderBy('name')->get();

        return view('admin.seo.keywords.index', compact('keywords', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.seo.keywords.create', compact('clients'));
    }

    public function store(StoreSeoKeywordRequest $request)
    {
        $this->service->createKeyword($request->validated());

        return redirect()->route('admin.seo.keywords.index')
            ->with('success', 'Palavra-chave criada com sucesso.');
    }

    public function edit(SeoKeyword $seoKeyword)
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.seo.keywords.edit', compact('seoKeyword', 'clients'));
    }

    public function update(StoreSeoKeywordRequest $request, SeoKeyword $seoKeyword)
    {
        $seoKeyword->update($request->validated());

        return redirect()->route('admin.seo.keywords.index')
            ->with('success', 'Palavra-chave atualizada com sucesso.');
    }

    public function destroy(SeoKeyword $seoKeyword)
    {
        $seoKeyword->delete();

        return redirect()->route('admin.seo.keywords.index')
            ->with('success', 'Palavra-chave removida.');
    }
}
