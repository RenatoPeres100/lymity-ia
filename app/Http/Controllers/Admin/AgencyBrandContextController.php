<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AgencyBrandContext;
use App\Models\Company;
use Illuminate\Http\Request;

class AgencyBrandContextController extends Controller
{
    public function index()
    {
        $company = Company::first();
        $context = AgencyBrandContext::where('company_id', $company?->id)->first();

        return view('admin.agency.brand-context.index', compact('context', 'company'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_name'        => 'required|string|max:255',
            'positioning'       => 'nullable|string',
            'tone_of_voice'     => 'nullable|string',
            'target_audience'   => 'nullable|string',
            'main_services'     => 'nullable|string',
            'forbidden_terms'   => 'nullable|string',
            'preferred_terms'   => 'nullable|string',
            'cta_examples'      => 'nullable|string',
            'content_guidelines'=> 'nullable|string',
            'visual_guidelines' => 'nullable|string',
        ]);

        $company = Company::first();
        $validated['company_id'] = $company?->id;

        AgencyBrandContext::create($validated);

        $this->log('brand_context_created', $request->user());

        return redirect()->route('admin.agency.brand-context.index')
            ->with('success', 'Contexto da marca criado com sucesso.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'brand_name'        => 'required|string|max:255',
            'positioning'       => 'nullable|string',
            'tone_of_voice'     => 'nullable|string',
            'target_audience'   => 'nullable|string',
            'main_services'     => 'nullable|string',
            'forbidden_terms'   => 'nullable|string',
            'preferred_terms'   => 'nullable|string',
            'cta_examples'      => 'nullable|string',
            'content_guidelines'=> 'nullable|string',
            'visual_guidelines' => 'nullable|string',
        ]);

        $company = Company::first();
        $context = AgencyBrandContext::where('company_id', $company?->id)->firstOrFail();

        $context->update($validated);

        $this->log('brand_context_updated', $request->user());

        return redirect()->route('admin.agency.brand-context.index')
            ->with('success', 'Contexto da marca atualizado com sucesso.');
    }

    private function log(string $action, $user): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'agency_brand_context',
                'level'       => 'info',
                'description' => "Brand Context: {$action}",
                'metadata'    => [],
            ]);
        } catch (\Throwable) {}
    }
}
