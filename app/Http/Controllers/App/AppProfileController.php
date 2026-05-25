<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('client');

        return view('app.profile', compact('user'));
    }
}
