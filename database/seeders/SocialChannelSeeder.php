<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\SocialChannel;
use Illuminate\Database\Seeder;

class SocialChannelSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        $channels = [
            ['platform' => 'instagram', 'account_name' => 'Instagram Lymity IA',   'client_id' => null,         'status' => 'active'],
            ['platform' => 'linkedin',  'account_name' => 'LinkedIn Lymity IA',    'client_id' => null,         'status' => 'active'],
            ['platform' => 'instagram', 'account_name' => 'Instagram Cliente Demo','client_id' => $client?->id, 'status' => 'active'],
            ['platform' => 'facebook',  'account_name' => 'Facebook Cliente Demo', 'client_id' => $client?->id, 'status' => 'paused'],
        ];

        foreach ($channels as $data) {
            SocialChannel::firstOrCreate(
                ['platform' => $data['platform'], 'account_name' => $data['account_name']],
                array_merge($data, [
                    'access_token'  => null,
                    'refresh_token' => null,
                ])
            );
        }
    }
}
