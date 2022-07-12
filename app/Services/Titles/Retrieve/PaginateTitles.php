<?php

namespace App\Services\Titles\Retrieve;

use App\Title;
use Carbon\Carbon;
use Common\Database\Paginator;
use Common\Settings\Settings;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PaginateTitles
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
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function execute($params)
    {
        if ($order = Arr::get($params, 'order')) {
            $order = str_replace('user_score', config('common.site.rating_column'), $order);
            $params['order'] = $order;
        }

        $paginator = new Paginator($this->title, $params);

        if ( ! $this->settings->get('tmdb.includeAdult')) {
            $paginator->where('adult', false);
        }

        $paginator->searchCallback = function(Builder $builder, $query) {
            $builder->whereRaw("MATCH(name) AGAINST('$query')");
        };

        if ($this->settings->get('streaming.show_label')) {
            $paginator->withCount('stream_videos');
        }

        $paginator->setDefaultOrderColumns('popularity', 'desc');

        if ($type = $paginator->param('type')) {
            $paginator->where('is_series', $type === Title::SERIES_TYPE);
        }

        if ($genre = $paginator->param('genre')) {
            $genres = explode(',', $genre);
            $paginator->query()->whereHas('genres', function(Builder $query) use($genres) {
                $genres = array_map(function($genre) {
                    return Str::slug($genre, ' ');
                }, $genres);
                $query->whereIn('name', $genres);
            });
        }

        if ($released = $paginator->param('released')) {
            $this->byReleaseDate($released, $paginator);
        }

        if ($runtime = $paginator->param('runtime')) {
            $this->byRuntime($runtime, $paginator);
        }

        if ($score = $paginator->param('score')) {
            $this->byRating($score, $paginator);
        }

        if ($language = $paginator->param('language')) {
            $paginator->query()->where('language', $language);
        }

        if ($certification = $paginator->param('certification')) {
            $paginator->query()->where('certification', $certification);
        }

        if ($country = $paginator->param('country')) {
            $paginator->query()->whereHas('countries', function(Builder $query) use($country) {
                $query->where('name', $country);
            });
        }

        if ($onlyStreamable = $paginator->param('onlyStreamable')) {
            // $paginator->query()->whereHas('stream_videos');
            $paginator->query()->whereIn('titles.id', function($query) {
                $query->from('videos')
                    ->select('videos.title_id')
                    ->where('approved', true)
                    ->where('source', 'local');
            });
        }

        // show titles with less then 50 votes on tmdb last, regardless of their average
        if (str_contains(Arr::get($params, 'order', ''), 'tmdb_vote_average')) {
            $paginator->query()->orderBy(DB::raw('tmdb_vote_count > 100'), 'desc');
        }

        return $paginator->paginate();
    }

    private function byRuntime($runtimes, Paginator $paginator)
    {
        $parts = explode(',', $runtimes);
        if (count($parts) !== 2) return;

        $paginator->query()
            ->where('runtime', '>=', $parts[0])
            ->where('runtime', '<=', $parts[1]);
    }

    private function byReleaseDate($dates, Paginator $paginator)
    {
        $parts = explode(',', $dates);
        if (count($parts) !== 2) return;

        // convert year to full date, otherwise same year range would not work
        // 2019,2019 => 2019-01-01,2019-12-31
        $from = Carbon::create($parts[0])->firstOfYear();
        $to = Carbon::create($parts[1])->lastOfYear();

        $paginator->query()
            ->where('release_date', '>=', $from)
            ->where('release_date', '<=', $to);
    }

    private function byRating($scores, Paginator $paginator)
    {
        $parts = explode(',', $scores);
        if (count($parts) !== 2) return;

        if ($this->settings->get('content.title_provider') !== TITLE::LOCAL_PROVIDER) {
            $paginator->query()
                ->where('tmdb_vote_average', '>=', $parts[0])
                ->where('tmdb_vote_average', '<=', $parts[1])
                ->where('tmdb_vote_count', '>=', 50);
        } else {
            $paginator->query()
                ->where('local_vote_average', '>=', $parts[0])
                ->where('local_vote_average', '<=', $parts[1]);
        }
    }
}