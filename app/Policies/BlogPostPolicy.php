<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        return $user->isClientUser() && $user->hasPermission('blog.view');
    }

    public function view(User $user, BlogPost $post): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser() && $user->hasPermission('blog.view')) {
            return $user->client_id && $post->client_id === $user->client_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $user->isAdminGeral();
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $user->isAdminGeral();
    }

    public function approve(User $user, BlogPost $post): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isCliente() && $user->hasPermission('blog.approve')) {
            return $user->client_id && $post->client_id === $user->client_id;
        }
        return false;
    }

    public function publish(User $user, BlogPost $post): bool
    {
        return $user->isAdminGeral();
    }
}
