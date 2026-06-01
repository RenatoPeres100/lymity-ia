<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Services\Social\SocialImagePromptService;
use App\Services\Social\SocialImageService;
use App\Services\Social\SocialPostService;
use App\Services\Social\PublicImageValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialPostAiController extends Controller
{
    public function __construct(
        private SocialPostService           $postService,
        private SocialImageService          $imageService,
        private PublicImageValidatorService $validator,
        private SocialImagePromptService    $promptService,
    ) {}

    // GET /admin/social/posts/ai-create
    public function create()
    {
        return view('admin.social.posts.ai-create');
    }

    // POST /admin/social/posts/ai-create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'theme'           => 'required|string|max:500',
            'objective'       => 'required|in:awareness,engagement,leads,sales,authority,relationship',
            'target_audience' => 'nullable|string|max:200',
            'tone'            => 'nullable|string|max:100',
            'desired_cta'     => 'nullable|string|max:200',
            'creative_brief'  => 'nullable|string|max:1000',
            'image_format'    => 'nullable|in:ai_single,ai_carousel,none',
            'image_prompt'    => 'nullable|string|max:1000',
            'scheduled_at'    => 'nullable|date',
        ]);

        $user = Auth::user();

        try {
            $post = $this->postService->createDraft([
                'title'           => $validated['theme'],
                'objective'       => $validated['objective'],
                'content_type'    => 'feed',
                'creative_format' => 'feed_image',
                'creative_brief'  => $validated['creative_brief'] ?? $validated['theme'],
                'cta'             => $validated['desired_cta'] ?? null,
                'image_prompt'    => $validated['image_prompt'] ?? null,
                'scheduled_at'    => $validated['scheduled_at'] ?? null,
                'requires_approval' => true,
                'metadata' => [
                    'target_audience' => $validated['target_audience'] ?? null,
                    'tone'            => $validated['tone'] ?? null,
                ],
            ], $user);

            // Step 1: Generate caption — always before image
            try {
                $post = $this->postService->generateCaptionWithGemini($post, $user);
            } catch (\Throwable $e) {
                session()->flash('warning', 'Post criado, mas falha ao gerar legenda: ' . $e->getMessage());
            }

            // Step 2: Redirect to edit so admin reviews text before generating image
            $imageFormat = $validated['image_format'] ?? 'none';

            $msg = 'Post criado com IA. Revise o texto antes de gerar a imagem.';

            if ($imageFormat === 'ai_single') {
                $msg .= ' Clique em "Gerar imagem com IA" para gerar a imagem com base no texto final.';
            } elseif ($imageFormat === 'ai_carousel') {
                $msg .= ' Clique em "Gerar carrossel com IA" para criar os slides com base no texto final.';
            }

            return redirect()
                ->route('admin.social.posts.show', $post)
                ->with('success', $msg)
                ->with('suggested_image_format', $imageFormat);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Falha ao criar post: ' . $e->getMessage());
        }
    }

    // POST /admin/social/posts/{post}/generate-caption
    public function generateCaption(SocialPost $post, Request $request)
    {
        try {
            $this->postService->generateCaptionWithGemini($post, $request->user());
            return back()->with('success', 'Legenda gerada com sucesso. Revise antes de gerar a imagem.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao gerar legenda: ' . $e->getMessage());
        }
    }

    // POST /admin/social/posts/{post}/generate-image
    public function generateImage(SocialPost $post, Request $request)
    {
        if (empty(trim($post->main_caption ?? ''))) {
            return back()->with('error', 'Preencha o texto do post antes de gerar a imagem.');
        }

        try {
            $this->imageService->generateSingleImageFromPost($post, $request->user());
            return back()->with('success', 'Imagem gerada com base no texto atual e validada com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao gerar imagem: ' . $e->getMessage());
        }
    }

    // POST /admin/social/posts/{post}/generate-carousel
    public function generateCarousel(SocialPost $post, Request $request)
    {
        if (empty(trim($post->main_caption ?? ''))) {
            return back()->with('error', 'Preencha o texto do post antes de gerar o carrossel.');
        }

        try {
            $this->imageService->generateCarouselFromPost($post, $request->user());
            $post->refresh();
            $count = $post->validAssets()->count();
            return back()->with('success', "Carrossel gerado com {$count} slides válidos. Revise e envie para aprovação.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao gerar carrossel: ' . $e->getMessage());
        }
    }

    // POST /admin/social/posts/{post}/suggest-carousel
    public function suggestCarousel(SocialPost $post)
    {
        $recommended = $this->promptService->shouldSuggestCarousel($post);
        return back()->with(
            $recommended ? 'carousel_suggestion' : 'info',
            $recommended
                ? 'Este conteúdo pode funcionar melhor como carrossel. Deseja gerar carrossel com IA?'
                : 'O conteúdo atual é mais adequado como imagem única.'
        );
    }

    // POST /admin/social/posts/{post}/validate-image
    public function validateImage(SocialPost $post)
    {
        $result = $this->validator->validateSocialPostImage($post);

        $post->update([
            'image_status'           => $result->valid ? 'valid' : 'invalid',
            'image_validation_status'=> $result->valid ? 'valid' : 'invalid',
            'image_validation_error' => $result->valid ? null : $result->error,
        ]);

        if ($result->valid) {
            return back()->with('success', "Imagem válida. {$result->width}x{$result->height}px, {$result->contentType}.");
        }

        return back()->with('error', 'Imagem inválida: ' . $result->error);
    }

    // POST /admin/social/posts/{post}/replace-image-url
    public function replaceImageUrl(SocialPost $post, Request $request)
    {
        $request->validate(['public_image_url' => 'required|url|starts_with:https://']);

        try {
            $this->imageService->replaceImageFromUrl($post, $request->public_image_url, $request->user());
            return back()->with('success', 'Imagem substituída e validada.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao substituir imagem: ' . $e->getMessage());
        }
    }

    // POST /admin/social/posts/{post}/replace-image-upload
    public function replaceImageUpload(SocialPost $post, Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png|max:8192',
        ]);

        try {
            $this->imageService->replaceImageFromUpload($post, $request->file('image'), $request->user());
            return back()->with('success', 'Imagem enviada e validada.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao enviar imagem: ' . $e->getMessage());
        }
    }
}
