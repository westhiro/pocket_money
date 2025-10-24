<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockPriceHistory;
use Carbon\Carbon;

class CleanOldStockPrices extends Command
{
    protected $signature = 'stocks:clean-old-prices';
    protected $description = '61日以上前の株価履歴データを削除する';

    public function handle()
    {
        $this->info('古い株価データの削除を開始します...');

        // 61日以上前のデータを削除
        $cutoffDate = Carbon::now()->subDays(61);

        $deletedCount = StockPriceHistory::where('recorded_at', '<', $cutoffDate)->delete();

        $this->info("削除完了: {$deletedCount}件の古いデータを削除しました。");
        $this->info("削除基準日: {$cutoffDate->format('Y-m-d H:i:s')} 以前のデータ");

        return 0;
    }
}
