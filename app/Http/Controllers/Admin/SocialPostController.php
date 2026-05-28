<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialPostRequest;
use App\Http\Requests\UpdateSocialPostRequest;
use App\Http\Requests\ScheduleSocialPostRequest;
use App\Models\Client;
use App\Models\SocialPost;
use App\Services\Social\SocialPostService;
use App\Services\Social\SocialPublishingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialPostController extends Controller
{
    public function __construct(
        private SocialPostService $postService,
        private SocialPublishingService $publishingService,
    ) {}

    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = SocialPost::visibleTo($user)->with(['client', 'aiEmployee'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('objective')) {
            $query->where('objective', $request->objective);
        }

        $posts   = $query->paginate(20)->withQueryString();
        $clients = Client::visibleTo($user)->orderBy('name')->get();

        return view('admin.social.posts.index', compact('posts', 'clients'));
    }

    public function create()
    {
        $clients = Client::visibleTo(Auth::user())->orderBy('name')->get();
        return view('admin.social.posts.create', compact('clients'));
    }

    public function store(StoreSocialPostRequest $request)
    {
        $post = $this->postService->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.social.posts.show', $post)
            ->with('success', 'Post criado com sucesso.');
    }

    public function show(SocialPost $post)
    {
        $post->load(['client', 'aiEmployee', 'createdBy', 'variants', 'approvalRequests.actions']);
        return view('admin.social.posts.show', compact('post'));
    }

    public function edit(SocialPost $post)
    {
        $clients = Client::visibleTo(Auth::user())->orderBy('name')->get();
        return view('admin.social.posts.edit', compact('post', 'clients'));
    }

    public function update(UpdateSocialPostRequest $request, SocialPost $post)
    {
        $post->update($request->validated());

        return redirect()
            ->route('admin.social.posts.show', $post)
            ->with('success', 'Post atualizado.');
    }

    public function destroy(SocialPost $post)
    {
        $post->delete();
        return redirect()->route('admin.social.posts.index')->with('success', 'Post excluído.');
    }

    public function sendToApproval(SocialPost $post, Request $request)
    {
        $this->postService->sendToApproval($post, $request->user());
        return back()->with('success', 'Post enviado para aprovação.');
    }

    public function approve(SocialPost $post, Request $request)
    {
        $this->postService->approve($post, $request->user(), $request->input('notes'));
        return back()->with('success', 'Post aprovado.');
    }

    public function reject(SocialPost $post, Request $request)
    {
        $this->postService->reject($post, $request->user(), $request->input('notes'));
        return back()->with('success', 'Post rejeitado.');
    }

    public function schedule(ScheduleSocialPostRequest $request, SocialPost $post)
    {
        $this->publishingService->schedule($post, $request->validated('scheduled_at'));
        return back()->with('success', 'Post agendado para ' . $request->validated('scheduled_at') . '.');
    }

    public function markPublished(SocialPost $post, Request $request)
    {
        $this->publishingService->markAsPublished($post, $request->user());
        return back()->with('success', 'Post marcado como publicado.');
    }

    public function backToDraft(SocialPost $post, Request $request)
    {
        $this->postService->backToDraft($post, $request->user());
        return back()->with('success', 'Post voltou para rascunho.');
    }
}
