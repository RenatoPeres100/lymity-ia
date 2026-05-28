<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class DiagnoseClientPermissionsCommand extends Command
{
    protected $signature   = 'permissions:diagnose-client {email : E-mail do usuário}';
    protected $description = 'Diagnóstico completo de permissões do painel cliente';

    private array $menuMap = [
        'client.dashboard.view'     => ['label' => 'Meu Painel',        'route' => 'client.dashboard'],
        'client.approvals.view'     => ['label' => 'Aprovações',         'route' => 'client.approvals.index'],
        'client.blog.view'          => ['label' => 'Blog',               'route' => 'client.blog.index'],
        'client.brand_context.view' => ['label' => 'Brand Context',      'route' => 'client.brand-context.index'],
        'client.routines.view'      => ['label' => 'Rotinas',            'route' => 'client.routines.index'],
        'client.ai_employees.view'  => ['label' => 'Funcionários IA',   'route' => 'client.ai-employees.index'],
        'client.ai_tasks.view'      => ['label' => 'Tarefas IA',         'route' => 'client.ai-tasks.index'],
        'client.ai_logs.view'       => ['label' => 'Logs IA',            'route' => 'client.ai-logs.index'],
        'client.files.view'         => ['label' => 'Arquivos',           'route' => 'client.files.index'],
        'client.users.view'         => ['label' => 'Usuários',           'route' => 'client.users.index'],
        'client.app.view'           => ['label' => 'App Mobile',         'route' => 'app.dashboard'],
    ];

    public function handle(): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuário não encontrado: {$email}");
            return 1;
        }

        $this->line('');
        $this->info('═══════════════════════════════════════════════════');
        $this->info("  DIAGNÓSTICO: {$user->email}");
        $this->info('═══════════════════════════════════════════════════');
        $this->line("  USER      = {$user->email}");
        $this->line("  ROLE      = {$user->role}");
        $this->line("  TYPE      = {$user->user_type}");
        $this->line("  CLIENT_ID = {$user->client_id}");
        $this->line("  STATUS    = {$user->status}");
        $this->line("  CAN_PANEL = " . ($user->canAccessClientPanel() ? 'yes' : 'no'));

        $perms = $user->permissions()->orderBy('key')->pluck('key');
        $this->line("  PERMISSIONS = {$perms->count()}");
        $this->line('');

        $this->line('  Permission Keys:');
        foreach ($perms as $key) {
            $this->line("    · {$key}");
        }

        $this->line('');
        $this->info('  MENU:');

        // Always-shown "Meu Painel"
        $hasRoute = Route::has('client.dashboard');
        $icon = $hasRoute ? '<OK>' : '<MISSING_ROUTE>';
        $this->line("  [{$icon}] Meu Painel - sempre visível - rota: client.dashboard");

        foreach ($this->menuMap as $permKey => $item) {
            if ($permKey === 'client.dashboard.view') continue;

            $hasPerm  = $user->hasPermission($permKey);
            $hasRoute = Route::has($item['route']);

            if ($hasPerm && $hasRoute) {
                $status = 'OK';
            } elseif (!$hasPerm) {
                $status = "BLOCKED (sem permissão: {$permKey})";
            } else {
                $status = "MISSING_ROUTE ({$item['route']})";
            }

            $icon = str_starts_with($status, 'OK') ? 'OK' : $status;
            $this->line("  [{$icon}] {$item['label']} - rota: {$item['route']}");
        }

        $this->line('');
        $this->info('═══════════════════════════════════════════════════');

        return 0;
    }
}
