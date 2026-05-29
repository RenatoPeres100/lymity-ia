<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Services\Social\PublicImageValidatorService;
use Illuminate\Console\Command;

class SocialValidateImageCommand extends Command
{
    protected $signature   = 'social:validate-image {post_id : ID do SocialPost}';
    protected $description = 'Valida a imagem pública de um post social e atualiza o status no banco.';

    public function handle(PublicImageValidatorService $validator): int
    {
        $post = SocialPost::find($this->argument('post_id'));

        if (!$post) {
            $this->error("Post #{$this->argument('post_id')} não encontrado.");
            return self::FAILURE;
        }

        $this->info("Validando imagem do Post #{$post->id} — {$post->title}");
        $this->line("  URL: " . ($post->public_image_url ?? '(vazia)'));

        $result = $validator->validateSocialPostImage($post);

        // Update post
        $post->update([
            'image_status'           => $result->valid ? 'valid' : 'invalid',
            'image_validation_status'=> $result->valid ? 'valid' : 'invalid',
            'image_validation_error' => $result->valid ? null : $result->error,
        ]);

        $this->line("  HTTP Status : " . ($result->httpStatus ?? '—'));
        $this->line("  Content-Type: " . ($result->contentType ?? '—'));
        $this->line("  Dimensões   : " . ($result->width ? "{$result->width}x{$result->height}px" : '—'));
        $this->line("  Tamanho     : " . ($result->bytes ? round($result->bytes / 1024, 1) . ' KB' : '—'));

        if ($result->valid) {
            $this->info("  RESULTADO: VÁLIDA ✓");
        } else {
            $this->error("  RESULTADO: INVÁLIDA ✗");
            $this->error("  Erro: " . $result->error);
        }

        return $result->valid ? self::SUCCESS : self::FAILURE;
    }
}
