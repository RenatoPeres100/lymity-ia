<?php

namespace App\Services\Seo;

use App\Models\SeoAudit;
use App\Models\SeoRecommendation;
use App\Models\User;

class SeoAuditService
{
    public function createAudit(array $data): SeoAudit
    {
        return SeoAudit::create([
            'client_id'   => $data['client_id'] ?? null,
            'company_id'  => $data['company_id'] ?? null,
            'website_url' => $data['website_url'],
            'title'       => $data['title'],
            'summary'     => $data['summary'] ?? null,
            'score'       => $data['score'] ?? null,
            'status'      => $data['status'] ?? 'draft',
        ]);
    }

    public function generateMockRecommendations(SeoAudit $audit): array
    {
        $audit->recommendations()->delete();

        $items = [
            [
                'title'       => 'Otimizar meta descriptions das páginas principais',
                'description' => 'Defina meta descriptions únicas, com entre 150-160 caracteres e chamada para ação clara em cada página relevante do site.',
                'priority'    => 'high',
            ],
            [
                'title'       => 'Melhorar velocidade de carregamento (Core Web Vitals)',
                'description' => 'Otimize imagens (WebP), ative lazy loading, minifique CSS/JS e considere um CDN para melhorar LCP e FID.',
                'priority'    => 'critical',
            ],
            [
                'title'       => 'Corrigir estrutura de headings (H1, H2, H3)',
                'description' => 'Verifique se todas as páginas têm exatamente um H1 e se a hierarquia de headings está correta e consistente.',
                'priority'    => 'medium',
            ],
            [
                'title'       => 'Implementar dados estruturados (Schema.org)',
                'description' => 'Adicione markup de schema para organização, artigos, FAQ e breadcrumbs para melhorar rich snippets nos resultados de busca.',
                'priority'    => 'medium',
            ],
            [
                'title'       => 'Criar estratégia de link building',
                'description' => 'Desenvolva um plano de aquisição de backlinks através de guest posts, parcerias e relações públicas digitais.',
                'priority'    => 'low',
            ],
        ];

        $created = [];
        foreach ($items as $item) {
            $created[] = SeoRecommendation::create([
                'seo_audit_id' => $audit->id,
                'title'        => $item['title'],
                'description'  => $item['description'],
                'priority'     => $item['priority'],
                'status'       => 'pending',
            ]);
        }

        $audit->update(['status' => 'completed', 'score' => 68]);

        return $created;
    }
}
