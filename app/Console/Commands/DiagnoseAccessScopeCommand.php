<?php

namespace App\Console\Commands;

use App\Models\ApprovalRequest;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use App\Services\Auth\AccessScopeService;
use Illuminate\Console\Command;

class DiagnoseAccessScopeCommand extends Command
{
    protected $signature   = 'access:diagnose-scope';
    protected $description = 'Diagnose real access scope isolation between companies and clients';

    private array $errors = [];

    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════════╗');
        $this->info('║    ACCESS SCOPE ISOLATION DIAGNOSIS        ║');
        $this->info('╚════════════════════════════════════════════╝');
        $this->newLine();

        // 1. Check AccessScopeService
        $this->checkService();

        // 2. Check models have scopeVisibleTo
        $this->checkModels();

        // 3. Check middlewares registered
        $this->checkMiddlewares();

        // 4. Check policies registered
        $this->checkPolicies();

        // 5. Check test data exists
        if (!$this->checkTestData()) {
            $this->warn('Test data not found. Run: php artisan db:seed --class=AccessScopeTestSeeder');
            $this->displayResult();
            return 1;
        }

        // 6. Run isolation tests
        $this->runIsolationTests();

        return $this->displayResult();
    }

    private function checkService(): void
    {
        $this->line('── Service Check ───────────────────────────────');
        try {
            $svc = app(AccessScopeService::class);
            $this->ok('AccessScopeService exists and resolves');
        } catch (\Throwable $e) {
            $this->recordFail('AccessScopeService failed to resolve: ' . $e->getMessage());
        }
    }

    private function checkModels(): void
    {
        $this->newLine();
        $this->line('── Model Scope Check ───────────────────────────');

        $models = [
            \App\Models\Company::class,
            \App\Models\Client::class,
            \App\Models\User::class,
            \App\Models\BlogPost::class,
            \App\Models\ContentBrief::class,
            \App\Models\SocialPost::class,
            \App\Models\ApprovalRequest::class,
            \App\Models\ActivityLog::class,
            \App\Models\AiTask::class,
            \App\Models\AgentRoutine::class,
            \App\Models\ExternalFile::class,
            \App\Models\AgencyBrandContext::class,
        ];

        foreach ($models as $model) {
            $short = class_basename($model);
            if (method_exists($model, 'scopeVisibleTo')) {
                $this->ok("{$short}::scopeVisibleTo exists");
            } else {
                $this->recordFail("{$short}::scopeVisibleTo MISSING");
            }
        }
    }

    private function checkMiddlewares(): void
    {
        $this->newLine();
        $this->line('── Middleware Check ────────────────────────────');

        $middlewares = [
            'EnsureAdminPanelAccess'  => \App\Http\Middleware\EnsureAdminPanelAccess::class,
            'EnsureClientPanelAccess' => \App\Http\Middleware\EnsureClientPanelAccess::class,
            'EnsureCanAccessCompany'  => \App\Http\Middleware\EnsureCanAccessCompany::class,
            'EnsureCanAccessClient'   => \App\Http\Middleware\EnsureCanAccessClient::class,
            'EnsureUserIsActive'      => \App\Http\Middleware\EnsureUserIsActive::class,
        ];

        foreach ($middlewares as $name => $class) {
            if (class_exists($class)) {
                $this->ok("{$name} class exists");
            } else {
                $this->recordFail("{$name} class MISSING");
            }
        }
    }

    private function checkPolicies(): void
    {
        $this->newLine();
        $this->line('── Policy Check ────────────────────────────────');

        $policies = [
            'UserPolicy'            => \App\Policies\UserPolicy::class,
            'ClientPolicy'          => \App\Policies\ClientPolicy::class,
            'BlogPostPolicy'        => \App\Policies\BlogPostPolicy::class,
            'ContentBriefPolicy'    => \App\Policies\ContentBriefPolicy::class,
            'SocialPostPolicy'      => \App\Policies\SocialPostPolicy::class,
            'ApprovalRequestPolicy' => \App\Policies\ApprovalRequestPolicy::class,
            'ActivityLogPolicy'     => \App\Policies\ActivityLogPolicy::class,
            'AiTaskPolicy'          => \App\Policies\AiTaskPolicy::class,
            'AgentRoutinePolicy'    => \App\Policies\AgentRoutinePolicy::class,
            'ExternalFilePolicy'    => \App\Policies\ExternalFilePolicy::class,
            'BrandContextPolicy'    => \App\Policies\BrandContextPolicy::class,
        ];

        foreach ($policies as $name => $class) {
            if (class_exists($class)) {
                $this->ok("{$name} exists");
            } else {
                $this->recordFail("{$name} MISSING");
            }
        }
    }

    private function checkTestData(): bool
    {
        $this->newLine();
        $this->line('── Test Data Check ─────────────────────────────');

        $exists = true;

        $companyA = Company::where('name', 'Agência Lymity Teste A')->first();
        $companyB = Company::where('name', 'Agência Lymity Teste B')->first();

        if ($companyA) {
            $this->ok('Company A exists (id=' . $companyA->id . ')');
        } else {
            $this->recordFail('Company A NOT found');
            $exists = false;
        }

        if ($companyB) {
            $this->ok('Company B exists (id=' . $companyB->id . ')');
        } else {
            $this->recordFail('Company B NOT found');
            $exists = false;
        }

        $clientNames = ['Cliente A1', 'Cliente A2', 'Cliente B1'];
        foreach ($clientNames as $name) {
            if (Client::where('name', $name)->exists()) {
                $this->ok("Client '{$name}' exists");
            } else {
                $this->recordFail("Client '{$name}' NOT found");
                $exists = false;
            }
        }

        $userEmails = [
            'admin_global_test@lymity.local',
            'agencia_admin_a@lymity.local',
            'operador_a@lymity.local',
            'agencia_admin_b@lymity.local',
            'cliente_admin_a1@lymity.local',
            'cliente_admin_b1@lymity.local',
        ];

        foreach ($userEmails as $email) {
            if (User::where('email', $email)->exists()) {
                $this->ok("User '{$email}' exists");
            } else {
                $this->recordFail("User '{$email}' NOT found");
                $exists = false;
            }
        }

        return $exists;
    }

    private function runIsolationTests(): void
    {
        $this->newLine();
        $this->line('── Isolation Tests ─────────────────────────────');

        $adminGlobal = User::where('email', 'admin_global_test@lymity.local')->first();
        $agencyAdminA = User::where('email', 'agencia_admin_a@lymity.local')->first();
        $agencyAdminB = User::where('email', 'agencia_admin_b@lymity.local')->first();
        $clientAdminA1 = User::where('email', 'cliente_admin_a1@lymity.local')->first();
        $clientAdminB1 = User::where('email', 'cliente_admin_b1@lymity.local')->first();

        $clientA1 = Client::where('name', 'Cliente A1')->first();
        $clientA2 = Client::where('name', 'Cliente A2')->first();
        $clientB1 = Client::where('name', 'Cliente B1')->first();

        // Admin global sees all clients
        $allCount = Client::count();
        $adminCount = Client::visibleTo($adminGlobal)->count();
        if ($adminCount >= $allCount) {
            $this->ok("admin_global_test vê todos os clientes ({$adminCount})");
        } else {
            $this->recordFail("admin_global_test deveria ver {$allCount} clientes, viu {$adminCount}");
        }

        // Agency A sees A1 and A2, not B1
        $agencyAClients = Client::visibleTo($agencyAdminA)->pluck('name')->toArray();

        if (in_array('Cliente A1', $agencyAClients)) {
            $this->ok('agencia_admin_a vê Cliente A1');
        } else {
            $this->recordFail('agencia_admin_a NÃO vê Cliente A1 (deveria ver)');
        }

        if (in_array('Cliente A2', $agencyAClients)) {
            $this->ok('agencia_admin_a vê Cliente A2');
        } else {
            $this->recordFail('agencia_admin_a NÃO vê Cliente A2 (deveria ver)');
        }

        if (!in_array('Cliente B1', $agencyAClients)) {
            $this->ok('agencia_admin_a NÃO vê Cliente B1 (correto — cross-company bloqueado)');
        } else {
            $this->recordFail('agencia_admin_a VÊ Cliente B1 (VIOLAÇÃO DE ESCOPO!)');
        }

        // Agency B sees B1, not A1/A2
        $agencyBClients = Client::visibleTo($agencyAdminB)->pluck('name')->toArray();

        if (in_array('Cliente B1', $agencyBClients)) {
            $this->ok('agencia_admin_b vê Cliente B1');
        } else {
            $this->recordFail('agencia_admin_b NÃO vê Cliente B1 (deveria ver)');
        }

        if (!in_array('Cliente A1', $agencyBClients) && !in_array('Cliente A2', $agencyBClients)) {
            $this->ok('agencia_admin_b NÃO vê Clientes A1/A2 (correto — cross-company bloqueado)');
        } else {
            $this->recordFail('agencia_admin_b VÊ Clientes de Company A (VIOLAÇÃO DE ESCOPO!)');
        }

        // Client A1 sees only A1
        $clientA1Clients = Client::visibleTo($clientAdminA1)->pluck('name')->toArray();

        if (in_array('Cliente A1', $clientA1Clients) && !in_array('Cliente A2', $clientA1Clients) && !in_array('Cliente B1', $clientA1Clients)) {
            $this->ok('cliente_admin_a1 vê apenas Cliente A1 (correto)');
        } else {
            $this->recordFail('cliente_admin_a1 tem acesso incorreto a outros clientes: ' . implode(', ', $clientA1Clients));
        }

        // Client B1 sees only B1
        $clientB1Clients = Client::visibleTo($clientAdminB1)->pluck('name')->toArray();

        if (in_array('Cliente B1', $clientB1Clients) && !in_array('Cliente A1', $clientB1Clients)) {
            $this->ok('cliente_admin_b1 vê apenas Cliente B1 (correto)');
        } else {
            $this->recordFail('cliente_admin_b1 tem acesso incorreto a outros clientes: ' . implode(', ', $clientB1Clients));
        }

        // ApprovalRequest isolation
        $approvalA1 = ApprovalRequest::where('title', '[TESTE] Aprovação Cliente A1')->first();
        $approvalB1 = ApprovalRequest::where('title', '[TESTE] Aprovação Cliente B1')->first();

        if ($approvalA1 && $approvalB1) {
            $clientAdminA1ApprovalsIds = ApprovalRequest::visibleTo($clientAdminA1)->pluck('id')->toArray();

            if (in_array($approvalA1->id, $clientAdminA1ApprovalsIds)) {
                $this->ok('cliente_admin_a1 vê aprovação A1');
            } else {
                $this->recordFail('cliente_admin_a1 NÃO vê aprovação A1 (deveria ver)');
            }

            if (!in_array($approvalB1->id, $clientAdminA1ApprovalsIds)) {
                $this->ok('cliente_admin_a1 NÃO vê aprovação B1 (correto — cross-client bloqueado)');
            } else {
                $this->recordFail('cliente_admin_a1 VÊ aprovação B1 (VIOLAÇÃO DE ESCOPO!)');
            }
        }

        // User model isolation
        $userA1   = User::where('email', 'cliente_admin_a1@lymity.local')->first();
        $adminAVisible = User::visibleTo($agencyAdminA)->pluck('email')->toArray();

        if (!in_array('agencia_admin_b@lymity.local', $adminAVisible)) {
            $this->ok('agencia_admin_a NÃO vê usuários da Company B (correto)');
        } else {
            $this->recordFail('agencia_admin_a VÊ usuários da Company B (VIOLAÇÃO!)');
        }

        // Admin global sees all users
        $adminGlobalUserCount = User::visibleTo($adminGlobal)->count();
        $totalUsers           = User::count();

        if ($adminGlobalUserCount >= $totalUsers) {
            $this->ok("admin_global_test vê todos os usuários ({$adminGlobalUserCount})");
        } else {
            $this->recordFail("admin_global_test deveria ver {$totalUsers} usuários, viu {$adminGlobalUserCount}");
        }
    }

    private function ok(string $message): void
    {
        $this->line("  <fg=green>[OK]</> {$message}");
    }

    private function recordFail(string $message): void
    {
        $this->errors[] = $message;
        $this->line("  <fg=red>[ERROR]</> {$message}");
    }

    private function displayResult(): int
    {
        $this->newLine();
        $this->line('════════════════════════════════════════════════');

        if (empty($this->errors)) {
            $this->info('ACCESS_SCOPE_VALIDATION=OK');
            $this->info('Todos os testes de isolamento passaram com sucesso.');
            return 0;
        }

        $this->error('ACCESS_SCOPE_VALIDATION=ERROR');
        $this->error(count($this->errors) . ' erro(s) encontrado(s):');
        foreach ($this->errors as $err) {
            $this->error("  • {$err}");
        }
        return 1;
    }
}
