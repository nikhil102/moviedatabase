<?php

namespace App\Console;

use App\Console\Commands\CleanDemoSite;
use App\Console\Commands\CreateDemoStreamLinks;
use App\Console\Commands\CreateDemoStreamVideos;
use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\TruncateTitleData;
use App\Console\Commands\UpdateListsFromRemote;
use App\Console\Commands\UpdateNewsFromRemote;
use App\Console\Commands\UpdateSeasonsFromRemote;
use Common\Generators\Action\GenerateAction;
use Common\Generators\Controller\GenerateController;
use Common\Generators\Model\GenerateModel;
use Common\Generators\Policy\GeneratePolicy;
use Common\Generators\Request\GenerateRequest;
use Common\Settings\Settings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * @var array
     */
    protected $commands = [
        UpdateNewsFromRemote::class,
        UpdateListsFromRemote::class,
        UpdateSeasonsFromRemote::class,
        CleanDemoSite::class,
        GenerateSitemap::class,
        TruncateTitleData::class,
        CreateDemoStreamVideos::class,
        CreateDemoStreamLinks::class,

        GenerateController::class,
        GenerateModel::class,
        GeneratePolicy::class,
        GenerateRequest::class,
        GenerateAction::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $settings = app(Settings::class);

        if ($settings->get('news.auto_update')) {
            $schedule->command('news:update')->daily();
        }

        if (config('common.site.demo')) {
            $schedule->command('demo:clean')->daily();
        }

        $schedule->command('lists:update')->daily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
