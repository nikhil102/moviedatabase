<?php

namespace App\Services\Data\Local;

use App\Episode;
use App\Person;
use App\Services\Data\Contracts\DataProvider;
use App\Title;
use Carbon\Carbon;
use Common\Tags\Tag;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class LocalDataProvider implements DataProvider
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @param Title $title
     * @param Tag $tag
     */
    public function __construct(Title $title, Tag $tag)
    {
        $this->title = $title;
        $this->tag = $tag;
    }

    public function getTitle(Title $title)
    {
        return [];
    }

    public function getPerson(Person $person)
    {
        return [];
    }

    public function getSeason(Title $title, $seasonNumber)
    {
        return [];
    }

    public function search($query, $params = [])
    {
        $titles = collect();
        $people = collect();

        if (Arr::get($params, 'type') !== 'person') {
            $titles = $this->title
                ->whereRaw("MATCH(name) AGAINST('$query')")
                ->orderBy('popularity', 'desc')
                ->limit(20)
                ->get(['id', 'name', 'year', 'description', 'poster', 'is_series', 'tmdb_id']);
        }

        if (Arr::get($params, 'type') !== 'title') {
            $people = app(Person::class)
                ->with('popularCredits')
                ->whereRaw("MATCH(name) AGAINST('$query')")
                ->orderBy('popularity', 'desc')
                ->limit(20)
                ->get(['id', 'name', 'poster', 'tmdb_id']);
        }

        return $titles
            ->concat($people)
            ->slice(0, Arr::get($params, 'limit', 8))
            ->values();
    }

    public function getTitles($titleType, $titleCategory)
    {
        $titleType = $titleType === 'tv' ? Title::SERIES_TYPE : Title::MOVIE_TYPE;

        if ($titleCategory === 'popular') {
            return $this->title
                ->orderBy('popularity', 'desc')
                ->limit(20)
                ->where('is_series', $titleType === Title::SERIES_TYPE)
                ->get();
        } else if ($titleCategory === 'topRated') {
            return $this->getTopRatedTitles($titleType);
        } else if ($titleCategory === 'upcoming') {
            return $this->getMoviesReleasingBetween(Carbon::now(), Carbon::now()->addWeek());
        } else if ($titleCategory === 'nowPlaying') {
            return $this->getMoviesReleasingBetween(Carbon::now(), Carbon::now()->subWeek(2));
        } else if ($titleCategory === 'onTheAir') {
           $this->getSeriesAiringBetween(Carbon::now(), Carbon::now()->addWeek());
        } else if ($titleCategory === 'airingToday') {
            return $this->getSeriesAiringBetween(Carbon::now(), Carbon::now()->addDay());
        } else if ($titleCategory === 'latestVideos') {
            return $this->title
                ->join('videos', 'titles.id', '=', 'videos.title_id')
                ->where('videos.source', 'local')
                ->where('approved', true)
                ->orderBy('videos.created_at', 'desc')
                ->select('titles.*')
                ->distinct()
                ->limit(20)
                ->get();
        } else if ($titleCategory === 'lastAdded') {
            return $this->title
                ->where('is_series', $titleType === Title::SERIES_TYPE)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        } else if (str_contains($titleCategory, ['keyword', 'genre'])) {
            list($_, $tagId) = explode(':', $titleCategory);
            $tagName = $this->tag->find($tagId)->name;
            $relation = starts_with($titleCategory, 'keyword') ? 'keywords' : 'genres';
            $query = $this->title
                ->whereHas($relation, function(Builder $query) use($tagName) {
                    $query->where('name', $tagName);
                })
                ->orderBy('popularity', 'desc')
                ->limit(40);
            if ($titleType !== '*') {
                $query->where('is_series', $titleType === Title::SERIES_TYPE);
            }
            return $query->get();

        }
    }
    
    private function getTopRatedTitles($type)
    {
        $ratingCol = config('common.site.rating_column');

        $query = $this->title
            ->where('is_series', $type === Title::SERIES_TYPE);

        if (str_contains($ratingCol, 'tmdb_vote_average')) {
            $query->orderBy(DB::raw('tmdb_vote_count > 100'), 'desc');
        }

        return $query
            ->orderBy($ratingCol, 'desc')
            ->limit(20)
            ->get();
    }

    private function getMoviesReleasingBetween($from, $to)
    {
        return $this->title
            ->whereBetween('release_date', [$from, $to])
            ->orderBy('popularity', 'desc')
            ->limit(20)
            ->where('is_series', false)
            ->get(['id', 'name']);
    }

    private function getSeriesAiringBetween($from, $to)
    {
        $titleIds = app(Episode::class)
            ->whereBetween('release_date',  [$from, $to])
            ->limit(300)
            ->get(['title_id'])
            ->pluck('title_id')
            ->unique()
            ->slice(0, 20);

        return $this->title->whereIn('id', $titleIds)->get(['id', 'name']);
    }
}