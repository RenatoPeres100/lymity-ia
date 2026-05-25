<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Files\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoogleDriveController extends Controller
{
    public function __construct(private GoogleDriveService $drive) {}

    public function connect(Request $request): View
    {
        $client = $request->user()->client;
        $result = $this->drive->connectPlaceholder($client);
        return view('client.files.google-drive', compact('result'));
    }
}
