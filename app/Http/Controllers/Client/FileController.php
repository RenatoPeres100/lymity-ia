<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ExternalFile;
use App\Services\Files\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FileController extends Controller
{
    public function __construct(private FileStorageService $storage) {}

    private function getClient(Request $request)
    {
        return $request->user()->client;
    }

    public function index(Request $request): View
    {
        $client  = $this->getClient($request);
        abort_if(!$client, 403, 'Acesso restrito a clientes.');

        $files   = $this->storage->listForClient($client, $request->only(['file_type', 'search']));
        $folders = $client->folders()->orderBy('name')->get();

        return view('client.files.index', compact('files', 'folders', 'client'));
    }

    public function create(Request $request): View
    {
        $client = $this->getClient($request);
        abort_if(!$client, 403);
        return view('client.files.create', compact('client'));
    }

    public function store(Request $request): RedirectResponse
    {
        $client = $this->getClient($request);
        abort_if(!$client, 403);

        $request->validate([
            'file'  => 'required|file|max:10240',
            'notes' => 'nullable|string',
        ]);

        $this->storage->uploadLocal(
            $request->file('file'),
            ['client_id' => $client->id, 'notes' => $request->notes],
            $request->user()
        );

        return redirect()->route('client.files.index')
            ->with('success', 'Arquivo enviado com sucesso.');
    }

    public function show(ExternalFile $externalFile, Request $request): View
    {
        $client = $this->getClient($request);
        abort_if(!$client || $externalFile->client_id !== $client->id, 403);

        $url = $this->storage->getPublicUrl($externalFile);
        return view('client.files.show', ['file' => $externalFile, 'url' => $url, 'client' => $client]);
    }

    public function destroy(ExternalFile $externalFile, Request $request): RedirectResponse
    {
        $client = $this->getClient($request);
        abort_if(!$client || $externalFile->client_id !== $client->id, 403);

        $this->storage->deleteFile($externalFile, $request->user());

        return redirect()->route('client.files.index')
            ->with('success', 'Arquivo removido.');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $client = $this->getClient($request);
        abort_if(!$client, 403);

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->storage->createClientFolder($client, $request->only(['name', 'description']));

        return redirect()->route('client.files.index')
            ->with('success', 'Pasta criada.');
    }
}
