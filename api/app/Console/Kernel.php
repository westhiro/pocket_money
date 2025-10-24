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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
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