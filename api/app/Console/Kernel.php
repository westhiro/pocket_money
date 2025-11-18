<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\UpdateStockPrices::class,
        Commands\GenerateStockHistory::class,
        Commands\CleanOldNews::class,
        Commands\TriggerMarketEvents::class,
        Commands\ProcessWeeklyRealEstate::class,
        Commands\ProcessMonthlyRealEstate::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 1時間ごとに20%の確率でマーケットイベントを発生
        $schedule->command('events:trigger', ['--probability' => 20])
                ->hourly()
                ->timezone('Asia/Tokyo')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/market-events.log'));

        // 1時間ごとに株価を更新
        $schedule->command('stocks:update-prices')
                ->hourly()
                ->timezone('Asia/Tokyo')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/stock-update.log'));

        // 毎日深夜2時に古い株価データを削除（61日以上前のデータ）
        $schedule->command('stocks:clean-old-prices')
                ->dailyAt('02:00')
                ->timezone('Asia/Tokyo')
                ->appendOutputTo(storage_path('logs/stock-cleanup.log'));

        // 毎日深夜3時に古いニュースを削除（7日以上前のニュース）
        $schedule->command('news:clean-old', ['--days' => 7])
                ->dailyAt('03:00')
                ->timezone('Asia/Tokyo')
                ->appendOutputTo(storage_path('logs/news-cleanup.log'));

        // 週次不動産処理（毎週月曜日 午前0時に実行）
        $schedule->command('real-estate:process-weekly')
                ->weeklyOn(1, '00:00')
                ->timezone('Asia/Tokyo')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/real-estate-weekly.log'));

        // 月次不動産処理（毎月1日 午前0時に実行）
        $schedule->command('real-estate:process-monthly')
                ->monthlyOn(1, '00:00')
                ->timezone('Asia/Tokyo')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/real-estate-monthly.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}