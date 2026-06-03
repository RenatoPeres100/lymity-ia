<?php

namespace App\Http\Controllers\Admin\Prospecting;

use App\Http\Controllers\Controller;
use App\Services\Prospecting\ProspectingPipelineService;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function __construct(private ProspectingPipelineService $pipelineService) {}

    public function __invoke(Request $request)
    {
        abort_unless($request->user()->hasPermission('prospecting.pipeline.view'), 403);

        $kanban = $this->pipelineService->getKanbanData($request->user());

        return view('admin.prospecting.pipeline', compact('kanban'));
    }
}
