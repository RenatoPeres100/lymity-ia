<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdAccount;
use App\Models\Client;
use App\Models\Company;
use App\Services\Ads\AdsPlanningService;
use Illuminate\Http\Request;

class AdAccountController extends Controller
{
    public function __construct(protected AdsPlanningService $service) {}

    public function index()
    {
        $accounts = AdAccount::with(['client', 'company'])->latest()->paginate(20);
        return view('admin.ads.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $clients   = Client::where('status', 'active')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        return view('admin.ads.accounts.create', compact('clients', 'companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'           => 'nullable|exists:clients,id',
            'company_id'          => 'nullable|exists:companies,id',
            'platform'            => 'required|in:google_ads,meta_ads,linkedin_ads,tiktok_ads',
            'account_name'        => 'required|string|max:255',
            'external_account_id' => 'nullable|string|max:255',
            'status'              => 'required|in:active,paused,disconnected',
        ]);

        $this->service->createAccount($data);

        return redirect()->route('admin.ads.accounts.index')->with('success', 'Conta de anúncio criada com sucesso.');
    }

    public function edit(AdAccount $adAccount)
    {
        $clients   = Client::where('status', 'active')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        return view('admin.ads.accounts.edit', compact('adAccount', 'clients', 'companies'));
    }

    public function update(Request $request, AdAccount $adAccount)
    {
        $data = $request->validate([
            'client_id'           => 'nullable|exists:clients,id',
            'company_id'          => 'nullable|exists:companies,id',
            'platform'            => 'required|in:google_ads,meta_ads,linkedin_ads,tiktok_ads',
            'account_name'        => 'required|string|max:255',
            'external_account_id' => 'nullable|string|max:255',
            'status'              => 'required|in:active,paused,disconnected',
        ]);

        $adAccount->update($data);

        return redirect()->route('admin.ads.accounts.index')->with('success', 'Conta atualizada com sucesso.');
    }
}
