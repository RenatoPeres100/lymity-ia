<?php

namespace App\Http\Controllers\Admin\Prospecting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prospecting\StoreProspectNoteRequest;
use App\Models\ProspectLead;
use App\Services\Prospecting\ProspectingService;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct(private ProspectingService $service) {}

    public function store(StoreProspectNoteRequest $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('view', $lead), 403);

        $this->service->createNote($lead, $request->validated(), $request->user());

        return redirect()->route('admin.prospecting.leads.show', $lead)
            ->with('success', "Nota adicionada.");
    }
}
