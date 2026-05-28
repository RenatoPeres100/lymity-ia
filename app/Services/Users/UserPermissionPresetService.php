<?php

namespace App\Services\Users;

use App\Models\Permission;
use App\Models\User;

class UserPermissionPresetService
{
    public function getDefaultClientPermissions(): array
    {
        return [
            'client.dashboard.view',
            'client.approvals.view',
            'client.approvals.approve',
            'client.approvals.comment',
            'client.blog.view',
            'client.blog.create',
            'client.blog.update',
            'client.blog.approve',
            'client.blog.schedule',
            'client.brand_context.view',
            'client.brand_context.update',
            'client.routines.view',
            'client.routines.manage',
            'client.ai_employees.view',
            'client.ai_tasks.view',
            'client.ai_tasks.create',
            'client.ai_logs.view',
            'client.files.view',
            'client.files.upload',
            'client.users.view',
            'client.users.create',
            'client.users.update',
            'client.users.disable',
            'client.users.reset_password',
            'client.app.view',
        ];
    }

    public function getDefaultCollaboratorPermissions(string $function = null): array
    {
        $base = [
            'client.dashboard.view',
            'client.approvals.view',
            'client.blog.view',
            'client.files.view',
            'client.app.view',
        ];

        if ($function === 'aprovador') {
            $base[] = 'client.approvals.approve';
            $base[] = 'client.approvals.comment';
        }

        if (in_array($function, ['editor', 'copywriter', 'blog_writer'])) {
            $base[] = 'client.blog.create';
            $base[] = 'client.blog.update';
        }

        return array_unique($base);
    }

    public function syncDefaultClientPermissions(User $user): void
    {
        $keys = $this->getDefaultClientPermissions();
        $ids  = Permission::whereIn('key', $keys)->pluck('id')->toArray();
        $user->permissions()->sync($ids);
        $user->clearPermissionCache();
    }

    public function syncDefaultCollaboratorPermissions(User $user, array $permissionKeys = []): void
    {
        if (empty($permissionKeys)) {
            $permissionKeys = $this->getDefaultCollaboratorPermissions();
        }

        $ids = Permission::whereIn('key', $permissionKeys)->pluck('id')->toArray();
        $user->permissions()->sync($ids);
        $user->clearPermissionCache();
    }
}
