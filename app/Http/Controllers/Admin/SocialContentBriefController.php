<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialContentBriefRequest;
use App\Http\Requests\UpdateSocialContentBriefRequest;
use App\Models\Client;
use App\Models\SocialContentBrief;
use Illuminate\Support\Facades\Auth;

class SocialContentBriefController extends Controller
{
    public function index()
    {
        $briefs  = SocialContentBrief::with('client')->latest()->paginate(20);
        return view('admin.social.briefs.index', compact('briefs'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.social.briefs.create', compact('clients'));
    }

    public function store(StoreSocialContentBriefRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        SocialContentBrief::create($data);
        return redirect()->route('admin.social.briefs.index')->with('success', 'Brief criado.');
    }

    public function show(SocialContentBrief $brief)
    {
        $brief->load('client', 'creator');
        return view('admin.social.briefs.show', compact('brief'));
    }

    public function edit(SocialContentBrief $brief)
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.social.briefs.edit', compact('brief', 'clients'));
    }

    public function update(UpdateSocialContentBriefRequest $request, SocialContentBrief $brief)
    {
        $brief->update($request->validated());
        return redirect()->route('admin.social.briefs.show', $brief)->with('success', 'Brief atualizado.');
    }

    public function destroy(SocialContentBrief $brief)
    {
        $brief->delete();
        return redirect()->route('admin.social.briefs.index')->with('success', 'Brief excluído.');
    }
}
