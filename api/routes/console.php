<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 毎日午前9時に株価を更新
Schedule::command('stocks:update-prices')->dailyAt('09:00');

// 8時間ごとにイベント発生をチェック（確率30%）
Schedule::command('events:trigger --probability=30')->cron('0 */8 * * *');
