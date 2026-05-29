<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
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
            'image_prompt'    => 'nullable|string|max:1000',
            'scheduled_at'    => 'nullable|date',
            'generate_image'  => 'nullable|boolean',
        ]);

        $user = Auth::user();

        try {
            $post = $this->postService->createDraft([
                'title'          => $validated['theme'],
                'objective'      => $validated['objective'],
                'content_type'   => 'feed',
                'creative_format'=> 'feed_image',
                'creative_brief' => $validated['theme'],
                'image_prompt'   => $validated['image_prompt'] ?? null,
                'scheduled_at'   => $validated['scheduled_at'] ?? null,
                'requires_approval' => true,
                'metadata' => [
                    'target_audience' => $validated['target_audience'] ?? null,
                    'tone'            => $validated['tone'] ?? null,
                    'desired_cta'     => $validated['desired_cta'] ?? null,
                ],
            ], $user);

            // Generate caption
            try {
                $post = $this->postService->generateCaptionWithGemini($post, $user);
            } catch (\Throwable $e) {
                session()->flash('warning', 'Post criado, mas falha ao gerar legenda: ' . $e->getMessage());
            }

            // Generate image if requested
            if (!empty($validated['generate_image'])) {
                try {
                    $post = $this->imageService->generateWithGemini($post, $user);
                } catch (\Throwable $e) {
                    session()->flash('warning_image', 'Imagem não gerada: ' . $e->getMessage());
                }
            }

            return redirect()
                ->route('admin.social.posts.show', $post)
                ->with('success', 'Post criado com IA. Revise antes de enviar para aprovação.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Falha ao criar post: ' . $e->getMessage());
        }
    }

    // POST /admin/social/posts/{post}/generate-caption
    public function generateCaption(SocialPost $post, Request $request)
    {
        try {
            $this->postService->generateCaptionWithGemini($post, $request->user());
            return back()->with('success', 'Legenda gerada com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao gerar legenda: ' . $e->getMessage());
        }
    }

    // POST /admin/social/posts/{post}/generate-image
    public function generateImage(SocialPost $post, Request $request)
    {
        try {
            $this->imageService->generateWithGemini($post, $request->user());
            return back()->with('success', 'Imagem gerada e validada com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao gerar imagem: ' . $e->getMessage());
        }
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
