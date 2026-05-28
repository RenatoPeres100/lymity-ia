<?php

namespace App\Policies;

use App\Models\SocialPost;
use App\Models\User;

class SocialPostPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        return $user->isClientUser() && $user->hasPermission('social.view');
    }

    public function view(User $user, SocialPost $post): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser() && $user->hasPermission('social.view')) {
            return $user->client_id && $post->client_id === $user->client_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function update(User $user, SocialPost $post): bool
    {
        return $user->isAdminGeral();
    }

    public function delete(User $user, SocialPost $post): bool
    {
        return $user->isAdminGeral();
    }

    public function approve(User $user, SocialPost $post): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser() && $user->hasPermission('social.approve')) {
            return $user->client_id && $post->client_id === $user->client_id;
        }
        return false;
    }

    public function publish(User $user, SocialPost $post): bool
    {
        return $user->isAdminGeral();
    }
}
