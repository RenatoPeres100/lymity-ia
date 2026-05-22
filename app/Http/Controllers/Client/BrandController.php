<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function show(): View
    {
        $client = auth()->user()->client;
        $brand  = $client?->brandProfile;

        return view('client.brand.show', compact('client', 'brand'));
    }
}
