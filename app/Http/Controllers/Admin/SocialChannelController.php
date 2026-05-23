<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialChannelRequest;
use App\Http\Requests\UpdateSocialChannelRequest;
use App\Models\Client;
use App\Models\SocialChannel;

class SocialChannelController extends Controller
{
    public function index()
    {
        $channels = SocialChannel::with('client')->latest()->paginate(20);
        return view('admin.social.channels.index', compact('channels'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.social.channels.create', compact('clients'));
    }

    public function store(StoreSocialChannelRequest $request)
    {
        $data = $request->validated();
        // Never persist real tokens from this form
        unset($data['access_token'], $data['refresh_token']);

        SocialChannel::create($data);
        return redirect()->route('admin.social.channels.index')->with('success', 'Canal criado.');
    }

    public function edit(SocialChannel $channel)
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.social.channels.edit', compact('channel', 'clients'));
    }

    public function update(UpdateSocialChannelRequest $request, SocialChannel $channel)
    {
        $data = $request->validated();
        unset($data['access_token'], $data['refresh_token']);

        $channel->update($data);
        return redirect()->route('admin.social.channels.index')->with('success', 'Canal atualizado.');
    }

    public function destroy(SocialChannel $channel)
    {
        $channel->delete();
        return redirect()->route('admin.social.channels.index')->with('success', 'Canal removido.');
    }
}
