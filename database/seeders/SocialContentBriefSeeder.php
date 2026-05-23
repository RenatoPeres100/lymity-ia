<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\SocialContentBrief;
use Illuminate\Support\Facades\Auth;

class SocialContentBriefSeeder extends \Illuminate\Database\Seeder
{
    public function run(): void
    {
        $client = Client::first();

        $briefs = [
            [
                'title'        => 'Conteúdo de Autoridade — IA e Marketing Digital',
                'objective'    => 'authority',
                'content_type' => 'feed',
                'tone'         => 'Profissional e educativo',
                'instructions' => 'Criar posts que demonstrem expertise em IA aplicada ao marketing. Foco em dados e resultados reais.',
                'client_id'    => null,
                'status'       => 'in_progress',
            ],
            [
                'title'        => 'Campanha de Engajamento — Cliente Demo',
                'objective'    => 'engagement',
                'content_type' => 'carousel',
                'tone'         => 'Descontraído e próximo',
                'instructions' => 'Posts interativos com perguntas para a audiência. Usar emojis moderadamente.',
                'client_id'    => $client?->id,
                'status'       => 'draft',
            ],
        ];

        foreach ($briefs as $data) {
            SocialContentBrief::firstOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
