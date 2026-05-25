<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ExternalFile;
use App\Services\Files\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientFileController extends Controller
{
    public function __construct(private FileStorageService $storage) {}

    public function index(Client $client, Request $request): View
    {
        $files   = $this->storage->listForClient($client, $request->only(['file_type', 'search']));
        $folders = $client->folders()->orderBy('name')->get();

        return view('admin.client-files.index', compact('client', 'files', 'folders'));
    }

    public function create(Client $client): View
    {
        return view('admin.client-files.create', compact('client'));
    }

    public function store(Client $client, Request $request): RedirectResponse
    {
        $request->validate([
            'file'  => 'required|file|max:10240',
            'notes' => 'nullable|string',
        ]);

        $this->storage->uploadLocal(
            $request->file('file'),
            ['client_id' => $client->id, 'notes' => $request->notes],
            $request->user()
        );

        return redirect()->route('admin.clients.files.index', $client)
            ->with('success', 'Arquivo enviado com sucesso.');
    }

    public function show(Client $client, ExternalFile $externalFile): View
    {
        abort_if($externalFile->client_id !== $client->id, 403);
        $url = $this->storage->getPublicUrl($externalFile);
        return view('admin.client-files.show', ['client' => $client, 'file' => $externalFile, 'url' => $url]);
    }

    public function destroy(Client $client, ExternalFile $externalFile, Request $request): RedirectResponse
    {
        abort_if($externalFile->client_id !== $client->id, 403);
        $this->storage->deleteFile($externalFile, $request->user());

        return redirect()->route('admin.clients.files.index', $client)
            ->with('success', 'Arquivo removido.');
    }

    public function storeFolder(Client $client, Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->storage->createClientFolder($client, $request->only(['name', 'description']));

        return redirect()->route('admin.clients.files.index', $client)
            ->with('success', 'Pasta criada.');
    }
}
