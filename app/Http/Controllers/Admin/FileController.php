<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ExternalFile;
use App\Services\Files\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FileController extends Controller
{
    public function __construct(private FileStorageService $storage) {}

    public function index(Request $request): View
    {
        $clients = Client::orderBy('name')->get();
        $files   = $this->storage->listForAdmin($request->only(['client_id', 'source', 'file_type', 'search']));

        return view('admin.files.index', compact('files', 'clients'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.files.create', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file'         => 'required|file|max:10240',
            'client_id'    => 'nullable|exists:clients,id',
            'company_id'   => 'nullable|exists:companies,id',
            'notes'        => 'nullable|string',
            'related_type' => 'nullable|string|max:255',
            'related_id'   => 'nullable|integer',
        ]);

        $file = $this->storage->uploadLocal(
            $request->file('file'),
            $request->only(['client_id', 'company_id', 'notes', 'related_type', 'related_id']),
            $request->user()
        );

        if ($request->filled('related_type') && $request->filled('related_id')) {
            $this->storage->relateFile($file, $request->related_type, (int) $request->related_id);
        }

        return redirect()->route('admin.files.show', $file)
            ->with('success', 'Arquivo enviado com sucesso.');
    }

    public function show(ExternalFile $externalFile): View
    {
        $externalFile->load(['client', 'uploader']);
        $url = $this->storage->getPublicUrl($externalFile);
        return view('admin.files.show', ['file' => $externalFile, 'url' => $url]);
    }

    public function destroy(ExternalFile $externalFile, Request $request): RedirectResponse
    {
        $this->storage->deleteFile($externalFile, $request->user());

        return redirect()->route('admin.files.index')
            ->with('success', 'Arquivo removido.');
    }

    public function relate(ExternalFile $externalFile, Request $request): RedirectResponse
    {
        $request->validate([
            'related_type' => 'required|string|max:255',
            'related_id'   => 'required|integer',
        ]);

        $this->storage->relateFile($externalFile, $request->related_type, (int) $request->related_id);

        return back()->with('success', 'Arquivo vinculado.');
    }
}
