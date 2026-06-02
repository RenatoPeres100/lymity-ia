<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiCarouselJob;
use App\Jobs\GenerateAiImageJob;
use App\Models\AiImageGeneration;
use App\Models\BlogPost;
use App\Models\Company;
use App\Models\SocialPost;
use App\Services\AiImages\GeminiAiImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiImageController extends Controller
{
    public function __construct(
        private readonly GeminiAiImageService $imageService,
    ) {}

    public function index(Request $request)
    {
        $query = AiImageGeneration::with(['creator', 'activeImages'])
            ->orderByDesc('created_at');

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('type')) {
            $query->where('generation_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $generations = $query->paginate(20)->withQueryString();

        $stats = [
            'today'     => AiImageGeneration::whereDate('created_at', today())->count(),
            'carousels' => AiImageGeneration::where('generation_type', 'carousel')->whereDate('created_at', today())->count(),
            'failed'    => AiImageGeneration::where('status', 'failed')->whereDate('created_at', today())->count(),
        ];

        return view('admin.ai-images.index', compact('generations', 'stats'));
    }

    public function create(Request $request)
    {
        $socialPosts = SocialPost::whereIn('status', ['draft', 'pending_approval', 'approved'])
            ->orderByDesc('created_at')->get();
        $blogPosts   = BlogPost::whereIn('status', ['draft', 'pending_approval', 'approved'])
            ->orderByDesc('created_at')->get();

        // Pre-fill from social post
        $prefill = [];
        if ($request->filled('social_post_id')) {
            $sp = SocialPost::find($request->social_post_id);
            if ($sp) {
                $prefill = [
                    'title'          => $sp->title,
                    'channel'        => 'social',
                    'purpose'        => 'social_single',
                    'prompt_context' => implode("\n", array_filter([$sp->main_caption, $sp->creative_brief])),
                    'related_type'   => SocialPost::class,
                    'related_id'     => $sp->id,
                ];
            }
        }
        if ($request->filled('blog_post_id')) {
            $bp = BlogPost::find($request->blog_post_id);
            if ($bp) {
                $prefill = [
                    'title'          => $bp->title,
                    'channel'        => 'blog',
                    'purpose'        => 'featured_image',
                    'subject'        => $bp->subtitle,
                    'prompt_context' => implode("\n", array_filter([$bp->excerpt, $bp->seo_description])),
                    'related_type'   => BlogPost::class,
                    'related_id'     => $bp->id,
                ];
            }
        }

        return view('admin.ai-images.create', compact('socialPosts', 'blogPosts', 'prefill'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel'         => 'required|in:blog,social,general',
            'purpose'         => 'required|string',
            'generation_type' => 'required|in:single,carousel',
            'title'           => 'nullable|string|max:255',
            'subject'         => 'nullable|string|max:255',
            'prompt_context'  => 'nullable|string',
            'target_audience' => 'nullable|string|max:255',
            'tone'            => 'nullable|string|max:100',
            'visual_style'    => 'nullable|string|max:100',
            'brand_context'   => 'nullable|string',
            'overlay_mode'    => 'nullable|in:none,safe_top,safe_bottom,safe_left,safe_right,center_free',
            'overlay_text'    => 'nullable|string|max:255',
            'slides_count'    => 'nullable|integer|min:1|max:10',
            'aspect_ratio'    => 'nullable|string|max:20',
            'related_type'    => 'nullable|string',
            'related_id'      => 'nullable|integer',
            'action'          => 'nullable|in:draft,generate,queue',
        ]);

        $company = Company::first();

        $generation = AiImageGeneration::create([
            'company_id'      => $company?->id,
            'created_by'      => Auth::id(),
            'channel'         => $validated['channel'],
            'purpose'         => $validated['purpose'],
            'generation_type' => $validated['generation_type'],
            'title'           => $validated['title'] ?? null,
            'subject'         => $validated['subject'] ?? null,
            'prompt_context'  => $validated['prompt_context'] ?? null,
            'target_audience' => $validated['target_audience'] ?? null,
            'tone'            => $validated['tone'] ?? null,
            'visual_style'    => $validated['visual_style'] ?? null,
            'brand_context'   => $validated['brand_context'] ?? null,
            'overlay_mode'    => $validated['overlay_mode'] ?? 'none',
            'overlay_text'    => $validated['overlay_text'] ?? null,
            'slides_count'    => $validated['slides_count'] ?? 1,
            'aspect_ratio'    => $validated['aspect_ratio'] ?? '1:1',
            'related_type'    => $validated['related_type'] ?? null,
            'related_id'      => $validated['related_id'] ?? null,
            'status'          => 'draft',
        ]);

        $action = $validated['action'] ?? 'draft';

        if ($action === 'generate') {
            return $this->dispatchGeneration($generation, sync: true);
        }
        if ($action === 'queue') {
            return $this->dispatchGeneration($generation, sync: false);
        }

        return redirect()->route('admin.ai-images.show', $generation)
            ->with('success', 'Geração criada como rascunho. Clique em "Gerar" quando pronto.');
    }

    public function show(AiImageGeneration $aiImageGeneration)
    {
        $aiImageGeneration->load(['images', 'creator', 'company', 'client', 'related']);
        return view('admin.ai-images.show', ['generation' => $aiImageGeneration]);
    }

    public function edit(AiImageGeneration $aiImageGeneration)
    {
        $socialPosts = SocialPost::whereIn('status', ['draft', 'pending_approval', 'approved'])
            ->orderByDesc('created_at')->get();
        $blogPosts   = BlogPost::whereIn('status', ['draft', 'pending_approval', 'approved'])
            ->orderByDesc('created_at')->get();

        return view('admin.ai-images.edit', [
            'generation'  => $aiImageGeneration,
            'socialPosts' => $socialPosts,
            'blogPosts'   => $blogPosts,
        ]);
    }

    public function update(Request $request, AiImageGeneration $aiImageGeneration)
    {
        $validated = $request->validate([
            'channel'         => 'required|in:blog,social,general',
            'purpose'         => 'required|string',
            'generation_type' => 'required|in:single,carousel',
            'title'           => 'nullable|string|max:255',
            'subject'         => 'nullable|string|max:255',
            'prompt_context'  => 'nullable|string',
            'target_audience' => 'nullable|string|max:255',
            'tone'            => 'nullable|string|max:100',
            'visual_style'    => 'nullable|string|max:100',
            'brand_context'   => 'nullable|string',
            'overlay_mode'    => 'nullable|in:none,safe_top,safe_bottom,safe_left,safe_right,center_free',
            'overlay_text'    => 'nullable|string|max:255',
            'slides_count'    => 'nullable|integer|min:1|max:10',
            'aspect_ratio'    => 'nullable|string|max:20',
        ]);

        $aiImageGeneration->update($validated);

        return redirect()->route('admin.ai-images.show', $aiImageGeneration)
            ->with('success', 'Geração atualizada com sucesso.');
    }

    public function generate(Request $request, AiImageGeneration $aiImageGeneration)
    {
        if (!$this->imageService->isConfigured()) {
            $status = $this->imageService->testConnection();
            return back()->with('error', $status['message'] ?? 'Gemini não configurado para geração de imagens.');
        }

        return $this->dispatchGeneration($aiImageGeneration, sync: true);
    }

    public function regenerate(Request $request, AiImageGeneration $aiImageGeneration)
    {
        if (!$aiImageGeneration->canRegenerate()) {
            return back()->with('error', 'Esta geração não pode ser regenerada no estado atual.');
        }

        // Archive previous images
        $aiImageGeneration->images()->update(['status' => 'replaced']);
        $aiImageGeneration->update(['status' => 'draft', 'error_message' => null]);

        return $this->generate($request, $aiImageGeneration);
    }

    public function archive(AiImageGeneration $aiImageGeneration)
    {
        $aiImageGeneration->update(['status' => 'archived']);
        return back()->with('success', 'Geração arquivada.');
    }

    public function attachToSocialPost(Request $request, AiImageGeneration $aiImageGeneration, SocialPost $socialPost)
    {
        if (!$aiImageGeneration->canAttach()) {
            return back()->with('error', 'A geração precisa estar completa e ter imagens ativas para ser vinculada.');
        }

        $mainImage = $aiImageGeneration->mainImage();
        if (!$mainImage) {
            return back()->with('error', 'Nenhuma imagem ativa encontrada nesta geração.');
        }

        // Reset approval if post was already approved
        $wasApproved = in_array($socialPost->status, ['approved', 'scheduled']);

        $socialPost->update([
            'ai_image_generation_id' => $aiImageGeneration->id,
            'public_image_url'       => $mainImage->public_url,
            'image_path'             => $mainImage->storage_path,
            'image_status'           => 'generated',
            'image_generation_mode'  => 'ai_generated',
            'status'                 => $wasApproved ? 'draft' : $socialPost->status,
            'approved_by'            => $wasApproved ? null : $socialPost->approved_by,
            'approved_at'            => $wasApproved ? null : $socialPost->approved_at,
        ]);

        $msg = 'Imagem vinculada ao post social com sucesso.';
        if ($wasApproved) {
            $msg .= ' O post foi revertido para rascunho pois a imagem foi alterada após aprovação.';
        }

        return back()->with('success', $msg);
    }

    public function attachToBlogPost(Request $request, AiImageGeneration $aiImageGeneration, BlogPost $blogPost)
    {
        if (!$aiImageGeneration->canAttach()) {
            return back()->with('error', 'A geração precisa estar completa e ter imagens ativas para ser vinculada.');
        }

        $mainImage = $aiImageGeneration->mainImage();
        if (!$mainImage) {
            return back()->with('error', 'Nenhuma imagem ativa encontrada nesta geração.');
        }

        $blogPost->update([
            'featured_image'                => $mainImage->public_url,
            'featured_image_generation_id'  => $aiImageGeneration->id,
        ]);

        return back()->with('success', 'Imagem de capa vinculada ao artigo do blog com sucesso.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function dispatchGeneration(AiImageGeneration $generation, bool $sync = false)
    {
        if (!$this->imageService->isConfigured()) {
            $status = $this->imageService->testConnection();
            $generation->update(['status' => 'failed', 'error_message' => $status['message']]);
            return redirect()->route('admin.ai-images.show', $generation)
                ->with('error', $status['message']);
        }

        $isCarousel = $generation->isCarousel();
        $generation->update(['status' => 'queued']);

        if ($sync) {
            try {
                if ($isCarousel) {
                    $this->imageService->generateCarousel($generation);
                } else {
                    $this->imageService->generateSingle($generation);
                }
                return redirect()->route('admin.ai-images.show', $generation->fresh())
                    ->with('success', 'Imagem(ns) gerada(s) com sucesso!');
            } catch (\Throwable $e) {
                return redirect()->route('admin.ai-images.show', $generation->fresh())
                    ->with('error', 'Falha na geração: ' . $e->getMessage());
            }
        }

        if ($isCarousel) {
            GenerateAiCarouselJob::dispatch($generation->id);
        } else {
            GenerateAiImageJob::dispatch($generation->id);
        }

        return redirect()->route('admin.ai-images.show', $generation)
            ->with('success', 'Geração enfileirada. Acompanhe o status nesta página.');
    }
}
