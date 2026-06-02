<?php

namespace App\Services\Content;

use App\Models\ActivityLog;
use App\Models\AiGeneratedImage;
use App\Models\AiImageGeneration;
use App\Models\BlogPost;
use App\Models\Company;
use App\Models\SocialPost;
use App\Models\User;
use App\Services\AiImages\GeminiAiImageService;
use Illuminate\Support\Facades\Log;

class ContextualImageGenerationService
{
    public function __construct(
        private GeminiAiImageService $geminiService,
    ) {}

    public function generateForBlogPost(BlogPost $post, User $user): AiImageGeneration
    {
        $company = Company::first();
        $brand   = $this->brandContext();

        $contextParts = [];
        if ($post->title)            $contextParts[] = "Título do artigo: {$post->title}.";
        if ($post->subtitle)         $contextParts[] = "Subtítulo: {$post->subtitle}.";
        if ($post->excerpt)          $contextParts[] = "Resumo: {$post->excerpt}.";
        if ($post->focus_keyword)    $contextParts[] = "Palavra-chave: {$post->focus_keyword}.";
        if ($post->seo_description)  $contextParts[] = "Descrição SEO: {$post->seo_description}.";

        $promptContext = implode(' ', $contextParts);

        $generation = AiImageGeneration::create([
            'company_id'      => $company?->id,
            'client_id'       => $post->client_id,
            'created_by'      => $user->id,
            'related_type'    => BlogPost::class,
            'related_id'      => $post->id,
            'channel'         => 'blog',
            'purpose'         => 'featured_image',
            'generation_type' => 'single',
            'title'           => $post->title,
            'subject'         => $post->focus_keyword ?? $post->title,
            'prompt_context'  => $promptContext,
            'tone'            => 'editorial, premium, technológico',
            'visual_style'    => 'clean, modern, high-end digital agency',
            'brand_context'   => $brand,
            'aspect_ratio'    => '1:1',
            'status'          => 'pending',
            'provider'        => 'google',
        ]);

        $result = $this->geminiService->generateSingle($generation);

        $generation->refresh();

        return $generation;
    }

    public function generateForSocialPost(SocialPost $post, User $user): AiImageGeneration
    {
        $company = Company::first();
        $brand   = $this->brandContext();

        $contextParts = [];
        if ($post->title)          $contextParts[] = "Título do post: {$post->title}.";
        if ($post->main_caption)   $contextParts[] = "Legenda: " . \Str::limit($post->main_caption, 300) . ".";
        if ($post->objective)      $contextParts[] = "Objetivo: {$post->objective}.";
        if ($post->content_type)   $contextParts[] = "Tipo de conteúdo: {$post->content_type}.";
        if ($post->creative_brief) $contextParts[] = "Brief criativo: " . \Str::limit($post->creative_brief, 200) . ".";

        $promptContext = implode(' ', $contextParts);

        $generation = AiImageGeneration::create([
            'company_id'      => $company?->id,
            'client_id'       => $post->client_id,
            'created_by'      => $user->id,
            'related_type'    => SocialPost::class,
            'related_id'      => $post->id,
            'channel'         => 'instagram',
            'purpose'         => 'social_post',
            'generation_type' => 'single',
            'title'           => $post->title,
            'subject'         => $post->title ?? $post->objective,
            'prompt_context'  => $promptContext,
            'tone'            => 'moderno, engajante, premium',
            'visual_style'    => 'clean Instagram visual, high-end digital agency',
            'brand_context'   => $brand,
            'aspect_ratio'    => '1:1',
            'status'          => 'pending',
            'provider'        => 'google',
        ]);

        $result = $this->geminiService->generateSingle($generation);

        $generation->refresh();

        return $generation;
    }

    public function attachToBlogPost(BlogPost $post, AiGeneratedImage $image, User $user): BlogPost
    {
        $post->update([
            'featured_image'              => $image->public_url,
            'featured_image_generation_id'=> $image->ai_image_generation_id,
            'featured_image_source'       => 'ai_generated',
            'featured_image_status'       => 'generated',
            'featured_image_error'        => null,
        ]);

        ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'blog_image_attached',
            'module'      => 'blog',
            'description' => "Imagem IA vinculada ao post de blog #{$post->id}",
            'metadata'    => ['blog_post_id' => $post->id, 'ai_generated_image_id' => $image->id],
        ]);

        return $post->fresh();
    }

    public function attachToSocialPost(SocialPost $post, AiGeneratedImage $image, User $user): SocialPost
    {
        $contextHash = $this->calculateSocialPostContextHash($post);

        $post->update([
            'public_image_url'                    => $image->public_url,
            'image_url'                           => $image->public_url,
            'ai_image_generation_id'              => $image->ai_image_generation_id,
            'image_status'                        => 'valid',
            'image_validation_status'             => 'valid',
            'image_validation_error'              => null,
            'image_generation_mode'               => 'ai_single',
            'image_generated_from_caption_hash'   => $contextHash,
            'image_last_generated_at'             => now(),
        ]);

        ActivityLog::create([
            'user_id'     => $user->id,
            'client_id'   => $post->client_id,
            'action'      => 'social_image_attached',
            'module'      => 'social',
            'description' => "Imagem IA vinculada ao post social #{$post->id}",
            'metadata'    => ['social_post_id' => $post->id, 'ai_generated_image_id' => $image->id],
        ]);

        return $post->fresh();
    }

    public function validateBlogPostImage(BlogPost $post): bool
    {
        if (empty($post->featured_image)) {
            $post->update(['featured_image_status' => 'missing']);
            return false;
        }

        $post->update(['featured_image_status' => 'valid']);
        return true;
    }

    public function validateSocialPostImage(SocialPost $post): bool
    {
        return !empty($post->public_image_url) && $post->image_validation_status === 'valid';
    }

    public function calculateSocialPostContextHash(SocialPost $post): string
    {
        return md5(implode('|', [
            $post->title ?? '',
            $post->main_caption ?? '',
            $post->objective ?? '',
            $post->content_type ?? '',
        ]));
    }

    public function calculateBlogPostContextHash(BlogPost $post): string
    {
        return md5(implode('|', [
            $post->title ?? '',
            $post->subtitle ?? '',
            $post->excerpt ?? '',
            $post->focus_keyword ?? '',
        ]));
    }

    public function isSocialPostImageOutdated(SocialPost $post): bool
    {
        if (empty($post->image_generated_from_caption_hash)) {
            return false;
        }

        return $post->image_generated_from_caption_hash !== $this->calculateSocialPostContextHash($post);
    }

    private function brandContext(): string
    {
        return 'Lymity IA — agência digital inteligente. Visual premium, tecnológico, limpo e editorial. Sem texto pequeno ou ilegível. Cores: tons escuros com destaques em indigo/violeta. Sem rostos genéricos.';
    }
}
