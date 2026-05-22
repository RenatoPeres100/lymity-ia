<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientKnowledgeBase;
use Illuminate\Database\Seeder;

class ClientKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::where('name', 'Cliente Demonstração')->first();
        if (!$client) return;

        ClientKnowledgeBase::updateOrCreate(
            ['client_id' => $client->id, 'title' => 'Informações Comerciais'],
            [
                'content' => "Horário de funcionamento: Segunda a Sexta, 9h às 20h. Sábados, 9h às 15h.\n\nEndereço: Rua Exemplo, 123 - Centro - São Paulo/SP\n\nContatos:\n- WhatsApp: (11) 99999-0000\n- E-mail: contato@clinicademo.local\n- Instagram: @clientedemo\n\nFormas de pagamento: Cartão de crédito (até 12x), débito, PIX e dinheiro.\n\nPolítica de cancelamento: Cancelamentos com menos de 24h de antecedência geram multa de 30% do valor da sessão.\n\nDiferenciais:\n- Avaliação gratuita para todos os novos clientes\n- Pacotes com desconto progressivo\n- Programa de fidelidade com cashback\n- Atendimento individual — sem sessões compartilhadas",
                'source' => 'Briefing do cliente',
                'status' => 'active',
            ]
        );

        ClientKnowledgeBase::updateOrCreate(
            ['client_id' => $client->id, 'title' => 'Perguntas Frequentes (FAQ)'],
            [
                'content' => "P: Os tratamentos são seguros?\nR: Sim. Todos os procedimentos são realizados por profissionais certificados com equipamentos aprovados pela ANVISA.\n\nP: Quantas sessões são necessárias?\nR: Depende do tratamento e objetivo. A média é de 8 a 12 sessões para resultados visíveis. Nossa especialista indica o protocolo ideal na avaliação gratuita.\n\nP: Os resultados são permanentes?\nR: Os resultados são duradouros quando combinados com hábitos saudáveis. Sessões de manutenção ajudam a manter os resultados.\n\nP: Quem pode fazer os tratamentos?\nR: A maioria dos tratamentos é indicada para adultos saudáveis. Grávidas, pessoas com marcapassos e algumas condições médicas não podem realizar certos procedimentos. A avaliação inicial identifica contraindicações.\n\nP: Dói?\nR: A maioria dos procedimentos é indolor ou causa leve desconforto. Nossa equipe sempre prioriza o conforto do cliente.",
                'source'  => 'Entrevista com a proprietária',
                'status'  => 'active',
            ]
        );
    }
}
