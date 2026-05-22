<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('site.contato');
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        Lead::create(array_merge($request->validated(), [
            'source' => 'website',
            'status' => 'new',
        ]));

        return redirect()->route('contato')
            ->with('success', 'Mensagem enviada com sucesso! Entraremos em contato em breve.');
    }
}
