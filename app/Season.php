<?php

namespace App;

use App\Services\Traits\HasCreditableRelation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Collection|Episode[] $episodes
 * @property Carbon $updated_at;
 * @property Carbon $created_at;
 * @property boolean $fully_synced;
 * @property integer $number;
 * @property-read integer $episode_count;
 * @property integer $id;
 * @property integer $title_id;
 * @property-read Title $tile
 * @method static Season findOrFail($id, $columns = ['*'])
 */
class Season extends Model
{
    use HasCreditableRelation;

    const SEASON_TYPE = 'season';

    protected $guarded = ['id'];
    protected $appends = ['type'];

    protected $casts = [
        'id' => 'integer',
        'fully_synced' => 'boolean',
        'episode_count' => 'integer',
        'number' => 'integer'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function title()
    {
        return $this->belongsTo(Title::class);
    }

    public function getTypeAttribute()
    {
        return self::SEASON_TYPE;
    }
}
