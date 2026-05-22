<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientWebsitePageRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientWebsite;
use App\Models\ClientWebsitePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientWebsitePageController extends Controller
{
    public function index(Client $client): View
    {
        $website = $client->websites()->latest()->first();
        $pages   = $website ? $website->pages()->with(['creator', 'approver'])->latest()->get() : collect();

        return view('admin.clients.pages.index', compact('client', 'website', 'pages'));
    }

    public function create(Client $client): View
    {
        $website = $client->websites()->latest()->first();

        return view('admin.clients.pages.create', compact('client', 'website'));
    }

    public function store(StoreClientWebsitePageRequest $request, Client $client): RedirectResponse
    {
        $website = $client->websites()->latest()->firstOrFail();
        $data    = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        $data['client_website_id'] = $website->id;
        $data['created_by']        = Auth::id();

        $page = ClientWebsitePage::create($data);

        ActivityLog::record('created', 'client_website_pages',
            "Página '{$page->title}' criada para {$client->name}", $client->id);

        return redirect()->route('admin.clients.pages.index', $client)
            ->with('success', 'Página criada com sucesso!');
    }

    public function edit(Client $client, ClientWebsitePage $page): View
    {
        return view('admin.clients.pages.edit', compact('client', 'page'));
    }

    public function update(StoreClientWebsitePageRequest $request, Client $client, ClientWebsitePage $page): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        $page->update($data);

        ActivityLog::record('updated', 'client_website_pages',
            "Página '{$page->title}' atualizada para {$client->name}", $client->id);

        return redirect()->route('admin.clients.pages.index', $client)
            ->with('success', 'Página atualizada com sucesso!');
    }

    public function destroy(Client $client, ClientWebsitePage $page): RedirectResponse
    {
        $title = $page->title;
        $page->delete();

        ActivityLog::record('deleted', 'client_website_pages',
            "Página '{$title}' excluída de {$client->name}", $client->id);

        return redirect()->route('admin.clients.pages.index', $client)
            ->with('success', 'Página excluída com sucesso!');
    }

    public function approve(Client $client, ClientWebsitePage $page): RedirectResponse
    {
        $page->update(['status' => 'approved', 'approved_by' => Auth::id()]);

        ActivityLog::record('approved', 'client_website_pages',
            "Página '{$page->title}' aprovada para {$client->name}", $client->id,
            ['page_id' => $page->id, 'approved_by' => Auth::id()]);

        return back()->with('success', 'Página aprovada com sucesso!');
    }

    public function reject(Client $client, ClientWebsitePage $page): RedirectResponse
    {
        $page->update(['status' => 'draft', 'approved_by' => null]);

        ActivityLog::record('rejected', 'client_website_pages',
            "Página '{$page->title}' reprovada para {$client->name}", $client->id,
            ['page_id' => $page->id, 'rejected_by' => Auth::id()]);

        return back()->with('success', 'Página reprovada. Status revertido para rascunho.');
    }

    public function publish(Client $client, ClientWebsitePage $page): RedirectResponse
    {
        $page->update(['status' => 'published', 'published_at' => now()]);

        ActivityLog::record('published', 'client_website_pages',
            "Página '{$page->title}' publicada para {$client->name}", $client->id);

        return back()->with('success', 'Página publicada com sucesso!');
    }
}
