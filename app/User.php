<?php

namespace App;

use Common\Auth\BaseUser;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property-read Collection|ListModel[] $watchlist
 */
class User extends BaseUser
{
    /**
     * @return HasOne
     */
    public function watchlist()
    {
        return $this->hasOne(ListModel::class)
            ->where('system', 1)
            ->where('name', 'watchlist');
    }

    /**
     * @return HasMany
     */
    public function ratings()
    {
        return $this->hasMany(Review::class)
            ->select('id', 'reviewable_id', 'reviewable_type', 'score')
            ->limit(500);
    }

    public function lists()
    {
        return $this->hasMany(ListModel::class);
    }
}
