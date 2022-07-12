<?php

namespace App\Services\People\Retrieve;

use App\Person;
use App\Title;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class GetPersonCredits
{
    public function execute(Person $person)
    {
        $credits = $this->getTitleCredits($person);
        $seasonCredits = $this->getSeasonCredits($person);
        $episodeCredits = $this->getEpisodeCredits($person);

        $mergedCredits = $this->mergeCredits($credits['all'], $seasonCredits, $episodeCredits);

        return [
            'credits' => $mergedCredits,
            'knownFor' => $credits['knownFor'],
        ];
    }

    private function mergeCredits($credits1, $credits2, $credits3)
    {
        $mergedCredits =  array_merge_recursive($credits1, $credits2, $credits3);

        $mergedCredits = array_map(function($titles) {
            // sort titles by year
            usort($titles, function($a, $b) {
                return $b['year'] - $a['year'];
            });

            $unique = [];

            // if this title already exists and existing
            // title has episodes property, continue,
            // otherwise push title into 'unique' array
            foreach ($titles as $title) {
                $existing = Arr::get($unique, $title['id']);
                if ($existing && isset($existing['episodes'])) {
                    continue;
                } else {
                    $unique[$title['id']] = $title;
                }
            }

            return array_values($unique);
        }, $mergedCredits);

        return $mergedCredits;
    }

    /**
     * @param Person $person
     * @return array
     */
    private function getTitleCredits(Person $person)
    {
        $credits = $person->credits()->get();

        // generate known for list for actors "known_for" department.
        $allKnownFor = $credits->filter(function (Title $credit) use ($person) {
            $knownFor = strtolower($person->known_for) === 'acting' ? 'cast' : $person->known_for;
            return $credit->pivot->department === strtolower($knownFor);
        });

        $knownFor = $allKnownFor->where('pivot.order', '<', 10);

        if ($knownFor->count() < 4) {
            $knownFor = $allKnownFor;
        }

        // sort by person credit "order" for title as well as title popularity
        $knownFor = $knownFor
            ->sortBy(function($title) {
                $order = $title->pivot->order;
                $popularity = $title->popularity;
                return $order - $popularity;
            })->slice(0, 4)->values();

        // cast to array, so poster/backdrop is not removed later.
        $knownFor = $knownFor->toArray();

        // remove any data not needed to render person filmography
        $credits = $credits->map(function (Title $credit) {
            unset($credit['backdrop']);
            return $credit;
        })->groupBy('pivot.department');

        return ['all' => $credits->toArray(), 'knownFor' => $knownFor];
    }

    /**
     * Get credits for all series seasons person is attached to.
     *
     * @param Person $person
     * @return array
     */
    private function getSeasonCredits(Person $person)
    {
        $seasons = $person->seasonCredits()->with(['title' => function($query) {
            return $query->select('id', 'name', 'year', 'poster');
        }, 'episodes' => function($query) {
            return $query->select('id', 'name', 'year', 'season_id', 'season_number', 'episode_number', 'title_id', 'release_date')
                ->orderBy('season_number', 'desc')
                ->orderBy('episode_number', 'desc');
        }])->get();

        //group all seasons by department, for example "production"
        $groupedSeasons = $seasons->groupBy('pivot.department');

        return $groupedSeasons->map(function (Collection $departmentGroup) {
            $groupedByTitle = $departmentGroup->groupBy('title.id');

            // attach episodes from all seasons to title
            return $groupedByTitle->map(function (Collection $titleGroup) {
                $title = $titleGroup->first()->title;

                //get episodes from each season and move season "pivot" data to each episode
                $episodes = $titleGroup
                    ->pluck('episodes')
                    ->flatten()
                    ->values()
                    ->map(function($episode) use($titleGroup) {
                        $episode->pivot = $titleGroup->first()->pivot->toArray();
                        return $episode;
                    });

                $title->episodes = $episodes->toArray();
                return $title;
            })->values();
        })->toArray();
    }

    /**
     * Get all individual episodes person is credited for.
     *
     * This will return array grouped by department, and
     * series with all episodes person is credited for attached
     * to that series.
     *
     * @param Person $person
     * @return \array
     */
    private function getEpisodeCredits(Person $person)
    {
        $episodes = $person
            ->episodeCredits()
            ->with(['title' => function(BelongsTo $query) {
                $query->select('id', 'name', 'year', 'poster');
            }])
            ->get();

        $groupedByDep = $episodes->groupBy('pivot.department');

        return $groupedByDep->map(function(Collection $episodes) {
            return $episodes->groupBy('title.id')->map(function(Collection $episodes) {
                $title = $episodes->first()->title;
                $title->episodes = $episodes->map(function($episode) {
                    unset($episode->title);
                    return $episode;
                })->toArray();
                return $title;
            })->values();
        })->toArray();
    }
}