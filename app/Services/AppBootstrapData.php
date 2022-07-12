<?php namespace App\Services;

use Common\Core\Bootstrap\BaseBootstrapData;

class AppBootstrapData extends BaseBootstrapData
{
    public function init()
    {
        parent::init();

        if (isset($this->data['user'])) {
            $this->getWatchlist();
            $this->getRatings();
        }

        return $this;
    }

    /**
     * @return void
     */
    private function getWatchlist()
    {
        $list = $this->data['user']
            ->watchlist()
            ->first();

        if ( ! $list) return;

        $items = $list->getItems(['minimal' => true]);

        $this->data['watchlist'] = [
            'id' => $list->id,
            'items' => $items
        ];
    }

    /**
     * @return void
     */
    private function getRatings()
    {
        $this->data['ratings'] = $this->data['user']->ratings()->get();
    }
}
