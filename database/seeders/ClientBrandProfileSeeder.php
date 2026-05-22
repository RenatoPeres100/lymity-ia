<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientBrandProfile;
use Illuminate\Database\Seeder;

class ClientBrandProfileSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::where('name', 'Cliente Demonstração')->first();
        if (!$client) return;

        ClientBrandProfile::updateOrCreate(
            ['client_id' => $client->id],
            [
                'tone_of_voice'    => 'Profissional, elegante e acolhedor. Transmitimos confiança e resultados com linguagem clara, sem exageros. Usamos o "você" para proximidade, evitamos termos muito técnicos.',
                'target_audience'  => 'Mulheres e homens entre 25 e 55 anos, classe B/C, residentes em centros urbanos. Buscam bem-estar, autoestima e transformação pessoal. Prezam por atendimento de qualidade e resultados reais.',
                'main_offer'       => 'Tratamentos estéticos avançados para emagrecimento, rejuvenescimento e bem-estar. Combina tecnologia de ponta com atendimento personalizado para resultados duradouros.',
                'objections'       => "Preço alto demais.\nNão vejo resultado rápido.\nTenho medo de procedimentos estéticos.\nJá tentei outras coisas e não funcionou.",
                'competitors'      => "Clínica BellaVita (forte em redes sociais)\nEstética & Cia (preço mais baixo)\nSpa ModernBody (foco em relaxamento)",
                'visual_style'     => 'Cores: rose gold (#C9956C), branco (#FFFFFF) e cinza claro (#F5F5F5). Fontes elegantes tipo Playfair Display + Lato. Imagens de alta qualidade com mulheres e ambientes clean. Evitar fotos muito clínicas.',
                'forbidden_terms'  => "barato\nimproviso\namador\ncaseiro\ngordura\ncellulite\ngordo",
                'preferred_terms'  => "transformação\nresultado\nbem-estar\nelegância\nconfiança\nautoestima\nrenovação",
                'cta_examples'             => "Agende sua avaliação gratuita\nTransforme sua autoestima hoje\nConheça nossos resultados\nFale com uma especialista",
                'notes'            => 'Cliente muito atento à qualidade visual. Prefere comunicação por WhatsApp. Aprovações geralmente ocorrem em até 48h.',
            ]
        );
    }
}
