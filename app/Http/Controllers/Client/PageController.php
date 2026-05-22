<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ClientWebsitePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $client  = auth()->user()->client;
        $website = $client?->websites()->latest()->first();
        $pages   = $website ? $website->pages()->with(['creator', 'approver'])->latest()->get() : collect();

        return view('client.pages.index', compact('client', 'pages'));
    }

    public function show(ClientWebsitePage $page): View
    {
        $client = auth()->user()->client;
        abort_unless($page->website->client_id === $client?->id, 403);

        return view('client.pages.show', compact('client', 'page'));
    }

    public function approve(ClientWebsitePage $page): RedirectResponse
    {
        $client = auth()->user()->client;
        abort_unless($page->website->client_id === $client?->id, 403);
        abort_unless($page->status === 'pending_approval', 403, 'Página não está aguardando aprovação.');

        $page->update(['status' => 'approved', 'approved_by' => auth()->id()]);

        ActivityLog::record('approved', 'client_website_pages',
            "Página '{$page->title}' aprovada pelo cliente", $client->id,
            ['page_id' => $page->id, 'approved_by' => auth()->id()]);

        return back()->with('success', 'Página aprovada com sucesso!');
    }

    public function reject(ClientWebsitePage $page): RedirectResponse
    {
        $client = auth()->user()->client;
        abort_unless($page->website->client_id === $client?->id, 403);
        abort_unless($page->status === 'pending_approval', 403, 'Página não está aguardando aprovação.');

        $page->update(['status' => 'draft', 'approved_by' => null]);

        ActivityLog::record('rejected', 'client_website_pages',
            "Página '{$page->title}' reprovada pelo cliente", $client->id,
            ['page_id' => $page->id, 'rejected_by' => auth()->id()]);

        return back()->with('success', 'Página reprovada.');
    }
}
