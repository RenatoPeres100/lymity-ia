<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use App\Services\Users\RolePermissionPreset;
use Illuminate\Database\Seeder;

class RolePermissionPresetSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', '!=', 'admin_geral')->get();

        foreach ($users as $user) {
            $keys = RolePermissionPreset::forRole($user->role);
            if (empty($keys)) {
                continue;
            }

            $ids = Permission::whereIn('key', $keys)->pluck('id')->toArray();

            // Only apply if user currently has no permissions (first-time setup)
            if ($user->permissions()->count() === 0) {
                $user->permissions()->sync($ids);
                $this->command->info("  {$user->email} ({$user->role}) → " . count($ids) . " permissões aplicadas.");
            } else {
                $this->command->line("  {$user->email} ({$user->role}) → já tem permissões, pulando.");
            }
        }
    }
}
