<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientWebsiteRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientWebsite;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientWebsiteController extends Controller
{
    public function show(Client $client): View
    {
        $website = $client->websites()->latest()->first();

        return view('admin.clients.website.show', compact('client', 'website'));
    }

    public function store(StoreClientWebsiteRequest $request, Client $client): RedirectResponse
    {
        $website = ClientWebsite::create(array_merge($request->validated(), ['client_id' => $client->id]));

        ActivityLog::record('created', 'client_website', "Website criado para {$client->name}: {$website->domain}", $client->id);

        return redirect()->route('admin.clients.website.show', $client)
            ->with('success', 'Website criado com sucesso!');
    }

    public function update(StoreClientWebsiteRequest $request, Client $client, ClientWebsite $website): RedirectResponse
    {
        $website->update($request->validated());

        ActivityLog::record('updated', 'client_website', "Website atualizado para {$client->name}", $client->id);

        return redirect()->route('admin.clients.website.show', $client)
            ->with('success', 'Website atualizado com sucesso!');
    }
}
