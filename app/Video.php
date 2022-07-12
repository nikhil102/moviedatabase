<?php

namespace App;

use Awobaz\Compoships\Compoships;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int positive_votes
 * @property int negative_votes
 * @property string name
 * @property string description
 * @property string thumbnail
 * @property int season
 * @property int episode
 * @property-read Episode episode_model
 */
class Video extends Model
{
    use Compoships;

    const VIDEO_TYPE_EMBED = 'embed';
    const VIDEO_TYPE_DIRECT = 'direct';
    const VIDEO_TYPE_EXTERNAL = 'external';

    protected $guarded = ['id'];
    protected $appends = ['score'];
    protected $casts = [
        'negative_votes' => 'integer',
        'positive_votes' => 'integer',
        'order' => 'integer',
        'approved' => 'boolean',
        'reports' => 'integer',
        'title_id' => 'integer',
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function title()
    {
        return $this->belongsTo(Title::class);
    }

    public function ratings()
    {
        return $this->hasMany(VideoRating::class);
    }

    public function reports()
    {
        return $this->hasMany(VideoReport::class);
    }

    public function captions()
    {
        return $this->hasMany(VideoCaption::class)
            ->orderBy('order', 'asc');
    }

    /**
     * Uses "Compoships" trait to query by multiple relation fields.
     *
     * @return BelongsTo
     */
    public function episode_model()
    {
        return $this->belongsTo(
            Episode::class,
            ['episode', 'season', 'title_id'],
            ['episode_number', 'season_number', 'title_id']
        );
    }

    public function getScoreAttribute()
    {
        $total = $this->positive_votes + $this->negative_votes;
        if ( ! $total) return null;
        return round(($this->positive_votes / $total) * 100);
    }

    public function scopeSelectScore(Builder $query)
    {
        return $query->select(['*', DB::raw('((positive_votes + 1.9208) / (positive_votes + negative_votes) -.96 * SQRT((positive_votes * negative_votes) / (positive_votes + negative_votes) + 0.9604) /
         (positive_votes + negative_votes)) / (1 + 3.8416 / (positive_votes + negative_votes))
         AS score')]);
    }
}
