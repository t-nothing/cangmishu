<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use PDepend\Util\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\StorageLinkCommand::class,
        Commands\DBResetCommand::class,
        Commands\HomePageCommand::class,
        Commands\RouteListCommand::class,
        Commands\RepushCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $filePath = env('APP_LOG_PATH');

        // $schedule->call(function () {
        //     app('log')->info('执行成功');
        // },[])->everyMinute();

        // $schedule->call('HomePageService@homePageNoticeStore')->everyMinute()->appendOutputTo($filePath);
        // $schedule->call('BatchStatisticsService@store')->everyMinute()->appendOutputTo($filePath);
        // $schedule->call('OrderStatisticsService@store')->everyMinute()->appendOutputTo($filePath);
        // $schedule->call('StockStatisticsService@store')->everyMinute()->appendOutputTo($filePath);

        // 库存检查，不足则发报警邮件
        // $schedule->call('WarningService@expirationWarning')->everyFiveMinutes()->appendOutputTo($filePath);
    }
}
