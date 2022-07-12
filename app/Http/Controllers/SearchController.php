<?php

namespace App\Http\Controllers;

use App\Services\Data\Local\LocalDataProvider;
use App\Services\Data\Tmdb\TmdbApi;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index($query)
    {
        $dataProvider = app(Settings::class)->get('content.search_provider');
        $results = $this->searchUsing($dataProvider, $query);

        $results = $results->map(function($result) {
            if (isset($result['description'])) {
                $result['description'] = str_limit($result['description'], 170);
            }
            return $result;
        })->values();

        return $this->success(['results' => $results, 'query' => e($query)]);
    }

    private function searchUsing($provider, $query)
    {
        if ($provider === 'local') {
            return app(LocalDataProvider::class)->search($query, $this->request->all());
        } else if ($provider === 'tmdb') {
            return app(TmdbApi::class)->search($query, $this->request->all());
        } else if ($provider === 'all') {
            $local = app(LocalDataProvider::class)->search($query, $this->request->all());
            $tmdb = app(TmdbApi::class)->search($query, $this->request->all());
            $merged = $local->concat($tmdb)->unique(function($item) {
                return ($item['tmdb_id'] ?: $item['name']) . $item['type'];
            });
            $grouped = $merged->groupBy('type');

            // make sure specified limit is enforced per group
            // (title, person) instead of the whole collection
            $grouped = $grouped->map(function(Collection $group) {
                return $group->slice(0, $this->request->get('limit', 8));
            })->flatten(1);

            return $grouped->sortByDesc('popularity');
        }
    }
}
