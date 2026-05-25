<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\StorageIntegration;
use Illuminate\Database\Seeder;

class StorageIntegrationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $client  = Client::first();

        if ($company && !StorageIntegration::where('company_id', $company->id)->where('provider', 'local')->exists()) {
            StorageIntegration::create([
                'provider'   => 'local',
                'company_id' => $company->id,
                'status'     => 'active',
                'metadata'   => ['description' => 'Armazenamento local da agência Lymity'],
            ]);
        }

        if ($client && !StorageIntegration::where('client_id', $client->id)->where('provider', 'local')->exists()) {
            StorageIntegration::create([
                'provider'  => 'local',
                'client_id' => $client->id,
                'status'    => 'active',
                'metadata'  => ['description' => 'Armazenamento local do cliente'],
            ]);
        }
    }
}
