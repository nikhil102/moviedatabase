<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImagePolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->hasPermission('images.view');
    }

    public function show(User $user)
    {
        return $user->hasPermission('images.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('images.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('images.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('images.delete');
    }
}
