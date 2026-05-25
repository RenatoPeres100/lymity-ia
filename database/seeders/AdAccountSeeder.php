<?php

namespace Database\Seeders;

use App\Models\AdAccount;
use App\Models\Client;
use Illuminate\Database\Seeder;

class AdAccountSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        AdAccount::updateOrCreate(
            ['platform' => 'google_ads', 'account_name' => 'Conta Google Ads Demo'],
            [
                'client_id'    => $client?->id,
                'status'       => 'active',
                'access_token' => null,
                'refresh_token' => null,
                'metadata'     => ['mode' => 'sandbox', 'demo' => true],
            ]
        );

        AdAccount::updateOrCreate(
            ['platform' => 'meta_ads', 'account_name' => 'Conta Meta Ads Demo'],
            [
                'client_id'    => $client?->id,
                'status'       => 'active',
                'access_token' => null,
                'refresh_token' => null,
                'metadata'     => ['mode' => 'sandbox', 'demo' => true],
            ]
        );
    }
}
