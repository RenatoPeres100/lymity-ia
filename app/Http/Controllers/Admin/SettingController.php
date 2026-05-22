<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencySetting;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = AgencySetting::orderBy('key')->get();

        return view('admin.settings.index', compact('settings'));
    }
}
