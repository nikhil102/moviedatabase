<?php

namespace App\Services;

use App\Title;
use App\Person;
use App\Season;
use App\Episode;
use Common\Tags\Tag;
use App\NewsArticle;
use Common\Core\Prerender\BaseUrlGenerator;

class UrlGenerator extends BaseUrlGenerator
{
    /**
     * @param array|Title $title
     * @return string
     */
    public function title($title)
    {
        $slug = str_slug($title['name']);
        return url("titles/{$title['id']}/{$slug}");
    }

    /**
     * @param array|Person $person
     * @return string
     */
    public function person($person)
    {
        $slug = str_slug($person['name']);
        return url("people/{$person['id']}/{$slug}");
    }

    /**
     * @param array|NewsArticle $article
     * @return string
     */
    public function article($article)
    {
        return url("news/{$article['id']}");
    }

    public function newsArticle($article) {
        return $this->article($article);
    }

    /**
     * @param array|Episode $episode
     * @return string
     */
    public function episode($episode)
    {
        return url("titles/{$episode['title_id']}/season/{$episode['season_number']}/episode/{$episode['episode_number']}");
    }

    /**
     * @param array|Season $season
     * @return string
     */
    public function season($season)
    {
        // whole response might be passed in, instead of just season
        if (isset($season['title'])) {
            $season = $season['title']['season'];
        }

        return url("titles/{$season['title_id']}/season/{$season['number']}");
    }

    /**
     * @param array|Tag $genre
     * @return string
     */
    public function genre($genre)
    {
        return url('browse?genres='.$genre['name']);
    }

    /**
     * @param array $data
     * @return string
     */
    public function listModel($data)
    {
        if (isset($data['list'])) {
            $data = $data['list'];
        }
        return url('lists/'.$data['id']);
    }

    /**
     * @param array $data
     * @return string
     */
    public function search($data)
    {
        return url('search?query=' . $data['query']);
    }

    /**
     * @param string|array $item
     * @return string
     */
    public function mediaImage($item)
    {
        if (is_string($item)) {
            return $item;
        } else {
            return $item['poster'] ?: $item['url'];
        }
    }

    /**
     * @param array $data
     * @return string
     */
    public function mediaItem($data)
    {
        if (isset($data['title'])) {
            return $this->title($data['title']);
        } else if (isset($data['person'])) {
            $this->person($data['person']);
        } else {
            return url('');
        }
    }
}