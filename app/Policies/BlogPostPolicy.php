<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\Client;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser() || $user->isClientUser();
    }

    public function view(User $user, BlogPost $post): bool
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
        return $user->isAdminGeral()
            || $user->isAgencyUser()
            || ($user->isClientAdmin() && $user->hasPermission('blog.create'));
    }

    public function update(User $user, BlogPost $post): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            return $user->company_id && $post->company_id === $user->company_id;
        }

        return false;
    }

    public function delete(User $user, BlogPost $post): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyUser()) {
            return $user->company_id && $post->company_id === $user->company_id;
        }
        return false;
    }

    public function approve(User $user, BlogPost $post): bool
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

    public function publish(User $user, BlogPost $post): bool
    {
        return $this->approve($user, $post);
    }
}
