<?php

namespace App\Policies;

use Common\Auth\BaseUser;
use App\VideoCaption;
use Common\Core\Policies\BasePolicy;

class CaptionPolicy extends BasePolicy
{
    public function index(BaseUser $user, $userId = null)
    {
        return $user->hasPermission('captions.view') || $user->id === (int) $userId;
    }

    public function show(BaseUser $user, VideoCaption $caption)
    {
        return $user->hasPermission('captions.view') || $caption->user_id === $user->id;
    }

    public function store(BaseUser $user)
    {
        return $user->hasPermission('captions.create');
    }

    public function update(BaseUser $user, VideoCaption $caption)
    {
        return $user->hasPermission('captions.update') || $caption->user_id === $user->id;
    }

    public function destroy(BaseUser $user, $captionIds)
    {
        if ($user->hasPermission('captions.delete')) {
            return true;
        } else {
            $dbCount = app(VideoCaption::class)
                ->whereIn('id', $captionIds)
                ->where('user_id', $user->id)
                ->count();
            return $dbCount === count($captionIds);
        }
    }
}
