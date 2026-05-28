<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ClientBrandContextController extends Controller
{
    public function index(): View
    {
        $user   = Auth::user();
        abort_unless($user->hasPermission('client.brand_context.view'), 403, 'Sem permissão para acessar o Brand Context.');

        $client = $user->client;
        $brand  = $client?->brandProfile;

        return view('client.brand-context.index', compact('client', 'brand'));
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->hasPermission('client.brand_context.update'), 403, 'Sem permissão para editar o Brand Context.');

        $data = $request->validate([
            'brand_voice'    => ['nullable', 'string', 'max:2000'],
            'target_audience'=> ['nullable', 'string', 'max:2000'],
            'tone'           => ['nullable', 'string', 'max:500'],
            'differentials'  => ['nullable', 'string', 'max:2000'],
        ]);

        $client = $user->client;
        if ($client) {
            $brand = $client->brandProfile()->firstOrNew(['client_id' => $client->id]);
            $brand->fill($data)->save();
        }

        return back()->with('success', 'Brand Context atualizado com sucesso.');
    }
}
