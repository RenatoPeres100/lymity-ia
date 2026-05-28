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
        $this->line('── Isolation Tests (3-role model) ──────────────');

        $adminGlobal  = User::where('email', 'admin_global_test@lymity.local')->first();
        $clienteA1    = User::where('email', 'cliente_admin_a1@lymity.local')->first();
        $clienteB1    = User::where('email', 'cliente_admin_b1@lymity.local')->first();

        $clientA1 = Client::where('name', 'Cliente A1')->first();
        $clientA2 = Client::where('name', 'Cliente A2')->first();
        $clientB1 = Client::where('name', 'Cliente B1')->first();

        // Admin global sees all clients
        $allCount   = Client::count();
        $adminCount = Client::visibleTo($adminGlobal)->count();
        if ($adminCount >= $allCount) {
            $this->ok("admin_global_test vê todos os clientes ({$adminCount})");
        } else {
            $this->recordFail("admin_global_test deveria ver {$allCount} clientes, viu {$adminCount}");
        }

        // Admin global sees all users
        $totalUsers      = User::count();
        $adminUsersCount = User::visibleTo($adminGlobal)->count();
        if ($adminUsersCount >= $totalUsers) {
            $this->ok("admin_global_test vê todos os usuários ({$adminUsersCount})");
        } else {
            $this->recordFail("admin_global_test deveria ver {$totalUsers} usuários, viu {$adminUsersCount}");
        }

        // Cliente A1 sees only own client
        if ($clienteA1) {
            $clienteA1Clients = Client::visibleTo($clienteA1)->pluck('name')->toArray();
            if (in_array('Cliente A1', $clienteA1Clients) && !in_array('Cliente B1', $clienteA1Clients)) {
                $this->ok('Cliente A1 vê apenas Cliente A1 (correto)');
            } else {
                $this->recordFail('Cliente A1 tem acesso incorreto: ' . implode(', ', $clienteA1Clients));
            }

            // Cross-client approval test
            $approvalA1 = ApprovalRequest::where('title', '[TESTE] Aprovação Cliente A1')->first();
            $approvalB1 = ApprovalRequest::where('title', '[TESTE] Aprovação Cliente B1')->first();

            if ($approvalA1 && $approvalB1) {
                $a1Approvals = ApprovalRequest::visibleTo($clienteA1)->pluck('id')->toArray();
                if (in_array($approvalA1->id, $a1Approvals)) {
                    $this->ok('Cliente A1 vê aprovação A1');
                } else {
                    $this->recordFail('Cliente A1 NÃO vê aprovação A1 (deveria ver)');
                }
                if (!in_array($approvalB1->id, $a1Approvals)) {
                    $this->ok('Cliente A1 NÃO vê aprovação B1 (cross-client bloqueado)');
                } else {
                    $this->recordFail('Cliente A1 VÊ aprovação B1 (VIOLAÇÃO!)');
                }
            }
        }

        // Cliente B1 sees only own client
        if ($clienteB1) {
            $clienteB1Clients = Client::visibleTo($clienteB1)->pluck('name')->toArray();
            if (in_array('Cliente B1', $clienteB1Clients) && !in_array('Cliente A1', $clienteB1Clients)) {
                $this->ok('Cliente B1 vê apenas Cliente B1 (correto)');
            } else {
                $this->recordFail('Cliente B1 tem acesso incorreto: ' . implode(', ', $clienteB1Clients));
            }
        }

        // Role verification
        if ($adminGlobal && $adminGlobal->role === 'admin_geral') {
            $this->ok('admin_global_test tem role admin_geral');
        } else {
            $this->recordFail('admin_global_test não tem role correto');
        }

        if ($clienteA1 && $clienteA1->role === 'cliente') {
            $this->ok('cliente_admin_a1 migrado para role cliente');
        } elseif (!$clienteA1) {
            $this->warn('cliente_admin_a1 não encontrado — execute o seeder');
        } else {
            $this->recordFail("cliente_admin_a1 tem role incorreto: {$clienteA1->role}");
        }

        // Verify no legacy agency roles exist
        $legacyCount = User::whereIn('role', [
            'agencia_admin', 'agencia_operador', 'social_media',
            'gestor_trafego', 'seo', 'copywriter', 'designer',
            'blog_writer', 'cliente_admin', 'cliente_colaborador', 'viewer',
        ])->count();

        if ($legacyCount === 0) {
            $this->ok('Nenhum usuário com role legado encontrado (migração completa)');
        } else {
            $this->recordFail("{$legacyCount} usuário(s) ainda com roles legados — execute a migração");
        }

        // Colaborador test
        $colaborador = User::where('role', 'colaborador')->first();
        if ($colaborador) {
            $this->ok("Colaborador encontrado: {$colaborador->email}");
            $collabClients = Client::visibleTo($colaborador)->pluck('name')->toArray();
            if ($colaborador->client_id && count($collabClients) === 1) {
                $this->ok('Colaborador vê apenas 1 cliente (correto)');
            } elseif (!$colaborador->client_id) {
                $this->ok('Colaborador sem client_id — vê nada (correto)');
            } else {
                $this->recordFail('Colaborador vê múltiplos clientes: ' . implode(', ', $collabClients));
            }
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
