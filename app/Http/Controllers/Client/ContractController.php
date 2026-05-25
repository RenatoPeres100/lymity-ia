<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientContract;

class ContractController extends Controller
{
    public function index()
    {
        $clientId  = auth()->user()->client_id;
        $contracts = ClientContract::where('client_id', $clientId)->latest()->paginate(20);

        return view('client.contracts.index', compact('contracts'));
    }

    public function show(ClientContract $clientContract)
    {
        abort_unless($clientContract->client_id === auth()->user()->client_id, 403);
        return view('client.contracts.show', compact('clientContract'));
    }
}
