<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientWebsitePage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientWebsitePageSeeder extends Seeder
{
    public function run(): void
    {
        $client  = Client::where('name', 'Cliente Demonstração')->first();
        if (!$client) return;

        $website = $client->websites()->latest()->first();
        if (!$website) return;

        $creator = User::where('user_type', 'agency')->first();

        ClientWebsitePage::updateOrCreate(
            ['client_website_id' => $website->id, 'slug' => 'home'],
            [
                'title'            => 'Página Inicial',
                'page_type'        => 'home',
                'status'           => 'pending_approval',
                'content'          => "# Bem-vinda à Clínica Demonstração\n\nSua jornada de transformação começa aqui. Oferecemos os tratamentos estéticos mais modernos com tecnologia de ponta e profissionais altamente qualificados.\n\n## Por que nos escolher?\n\n- **Tecnologia avançada**: Equipamentos de última geração\n- **Resultados comprovados**: Mais de 2.000 clientes satisfeitos\n- **Atendimento personalizado**: Avaliação individual para cada cliente\n- **Ambiente acolhedor**: Espaço pensado para o seu conforto\n\n## Nossos diferenciais\n\nCombinamos ciência estética com cuidado humano para entregarmos resultados reais e duradouros. Cada tratamento é personalizado de acordo com as suas necessidades.\n\n## Agende sua avaliação\n\nFaça uma avaliação gratuita e descubra os melhores tratamentos para você.",
                'seo_title'       => 'Clínica de Estética Demonstração — Transformação e Bem-estar',
                'seo_description' => 'Tratamentos estéticos avançados para emagrecimento, rejuvenescimento e bem-estar. Agende sua avaliação gratuita.',
                'created_by'       => $creator?->id,
            ]
        );

        ClientWebsitePage::updateOrCreate(
            ['client_website_id' => $website->id, 'slug' => 'servicos'],
            [
                'title'      => 'Nossos Serviços',
                'page_type'  => 'services',
                'status'     => 'draft',
                'content'    => "# Nossos Serviços\n\nConheça nossa linha completa de tratamentos estéticos.\n\n## Emagrecimento\n\nTratamentos com ultrassom cavitacional, radiofrequência e criolipólise para eliminação de gordura localizada.\n\n## Rejuvenescimento\n\nMicroneedling, laser facial e peelings químicos para renovação celular e redução de rugas.\n\n## Corporal\n\nDrenagem linfática, massagem modeladora e tratamentos para celulite.\n\n## Como funciona?\n\n1. Agendamento de avaliação gratuita\n2. Avaliação personalizada com especialista\n3. Plano de tratamento exclusivo\n4. Sessões com acompanhamento contínuo",
                'seo_title' => 'Serviços — Clínica de Estética Demonstração',
                'created_by' => $creator?->id,
            ]
        );
    }
}
