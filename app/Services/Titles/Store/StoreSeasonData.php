<?php

namespace App\Services\Titles;

use App\Person;
use App\Season;
use App\Services\Titles\Store\StoreCredits;
use App\Title;

class StoreSeasonData
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var Season
     */
    private $season;

    /**
     * @param Person $person
     * @param Season $season
     */
    public function __construct(Person $person, Season $season)
    {
        $this->person = $person;
        $this->season = $season;
    }

    /**
     * @param Title $title
     * @param array $data
     * @return Title
     */
    public function execute(Title $title, $data)
    {
        if (empty($data)) return $title;

        $this->title = $title;

        $season = $this->persistData($data);
        app(StoreCredits::class)->execute($season, $data['cast']);

        if (isset($data['episodes'])) {
            app(StoreEpisodeData::class)->execute($title, $season, $data['episodes']);
        }

        return $this->title;
    }

    /**
     * @param array $data
     * @return Season
     */
    private function persistData($data)
    {
        // remove all relation data
        $data = array_filter($data, function ($value) {
            return ! is_array($value) && $value !== Season::SEASON_TYPE;
        });

        return $this->season->updateOrCreate(
            [
                'title_id' => $this->title->id,
                'number' => $data['number'],
            ],
            $data
        );
    }
}