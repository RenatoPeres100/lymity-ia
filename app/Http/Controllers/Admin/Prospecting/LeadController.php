<?php

namespace App\Http\Controllers\Admin\Prospecting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prospecting\MoveProspectStageRequest;
use App\Http\Requests\Prospecting\StoreProspectLeadRequest;
use App\Http\Requests\Prospecting\UpdateProspectLeadRequest;
use App\Models\ProspectLead;
use App\Models\ProspectStage;
use App\Models\User;
use App\Services\Prospecting\ProspectingPipelineService;
use App\Services\Prospecting\ProspectingService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(
        private ProspectingService        $service,
        private ProspectingPipelineService $pipelineService,
    ) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('prospecting.view'), 403);

        $query = ProspectLead::visibleTo($request->user())->with(['stage', 'owner', 'pipeline']);

        if ($s = $request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('company_name', 'like', "%{$s}%")
                ->orWhere('segment', 'like', "%{$s}%"));
        }

        if ($v = $request->stage_id) {
            $query->where('prospect_stage_id', $v);
        }
        if ($v = $request->status) {
            $query->where('status', $v);
        }
        if ($v = $request->interest_level) {
            $query->where('interest_level', $v);
        }
        if ($v = $request->owner_id) {
            $query->where('owner_id', $v);
        }
        if ($v = $request->segment) {
            $query->where('segment', 'like', "%{$v}%");
        }
        if ($request->overdue) {
            $query->where('status', 'open')
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '<', now());
        }

        $leads = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $stages = ProspectStage::visibleTo($request->user())->active()->orderBy('position')->get();
        $owners = User::where('status', 'active')->orderBy('name')->get();

        return view('admin.prospecting.leads.index', compact('leads', 'stages', 'owners'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('create', ProspectLead::class), 403);

        $stages  = ProspectStage::visibleTo($request->user())->active()->orderBy('position')->get();
        $owners  = User::where('status', 'active')->orderBy('name')->get();
        $pipeline = $this->pipelineService->getDefaultPipelineForCompany(
            $request->user()->isAdminGeral() ? \App\Models\Company::first() : $request->user()->company
        );

        return view('admin.prospecting.leads.create', compact('stages', 'owners', 'pipeline'));
    }

    public function store(StoreProspectLeadRequest $request)
    {
        $lead = $this->service->createLead($request->validated(), $request->user());

        return redirect()->route('admin.prospecting.leads.show', $lead)
            ->with('success', "Lead \"{$lead->name}\" criado com sucesso.");
    }

    public function show(Request $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('view', $lead), 403);

        $lead->load([
            'stage', 'pipeline', 'owner', 'company',
            'activities.user',
            'noteEntries.user',
            'insights' => fn($q) => $q->orderByDesc('created_at'),
            'messageSuggestions' => fn($q) => $q->orderByDesc('created_at'),
        ]);

        $stages = ProspectStage::visibleTo($request->user())->active()->orderBy('position')->get();
        $canGenerateAi = $request->user()->hasAnyPermission([
            'prospecting.ai.analyze', 'prospecting.ai.qualify',
            'prospecting.ai.generate_message', 'prospecting.ai.suggest_next_action',
        ]);

        return view('admin.prospecting.leads.show', compact('lead', 'stages', 'canGenerateAi'));
    }

    public function edit(Request $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('update', $lead), 403);

        $stages  = ProspectStage::visibleTo($request->user())->active()->orderBy('position')->get();
        $owners  = User::where('status', 'active')->orderBy('name')->get();

        return view('admin.prospecting.leads.edit', compact('lead', 'stages', 'owners'));
    }

    public function update(UpdateProspectLeadRequest $request, ProspectLead $lead)
    {
        $this->service->updateLead($lead, $request->validated(), $request->user());

        return redirect()->route('admin.prospecting.leads.show', $lead)
            ->with('success', "Lead \"{$lead->name}\" atualizado com sucesso.");
    }

    public function moveStage(MoveProspectStageRequest $request, ProspectLead $lead)
    {
        $stage = ProspectStage::findOrFail($request->prospect_stage_id);
        $this->service->moveStage($lead, $stage, $request->user());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Etapa atualizada.', 'stage' => $stage->name]);
        }

        return redirect()->back()->with('success', "Lead movido para \"{$stage->name}\".");
    }

    public function archive(Request $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('archive', $lead), 403);
        $this->service->archiveLead($lead, $request->user());

        return redirect()->route('admin.prospecting.leads.index')
            ->with('success', "Lead \"{$lead->name}\" arquivado.");
    }

    public function destroy(Request $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('delete', $lead), 403);
        $lead->delete();

        return redirect()->route('admin.prospecting.leads.index')
            ->with('success', "Lead excluído.");
    }
}
