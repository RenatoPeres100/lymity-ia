<?php

namespace App\Http\Controllers\Admin\Prospecting;

use App\Http\Controllers\Controller;
use App\Models\ProspectMessageSuggestion;
use App\Services\Prospecting\ProspectingLogService;
use Illuminate\Http\Request;

class MessageSuggestionController extends Controller
{
    public function __construct(private ProspectingLogService $logger) {}

    public function approve(Request $request, ProspectMessageSuggestion $message)
    {
        abort_unless($request->user()->can('approveMessage', $message), 403);

        $message->update([
            'status'              => 'approved',
            'approved_by_user_id' => $request->user()->id,
            'approved_at'         => now(),
        ]);

        $this->logger->log('prospect.message.approved', $message, $request->user());

        return redirect()->back()->with('success', "Mensagem aprovada.");
    }

    public function reject(Request $request, ProspectMessageSuggestion $message)
    {
        abort_unless($request->user()->can('approveMessage', $message), 403);

        $message->update(['status' => 'rejected']);

        $this->logger->log('prospect.message.rejected', $message, $request->user());

        return redirect()->back()->with('success', "Mensagem rejeitada.");
    }

    public function markUsed(Request $request, ProspectMessageSuggestion $message)
    {
        abort_unless($request->user()->can('markUsed', $message), 403);

        $message->update([
            'status'  => 'used',
            'used_at' => now(),
        ]);

        $this->logger->log('prospect.message.used', $message, $request->user());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Marcada como usada.']);
        }

        return redirect()->back()->with('success', "Mensagem marcada como usada.");
    }
}
