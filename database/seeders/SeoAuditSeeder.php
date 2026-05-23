<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\SeoAudit;
use App\Models\SeoRecommendation;
use App\Services\Seo\SeoAuditService;
use Illuminate\Database\Seeder;

class SeoAuditSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        $auditData = [
            [
                'title'       => 'Auditoria SEO — Lymity IA (lymity.com.br)',
                'website_url' => 'https://lymity.com.br',
                'summary'     => 'Auditoria inicial do site institucional da Lymity IA. Identificadas oportunidades de melhoria em velocidade, meta dados e estrutura de headings.',
                'score'       => 72,
                'status'      => 'completed',
                'client_id'   => null,
            ],
            [
                'title'       => 'Auditoria SEO — Site do Cliente Demo',
                'website_url' => 'https://clientedemo.com.br',
                'summary'     => null,
                'score'       => null,
                'status'      => 'draft',
                'client_id'   => $client?->id,
            ],
        ];

        $service = new SeoAuditService();

        foreach ($auditData as $data) {
            $audit = SeoAudit::firstOrCreate(
                ['title' => $data['title'], 'client_id' => $data['client_id']],
                $data
            );

            if ($audit->wasRecentlyCreated && $audit->status === 'completed') {
                $service->generateMockRecommendations($audit);
            }
        }
    }
}
