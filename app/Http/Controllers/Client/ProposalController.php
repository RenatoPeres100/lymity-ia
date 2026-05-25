<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Proposal;
use App\Services\Commercial\ProposalService;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function __construct(protected ProposalService $service) {}

    public function index()
    {
        $clientId  = auth()->user()->client_id;
        $proposals = Proposal::where('client_id', $clientId)
            ->with('creator')
            ->latest()
            ->paginate(20);

        return view('client.proposals.index', compact('proposals'));
    }

    public function show(Proposal $proposal)
    {
        abort_unless($proposal->client_id === auth()->user()->client_id, 403);
        $proposal->load(['creator', 'items']);
        return view('client.proposals.show', compact('proposal'));
    }

    public function accept(Request $request, Proposal $proposal)
    {
        abort_unless($proposal->client_id === auth()->user()->client_id, 403);
        $notes = $request->input('notes');
        $this->service->acceptByClient($proposal, auth()->user(), $notes);
        return back()->with('success', 'Proposta aceita com sucesso!');
    }

    public function reject(Request $request, Proposal $proposal)
    {
        abort_unless($proposal->client_id === auth()->user()->client_id, 403);
        $notes = $request->input('notes');
        $this->service->rejectByClient($proposal, auth()->user(), $notes);
        return back()->with('success', 'Proposta rejeitada.');
    }

    public function comment(Request $request, Proposal $proposal)
    {
        abort_unless($proposal->client_id === auth()->user()->client_id, 403);

        $request->validate(['comment' => 'required|string|max:2000']);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'client_id'   => $proposal->client_id,
            'action'      => 'proposal_comment',
            'module'      => 'commercial',
            'description' => "Comentário na proposta [{$proposal->title}]: " . $request->comment,
        ]);

        return back()->with('success', 'Comentário registrado.');
    }
}
