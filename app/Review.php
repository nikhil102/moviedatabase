<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $user_id
 * @property string body
 * @property integer reviewable_id
 * @property string reviewable_type
 * @method static Review findOrFail($id, $columns = ['*'])
 */
class Review extends Model
{
    const USER_REVIEW_TYPE = 'user';
    protected $guarded = ['id'];
    protected $appends = ['media_type'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'reviewable_id' => 'integer',
        'rating' => 'integer',
    ];

    public function getMediaTypeAttribute()
    {
        if ($this->attributes['reviewable_type'] === Episode::class) {
            return Episode::EPISODE_TYPE;
        } else {
            return Title::TITLE_TYPE;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class)
            ->select('id', 'first_name', 'last_name', 'email');
    }
}
