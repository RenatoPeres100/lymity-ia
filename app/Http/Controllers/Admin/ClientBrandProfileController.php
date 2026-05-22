<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientBrandProfileRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientBrandProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientBrandProfileController extends Controller
{
    public function show(Client $client): View
    {
        $brand = $client->brandProfile;

        return view('admin.clients.brand.show', compact('client', 'brand'));
    }

    public function store(StoreClientBrandProfileRequest $request, Client $client): RedirectResponse
    {
        $brand = ClientBrandProfile::updateOrCreate(
            ['client_id' => $client->id],
            array_merge($request->validated(), ['client_id' => $client->id])
        );

        ActivityLog::record('created', 'brand_profile', "Perfil de marca criado para {$client->name}", $client->id);

        return redirect()->route('admin.clients.brand.show', $client)
            ->with('success', 'Perfil de marca salvo com sucesso!');
    }

    public function update(StoreClientBrandProfileRequest $request, Client $client): RedirectResponse
    {
        $brand = $client->brandProfile ?? new ClientBrandProfile(['client_id' => $client->id]);
        $brand->fill($request->validated());
        $brand->save();

        ActivityLog::record('updated', 'brand_profile', "Perfil de marca atualizado para {$client->name}", $client->id);

        return redirect()->route('admin.clients.brand.show', $client)
            ->with('success', 'Perfil de marca atualizado com sucesso!');
    }
}
