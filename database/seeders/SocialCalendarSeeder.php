<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\SocialCalendar;
use Illuminate\Database\Seeder;

class SocialCalendarSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();
        $now    = now();

        $calendars = [
            [
                'month'            => $now->month,
                'year'             => $now->year,
                'client_id'        => null,
                'company_id'       => null,
                'strategy_summary' => 'Calendário da agência para ' . $now->translatedFormat('F Y') . '. Foco em autoridade e brand awareness.',
                'status'           => 'active',
            ],
            [
                'month'            => $now->month,
                'year'             => $now->year,
                'client_id'        => $client?->id,
                'company_id'       => null,
                'strategy_summary' => 'Calendário do cliente ' . ($client?->name ?? 'Demo') . ' — ' . $now->translatedFormat('F Y') . '. Engajamento e geração de leads.',
                'status'           => 'draft',
            ],
        ];

        foreach ($calendars as $data) {
            SocialCalendar::firstOrCreate(
                [
                    'month'     => $data['month'],
                    'year'      => $data['year'],
                    'client_id' => $data['client_id'],
                ],
                $data
            );
        }
    }
}
