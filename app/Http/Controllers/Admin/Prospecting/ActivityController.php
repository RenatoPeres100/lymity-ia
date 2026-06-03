<?php

namespace App\Http\Controllers\Admin\Prospecting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prospecting\StoreProspectActivityRequest;
use App\Models\ProspectActivity;
use App\Models\ProspectLead;
use App\Services\Prospecting\ProspectingService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(private ProspectingService $service) {}

    public function store(StoreProspectActivityRequest $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('view', $lead), 403);

        $activity = $this->service->createActivity($lead, $request->validated(), $request->user());

        return redirect()->route('admin.prospecting.leads.show', $lead)
            ->with('success', "Atividade \"{$activity->title}\" criada.");
    }

    public function complete(Request $request, ProspectActivity $activity)
    {
        abort_unless($request->user()->can('update', $activity), 403);

        $this->service->completeActivity($activity, $request->input('outcome'), $request->user());

        return redirect()->back()->with('success', "Atividade concluída.");
    }

    public function cancel(Request $request, ProspectActivity $activity)
    {
        abort_unless($request->user()->can('update', $activity), 403);

        $this->service->cancelActivity($activity, $request->user());

        return redirect()->back()->with('success', "Atividade cancelada.");
    }
}
