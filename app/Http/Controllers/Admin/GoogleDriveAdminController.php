<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Files\GoogleDriveService;
use Illuminate\View\View;

class GoogleDriveAdminController extends Controller
{
    public function __construct(private GoogleDriveService $drive) {}

    public function connect(?Client $client = null): View
    {
        $result = $this->drive->connectPlaceholder($client);
        return view('admin.files.google-drive', compact('result', 'client'));
    }

    public function connectForClient(Client $client): View
    {
        $result = $this->drive->connectPlaceholder($client);
        return view('admin.files.google-drive', compact('result', 'client'));
    }
}
