<?php

namespace App\Console\Commands;

use App\NewsArticle;
use App\Services\Data\Contracts\NewsProviderInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateNewsFromRemote extends Command
{
    /**
     * @var string
     */
    protected $signature = 'news:update';

    /**
     * @var string
     */
    protected $description = 'Update news from currently selected 3rd party site.';

    /**
     * @var NewsProviderInterface
     */
    private $newsProvider;

    /**
     * @var NewsArticle
     */
    private $newsArticle;

    /**
     * @param NewsProviderInterface $newsProvider
     * @param NewsArticle $newsArticle
     */
    public function __construct(
        NewsProviderInterface $newsProvider,
        NewsArticle $newsArticle
    ) {
        parent::__construct();
        $this->newsProvider = $newsProvider;
        $this->newsArticle = $newsArticle;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $newArticles = $this->newsProvider->getArticles()->map(function($article) {
            $article['slug'] = str_slug(str_limit($article['title'], 50));
            $article['type'] = NewsArticle::NEWS_ARTICLE_TYPE;
            $article['meta'] = json_encode($article['meta']);
            $article['created_at'] = Carbon::now();
            $article['updated_at'] = Carbon::now();
            return $article;
        });

        $existing = $this->newsArticle->whereIn('slug', $newArticles->pluck('slug'))->get();

        // filter out already existing articles
        $newArticles = $newArticles->filter(function($newArticle) use($existing) {
            return ! $existing->first(function($existingArticle) use($newArticle) {
                return $existingArticle['title'] === $newArticle['title'] || $existingArticle['slug'] === $newArticle['slug'];
            });
        })->unique('slug');

        $this->newsArticle->insert($newArticles->toArray());

        $this->info('News updated.');
    }
}
