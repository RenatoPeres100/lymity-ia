<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $leads = [
            [
                'name'    => 'Carlos Mendes',
                'email'   => 'carlos@empresa-demo.com.br',
                'phone'   => '(11) 99999-1234',
                'company' => 'Empresa Demo Ltda',
                'message' => 'Quero entender como a plataforma funciona e quais serviços estão disponíveis para e-commerce.',
                'source'  => 'website',
                'status'  => 'new',
            ],
            [
                'name'    => 'Ana Paula Costa',
                'email'   => 'ana@clinica-demo.com.br',
                'phone'   => '(21) 98888-5678',
                'company' => 'Clínica Demo',
                'message' => 'Tenho uma clínica de estética e quero crescer no digital. Vi vocês no Google e adorei a proposta.',
                'source'  => 'website',
                'status'  => 'contacted',
            ],
        ];

        foreach ($leads as $lead) {
            Lead::firstOrCreate(['email' => $lead['email']], $lead);
        }
    }
}
