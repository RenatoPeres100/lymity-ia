<?php

namespace App\Http\Controllers\Admin\Social;

use App\Http\Controllers\Controller;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Services\Social\ThreadsTextPipelineService;
use App\Services\Threads\ThreadsPublishingService;
use App\Services\Threads\ThreadsReadinessService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ThreadsPostController extends Controller
{
    public function __construct(
        private ThreadsTextPipelineService $pipeline,
        private ThreadsReadinessService    $readiness,
        private ThreadsPublishingService   $publisher,
    ) {}

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = SocialPost::where('platform', 'threads')
            ->where('company_id', $user->company_id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts   = $query->paginate(20);
        $channel = SocialChannel::where('platform', 'threads')
            ->where('company_id', $user->company_id)
            ->whereNull('client_id')
            ->first();

        return view('admin.social.threads.posts.index', compact('posts', 'channel'));
    }

    public function create()
    {
        return view('admin.social.threads.posts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'nullable|string|max:255',
            'main_caption' => 'required|string|min:10|max:1800',
            'cta'          => 'nullable|string|max:255',
            'hashtags'     => 'nullable|string|max:500',
        ]);

        $post = $this->pipeline->createManualDraft($data, $request->user());

        return redirect()->route('admin.social.threads.posts.show', $post)
            ->with('success', 'Post Threads criado com sucesso.');
    }

    public function show(SocialPost $post)
    {
        abort_unless($post->platform === 'threads', 404);

        $channel     = SocialChannel::where('platform', 'threads')
            ->where('company_id', $post->company_id)
            ->whereNull('client_id')
            ->first();
        $canSchedule = $this->readiness->canBeScheduled($post);
        $canPublish  = $this->readiness->canBePublished($post);
        $publishingEnabled = config('threads.publishing_enabled', false);

        return view('admin.social.threads.posts.show', compact('post', 'channel', 'canSchedule', 'canPublish', 'publishingEnabled'));
    }

    public function edit(SocialPost $post)
    {
        abort_unless($post->platform === 'threads', 404);
        abort_unless(in_array($post->status, ['draft', 'rejected', 'pending_approval']), 422,
            'Post aprovado ou publicado não pode ser editado.');
        return view('admin.social.threads.posts.edit', compact('post'));
    }

    public function update(Request $request, SocialPost $post)
    {
        abort_unless($post->platform === 'threads', 404);
        abort_unless(in_array($post->status, ['draft', 'rejected', 'pending_approval']), 422,
            'Post aprovado ou publicado não pode ser editado.');

        $data = $request->validate([
            'title'        => 'nullable|string|max:255',
            'main_caption' => 'required|string|min:10|max:1800',
            'cta'          => 'nullable|string|max:255',
            'hashtags'     => 'nullable|string|max:500',
        ]);

        $hashtags = $data['hashtags'] ?? '';
        $post->update([
            'title'        => $data['title'] ?? $post->title,
            'main_caption' => $data['main_caption'],
            'cta'          => $data['cta'],
            'hashtags'     => $hashtags,
        ]);

        return redirect()->route('admin.social.threads.posts.show', $post)
            ->with('success', 'Post atualizado com sucesso.');
    }

    public function submitApproval(Request $request, SocialPost $post)
    {
        abort_unless($post->platform === 'threads', 404);
        $approval = $this->pipeline->submitForApproval($post, $request->user());

        return redirect()->route('admin.social.threads.posts.show', $post)
            ->with('success', 'Post enviado para aprovação. ID: ' . $approval->id);
    }

    public function schedule(Request $request, SocialPost $post)
    {
        abort_unless($post->platform === 'threads', 404);

        $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $scheduledAt = Carbon::parse($request->scheduled_at);
        $this->pipeline->schedule($post, $scheduledAt, $request->user());

        return redirect()->route('admin.social.threads.posts.show', $post)
            ->with('success', 'Post agendado para ' . $scheduledAt->format('d/m/Y H:i') . '.');
    }

    public function publishNow(Request $request, SocialPost $post)
    {
        abort_unless($post->platform === 'threads', 404);

        $user = $request->user();
        abort_unless(
            in_array($user->role ?? $user->roles?->first()?->name ?? '', ['admin_geral', 'agencia_admin', 'admin']),
            403,
            'Apenas admin_geral ou agencia_admin podem publicar manualmente.'
        );

        if (!config('threads.publishing_enabled', false)) {
            return back()->withErrors(['threads' => 'Publicação desabilitada (THREADS_PUBLISHING_ENABLED=false). Ative no .env após validar a conexão.']);
        }

        $canPub = $this->readiness->canBePublished($post);
        if (!$canPub['ready']) {
            return back()->withErrors(['threads' => 'Não é possível publicar: ' . implode('; ', $canPub['reasons'])]);
        }

        try {
            $this->publisher->publishTextPost($post);
            $post->refresh();
            return redirect()->route('admin.social.threads.posts.show', $post)
                ->with('success', 'Post publicado no Threads! ID externo: ' . ($post->external_post_id ?? 'N/D'));
        } catch (\Throwable $e) {
            $this->publisher->handleError($e, $post);
            return redirect()->route('admin.social.threads.posts.show', $post)
                ->withErrors(['threads' => 'Falha ao publicar: ' . $e->getMessage()]);
        }
    }
}
