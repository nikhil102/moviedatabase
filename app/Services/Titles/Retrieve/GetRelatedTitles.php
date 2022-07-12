<?php

namespace App\Services\Titles;

use DB;
use App\Title;
use Illuminate\Support\Arr;
use Common\Settings\Settings;
use Illuminate\Support\Collection;

class GetRelatedTitles
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Title $title
     * @param Settings $settings
     */
    public function __construct(Title $title, Settings $settings)
    {
        $this->title = $title;
        $this->settings = $settings;
    }

    /**
     * @param Title $title
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function execute(Title $title, $params = [])
    {
        $keywordIds = $title->keywords->pluck('id');
        $genreIds = $title->genres->pluck('id');

        if ($keywordIds->isNotEmpty()) {
            $titleIds = $this->getByTags($keywordIds, $title->id, $params);
        } else if ($genreIds->isNotEmpty()) {
            $titleIds = $this->getByTags($genreIds, $title->id, $params);
        }

        if (isset($titleIds)) {
            $query = $this->title
                ->whereIn('id', $titleIds);

            if ( ! $this->settings->get('tmdb.includeAdult')) {
                $query->where('adult', false);
            }

            return $query->get();
        }

        return collect();
    }

    /**
     * @param Collection $tagIds
     * @param int $titleId
     * @param array $params
     * @return Collection
     */
    private function getByTags(Collection $tagIds, $titleId, $params)
    {
        return DB::table('titles')->select(DB::raw('(id), COUNT(*) AS tag_count'))
            ->join('taggables', 'taggable_id', '=', 'titles.id')
            ->whereIn('taggables.tag_id', $tagIds)
            ->where('taggables.taggable_type', Title::class)
            ->where('titles.id', '!=', $titleId)
            ->groupBy('titles.id')
            ->orderBy('tag_count', 'desc')
            ->limit(Arr::get($params, 'limit', 10))
            ->pluck('id');
    }
}