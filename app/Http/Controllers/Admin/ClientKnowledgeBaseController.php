<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientKnowledgeBaseRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientKnowledgeBase;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientKnowledgeBaseController extends Controller
{
    public function index(Client $client): View
    {
        $entries = $client->knowledgeBases()->latest()->get();

        return view('admin.clients.knowledge-base.index', compact('client', 'entries'));
    }

    public function create(Client $client): View
    {
        return view('admin.clients.knowledge-base.create', compact('client'));
    }

    public function store(StoreClientKnowledgeBaseRequest $request, Client $client): RedirectResponse
    {
        $entry = ClientKnowledgeBase::create(array_merge($request->validated(), ['client_id' => $client->id]));

        ActivityLog::record('created', 'client_knowledge_base',
            "Base de conhecimento '{$entry->title}' criada para {$client->name}", $client->id);

        return redirect()->route('admin.clients.knowledge-base.index', $client)
            ->with('success', 'Entrada criada com sucesso!');
    }

    public function edit(Client $client, ClientKnowledgeBase $knowledgeBase): View
    {
        return view('admin.clients.knowledge-base.edit', compact('client', 'knowledgeBase'));
    }

    public function update(StoreClientKnowledgeBaseRequest $request, Client $client, ClientKnowledgeBase $knowledgeBase): RedirectResponse
    {
        $knowledgeBase->update($request->validated());

        ActivityLog::record('updated', 'client_knowledge_base',
            "Base de conhecimento '{$knowledgeBase->title}' atualizada para {$client->name}", $client->id);

        return redirect()->route('admin.clients.knowledge-base.index', $client)
            ->with('success', 'Entrada atualizada com sucesso!');
    }

    public function destroy(Client $client, ClientKnowledgeBase $knowledgeBase): RedirectResponse
    {
        $title = $knowledgeBase->title;
        $knowledgeBase->delete();

        ActivityLog::record('deleted', 'client_knowledge_base',
            "Entrada '{$title}' removida de {$client->name}", $client->id);

        return redirect()->route('admin.clients.knowledge-base.index', $client)
            ->with('success', 'Entrada removida com sucesso!');
    }
}
