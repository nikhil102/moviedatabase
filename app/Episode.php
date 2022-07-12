<?php

namespace App;

use Awobaz\Compoships\Compoships;
use App\Services\Traits\HasCreditableRelation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

/**
 * Class Episode
 * @property string $type;
 * @property int $episode_number;
 * @property int season_number
 * @property int title_id
 * @property Carbon $updated_at;
 * @method static Episode findOrFail($id, $columns = ['*'])
 */
class Episode extends Model
{
    use HasCreditableRelation, Compoships;

    const EPISODE_TYPE = 'episode';

    protected $guarded = ['id'];
    protected $appends = ['type', 'rating'];
    protected $dates = ['release_date'];

    protected $casts = [
        'id' => 'integer',
        'episode_number' => 'integer',
        'season_number' => 'integer',
        'year' => 'integer',
        'title_id' => 'integer',
        'season_id' => 'integer',
        'allow_update' => 'boolean',
        'tmdb_vote_count' => 'integer',
        'popularity' => 'integer',
    ];

    public $hidden = [
        'imdb_rating',
        'imdb_votes_num',
        'tmdb_vote_average',
        'local_vote_average',
        'tmdb_vote_count',
        'mc_user_score',
        'mc_critic_score'
    ];

    /**
     * @return float
     */
    public function getRatingAttribute() {
        return Arr::get($this->attributes, config('common.site.rating_column'));
    }

    public function getTypeAttribute()
    {
        return self::EPISODE_TYPE;
    }

    /**
     * @return BelongsTo
     */
    public function title()
    {
        return $this->belongsTo(Title::class);
    }

    /**
     * Uses "Compoships" trait to query by multiple relation fields.
     *
     * @return HasMany
     */
    public function stream_videos()
    {
        return $this->hasMany(
            Video::class,
            ['episode', 'season', 'title_id'],
            ['episode_number', 'season_number', 'title_id']
        )->where('approved', true)
            ->where('category', 'full')
            ->orderBy('order', 'asc');
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
