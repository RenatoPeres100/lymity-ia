<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Users\UserPermissionPresetService;
use Illuminate\Console\Command;

class RefreshClientPermissionsCommand extends Command
{
    protected $signature   = 'permissions:refresh-client
                                {email? : E-mail do usuário (omitir para listar)}
                                {--defaults : Reaplicar permissões padrão do perfil}';
    protected $description = 'Exibe ou reaaplica permissões de usuário cliente/colaborador';

    public function handle(UserPermissionPresetService $preset): int
    {
        $email = $this->argument('email');

        if (!$email) {
            $this->info('Usuários cliente/colaborador:');
            User::whereIn('role', ['cliente', 'colaborador'])->orderBy('email')->get(['id', 'email', 'role', 'status'])->each(function ($u) {
                $cnt = $u->permissions()->count();
                $this->line("  [{$u->role}] {$u->email} — {$cnt} permissões");
            });
            return 0;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuário não encontrado: {$email}");
            return 1;
        }

        $this->line('');
        $this->line("USER   = {$user->email}");
        $this->line("ROLE   = {$user->role}");
        $this->line("TYPE   = {$user->user_type}");
        $this->line("STATUS = {$user->status}");
        $this->line("PERMS  = " . $user->permissions()->count());

        if ($this->option('defaults')) {
            $this->line('');
            $this->info('Reaplicando permissões padrão...');

            if ($user->role === 'cliente') {
                $preset->syncDefaultClientPermissions($user);
                $this->info("✓ Preset de Cliente aplicado.");
            } elseif ($user->role === 'colaborador') {
                $preset->syncDefaultCollaboratorPermissions($user);
                $this->info("✓ Preset de Colaborador aplicado.");
            } else {
                $this->warn("Perfil '{$user->role}' não tem preset de cliente.");
                return 1;
            }

            $this->line("PERMS após = " . $user->fresh()->permissions()->count());
        }

        $this->line('');
        $this->line('Permissões atuais:');
        $user->fresh()->permissions()->orderBy('key')->pluck('key')->each(fn ($k) => $this->line("  · {$k}"));

        return 0;
    }
}
