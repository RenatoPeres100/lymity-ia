<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Services\Instagram\InstagramPublishingService;
use Illuminate\Http\Request;

class SocialInstagramPublishController extends Controller
{
    public function publishNow(Request $request, SocialPost $socialPost): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Only admin_geral / agencia_admin
        abort_unless(
            in_array($user->role ?? $user->roles?->first()?->name ?? '', ['admin_geral', 'agencia_admin', 'admin']),
            403,
            'Apenas admin_geral ou agencia_admin podem publicar manualmente.'
        );

        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        // Pre-flight checks with friendly errors
        if (!$channel) {
            return back()->withErrors(['instagram' => 'Canal Instagram @lymity.ia não configurado. Conecte em /admin/social/instagram.']);
        }

        if (!config('meta.instagram_publishing_enabled', false)) {
            return back()->withErrors(['instagram' => 'Publicação desabilitada (INSTAGRAM_PUBLISHING_ENABLED=false). Ative no .env após validar a conexão.']);
        }

        if (!$channel->isConnected() || !$channel->hasValidToken()) {
            return back()->withErrors(['instagram' => 'Canal Instagram não está conectado ou token expirado. Reconecte em /admin/social/instagram.']);
        }

        if (!in_array($socialPost->status, ['approved', 'scheduled'])) {
            return back()->withErrors(['instagram' => "Post no status '{$socialPost->status_label}' não pode ser publicado. Aprovação e agendamento são obrigatórios."]);
        }

        if ($socialPost->requires_approval && !$socialPost->isApprovedForPublishing()) {
            return back()->withErrors(['instagram' => 'Post requer aprovação formal mas ainda não foi aprovado.']);
        }

        if (!$socialPost->hasPublicImage()) {
            return back()->withErrors(['instagram' => 'URL de imagem pública (HTTPS) ausente ou inválida. Adicione public_image_url antes de publicar.']);
        }

        $publisher = app(InstagramPublishingService::class);

        try {
            $publisher->publishSingleImage($channel, $socialPost);
            $socialPost->refresh();

            $this->log('instagram_publish_success', $user, [
                'post_id'         => $socialPost->id,
                'channel_id'      => $channel->id,
                'external_post_id'=> $socialPost->external_post_id,
            ]);

            return redirect()->route('admin.social.posts.show', $socialPost)
                ->with('success', 'Post publicado com sucesso no Instagram! ID externo: ' . ($socialPost->external_post_id ?? 'N/D'));

        } catch (\Throwable $e) {
            $publisher->handleError($e, $channel, $socialPost);

            $this->log('instagram_publish_failed', $user, [
                'post_id'    => $socialPost->id,
                'channel_id' => $channel->id,
                'error'      => $e->getMessage(),
            ]);

            return redirect()->route('admin.social.posts.show', $socialPost)
                ->withErrors(['instagram' => 'Falha ao publicar: ' . $e->getMessage()]);
        }
    }

    private function log(string $action, $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'instagram',
                'level'       => str_contains($action, 'fail') ? 'error' : 'info',
                'description' => "Instagram manual publish: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (\Throwable) {}
    }
}
