<?php

namespace Common\Tags;

use Common\Files\FileEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

class Tag extends Model
{
    protected $hidden = ['pivot'];
    protected $guarded = ['id'];
    protected $casts = ['id' => 'integer'];

    const DEFAULT_TYPE = 'default';

    /**
     * @return MorphToMany
     */
    public function files()
    {
        return $this->morphedByMany(FileEntry::class, 'taggable');
    }

    /**
     * @param array $ids
     * @param null|int $userId
     */
    public function attachEntries($ids, $userId = null)
    {
        if ($userId) {
            $ids = collect($ids)->mapWithKeys(function($id) use($userId) {
                return [$id => ['user_id' => $userId]];
            });
        }

        $this->files()->syncWithoutDetaching($ids);
    }

    /**
     * @param array $ids
     * @param null|int $userId
     */
    public function detachEntries($ids, $userId = null)
    {
        $query = $this->files();

        if ($userId) {
            $query->wherePivot('user_id', $userId);
        }

        $query->detach($ids);
    }

    /**
     * @param Collection|array $tags
     * @return Collection|Tag[]
     */
    public function insertOrRetrieve($tags)
    {
        if ( ! $tags instanceof Collection) {
            $tags = collect($tags);
        }

        if (is_string($tags->first())) {
            $tags = $tags->map(function($tag) {
                return ['name' => $tag, 'type' => 'custom'];
            });
        }

        $tags = $tags->toLower('name');
        $tagType = $tags->first()['type'];
        $existing = $this->getByNames($tags->pluck('name'), $tagType);

        $new = $tags->filter(function($tag) use($existing) {
            return !$existing->contains('name', strtolower($tag['name']));
        });

        if ($new->isNotEmpty()) {
            $this->insert($new->toArray());
            return $this->getByNames($tags->pluck('name'), $tagType);
        } else {
            return $existing;
        }
    }

    /**
     * @param Collection $names
     * @param string $type
     * @return Collection
     */
    public function getByNames(Collection $names, $type = null)
    {
        $query = $this->whereIn('name', $names);
        if ($type) $query->where('type', $type);
        return $query->get()->toLower('name');
    }

    /**
     * @param string $value
     * @return string
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ? $value : $this->attributes['name'];
    }
}
