<?php

namespace App\Policies;

use App\Models\SocialPost;
use App\Models\User;

class SocialPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser() || $user->isClientUser();
    }

    public function view(User $user, SocialPost $post): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            return $user->company_id && $post->company_id === $user->company_id;
        }

        if ($user->isClientUser()) {
            return $user->client_id && $post->client_id === $user->client_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser();
    }

    public function update(User $user, SocialPost $post): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyUser()) {
            return $user->company_id && $post->company_id === $user->company_id;
        }
        return false;
    }

    public function delete(User $user, SocialPost $post): bool
    {
        return $this->update($user, $post);
    }

    public function approve(User $user, SocialPost $post): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            return $user->company_id && $post->company_id === $user->company_id;
        }

        if ($user->isClientAdmin()) {
            return $user->client_id && $post->client_id === $user->client_id;
        }

        return false;
    }

    public function publish(User $user, SocialPost $post): bool
    {
        return $this->approve($user, $post);
    }
}
