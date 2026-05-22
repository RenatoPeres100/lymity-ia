<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::with('company')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }
}
