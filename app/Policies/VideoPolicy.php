<?php

namespace App\Policies;

use App\User;
use App\Video;
use Illuminate\Auth\Access\HandlesAuthorization;

class VideoPolicy
{
    use HandlesAuthorization;

    public function rate(User $user)
    {
        return $user->hasPermission('videos.rate');
    }

    public function index(User $user)
    {
        return $user->hasPermission('videos.view');
    }

    public function show(User $user)
    {
        return $user->hasPermission('videos.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('videos.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('videos.update');
    }

    public function destroy(User $user, $videoIds)
    {
        if ($user->hasPermission('videos.delete')) {
            return true;
        } else {
            $dbCount = app(Video::class)
                ->whereIn('id', $videoIds)
                ->where('user_id', $user->id)
                ->count();
            return $dbCount === count($videoIds);
        }
    }
}
