<?php

namespace App\Providers;

use App\Episode;
use App\Image;
use App\ListModel;
use App\NewsArticle;
use App\Person;
use App\Policies\EpisodePolicy;
use App\Policies\ImagePolicy;
use App\Policies\ListPolicy;
use App\Policies\NewsArticlePolicy;
use App\Policies\PersonPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\TitlePolicy;
use App\Policies\VideoPolicy;
use App\Review;
use App\Title;
use App\Video;
use App\VideoCaption;
use App\Policies\CaptionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        VideoCaption::class => CaptionPolicy::class,
        ListModel::class => ListPolicy::class,
        Title::class => TitlePolicy::class,
        Image::class => ImagePolicy::class,
        Episode::class => EpisodePolicy::class,
        Review::class => ReviewPolicy::class,
        NewsArticle::class => NewsArticlePolicy::class,
        Video::class => VideoPolicy::class,
        Person::class => PersonPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
