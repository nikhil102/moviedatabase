<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EpisodePolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->hasPermission('episodes.view');
    }

    public function show(User $user)
    {
        return $user->hasPermission('episodes.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('episodes.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('episodes.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('episodes.delete');
    }
}
