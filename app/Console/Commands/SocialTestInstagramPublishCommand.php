<?php

namespace App\Console\Commands;

use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\User;
use App\Services\Instagram\InstagramPublishingService;
use App\Services\Social\SocialPostService;
use Illuminate\Console\Command;

class SocialTestInstagramPublishCommand extends Command
{
    protected $signature   = 'social:test-instagram-publish
                                {post_id? : ID do post a publicar (opcional)}
                                {--use-placeholder : Usa imagem placeholder se Gemini indisponível}';
    protected $description = 'Publica um post de teste no Instagram @lymity.ia para validar o pipeline real.';

    public function handle(InstagramPublishingService $publisher, SocialPostService $postService): int
    {
        $this->info('=== SOCIAL TEST INSTAGRAM PUBLISH ===');

        if (!config('meta.instagram_publishing_enabled', false)) {
            $this->error('INSTAGRAM_PUBLISHING_ENABLED=false. Configure no .env e reconecte o canal.');
            return self::FAILURE;
        }

        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if (!$channel) {
            $this->error('Canal Instagram @lymity.ia não encontrado. Configure em /admin/social/instagram.');
            return self::FAILURE;
        }

        if (!$channel->isConnected() || !$channel->hasValidToken()) {
            $this->error('Canal Instagram não conectado ou token inválido.');
            return self::FAILURE;
        }

        $admin = User::where('email', 'admin@lymity.local')->first()
            ?? User::whereHas('roles', fn($q) => $q->where('name', 'admin_geral'))->first()
            ?? User::first();

        // Resolve post
        $postId = $this->argument('post_id');
        if ($postId) {
            $post = SocialPost::find($postId);
            if (!$post) {
                $this->error("Post #{$postId} não encontrado.");
                return self::FAILURE;
            }
        } else {
            // Create a minimal approved test post
            $post = $this->createApprovedTestPost($postService, $admin);
        }

        $this->info("  Post: #{$post->id} — {$post->title}");
        $this->info("  Status: {$post->status}");
        $this->info("  Imagem: " . ($post->public_image_url ?? 'none'));
        $this->info("  Validação: " . ($post->image_validation_status ?? 'none'));

        // Check readiness
        if (!$post->canBePublished()) {
            $this->error('Post não está pronto para publicação. Verifique:');
            $this->line("  - Status deve ser approved/scheduled. Atual: {$post->status}");
            $this->line("  - approved_by preenchido: " . ($post->approved_by ? 'sim' : 'não'));
            $this->line("  - image_validation_status=valid. Atual: " . ($post->image_validation_status ?? 'none'));
            $this->line("  - public_image_url HTTPS. Atual: " . ($post->public_image_url ?? 'none'));
            $this->line("  - main_caption preenchido: " . (!empty(trim($post->main_caption ?? '')) ? 'sim' : 'não'));
            return self::FAILURE;
        }

        if (!$this->confirm("Publicar Post #{$post->id} no Instagram @lymity.ia agora?")) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        try {
            $this->info('  Publicando...');
            $result = $publisher->publishSingleImage($channel, $post);
            $post->refresh();

            $this->info('');
            $this->info('  PUBLICADO COM SUCESSO!');
            $this->info("  STATUS    : {$post->status}");
            $this->info("  MEDIA_ID  : " . ($post->external_post_id ?? '—'));
            $this->info("  PERMALINK : " . ($post->permalink ?? '—'));
            $this->info("  CONTAINER : " . ($post->instagram_container_id ?? '—'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $e->getMessage());
            $this->error("  FALHOU: {$safe}");
            return self::FAILURE;
        }
    }

    private function createApprovedTestPost(SocialPostService $postService, User $admin): SocialPost
    {
        $this->warn('Nenhum post_id informado. Criando post de teste aprovado...');

        $post = $postService->createDraft([
            'title'           => 'Publicação de Teste — Lymity IA [' . now()->format('d/m/Y H:i') . ']',
            'objective'       => 'authority',
            'content_type'    => 'feed',
            'creative_format' => 'feed_image',
            'main_caption'    => "🚀 Lymity IA — Transformando negócios com inteligência artificial.\n\nAutomação inteligente, resultados reais.\n\nSaiba mais: ia.lymity.com.br",
            'hashtags'        => '#LymityIA #InteligenciaArtificial #MarketingDigital #Automacao #AgenciaDigital',
            'public_image_url'=> 'https://ia.lymity.com.br/storage/social/generated/test/placeholder.jpg',
            'image_validation_status' => null,
            'requires_approval' => false,
        ], $admin);

        // Use a real public placeholder if flag set
        if ($this->option('use-placeholder')) {
            $post->update([
                'public_image_url'        => 'https://placehold.co/1080x1080/0f172a/ffffff.jpg?text=Lymity+IA',
                'image_validation_status' => 'valid',
                'image_status'            => 'valid',
            ]);
        }

        // Approve it
        $post->update([
            'status'      => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'scheduled_at'=> now()->subMinute(),
        ]);

        return $post->fresh();
    }
}
