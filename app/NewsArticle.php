<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $body
 * @property array $meta
 */
class NewsArticle extends Model
{
    const NEWS_ARTICLE_TYPE = 'news_article';
    protected $table = 'custom_pages';
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('pageType', function (Builder $builder) {
            $builder->where('type', self::NEWS_ARTICLE_TYPE);
        });
    }

    public function getMetaAttribute() {
        $meta = json_decode($this->attributes['meta'], true);
        return $meta;
    }

    public function setMetaAttribute($value) {
        if (is_array($value)) {
            $this->attributes['meta'] = json_encode($value);
        }
    }
}
