<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientAssetRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientAssetController extends Controller
{
    public function index(Client $client): View
    {
        $assets = $client->assets()->latest()->get();

        return view('admin.clients.assets.index', compact('client', 'assets'));
    }

    public function create(Client $client): View
    {
        return view('admin.clients.assets.create', compact('client'));
    }

    public function store(StoreClientAssetRequest $request, Client $client): RedirectResponse
    {
        $asset = ClientAsset::create(array_merge($request->validated(), ['client_id' => $client->id]));

        ActivityLog::record('created', 'client_assets',
            "Asset '{$asset->name}' adicionado para {$client->name}", $client->id);

        return redirect()->route('admin.clients.assets.index', $client)
            ->with('success', 'Asset adicionado com sucesso!');
    }

    public function destroy(Client $client, ClientAsset $asset): RedirectResponse
    {
        $name = $asset->name;
        $asset->delete();

        ActivityLog::record('deleted', 'client_assets',
            "Asset '{$name}' removido de {$client->name}", $client->id);

        return redirect()->route('admin.clients.assets.index', $client)
            ->with('success', 'Asset removido com sucesso!');
    }
}
