<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

echo "=== 株価更新システムの状態確認 ===\n";

$stocks = Stock::all();

echo "株式数: " . $stocks->count() . "\n\n";

foreach ($stocks as $stock) {
    echo "=== {$stock->company_name} ===\n";
    echo "現在価格: {$stock->current_price}円\n";
    echo "最終更新: " . ($stock->last_updated_at ? $stock->last_updated_at : 'なし') . "\n";
    echo "価格範囲: {$stock->min_price}円 ～ {$stock->max_price}円\n";
    
    // 最新の履歴データを確認
    $latestHistory = $stock->priceHistory()->latest('recorded_at')->first();
    if ($latestHistory) {
        echo "最新履歴: " . $latestHistory->recorded_at . " - {$latestHistory->price}円 ({$latestHistory->change_percentage}%)\n";
    } else {
        echo "履歴データ: なし\n";
    }
    
    // 今日の履歴データ数を確認
    $todayCount = $stock->priceHistory()->whereDate('recorded_at', now())->count();
    echo "今日の履歴データ: {$todayCount}件\n";
    echo "\n";
}

// 株価更新スケジュールの確認
echo "=== スケジュール設定 ===\n";
echo "毎日15:00（日本時間）に株価更新が実行されます\n";
echo "手動更新コマンド: php artisan stocks:update-prices\n";
echo "強制更新コマンド: php artisan stocks:update-prices --force\n";
echo "\n";

// 最新の株価履歴件数
$totalHistory = StockPriceHistory::count();
echo "株価履歴の総データ数: {$totalHistory}件\n";

// 今日作成されたデータ数
$todayHistory = StockPriceHistory::whereDate('recorded_at', now())->count();
echo "今日作成された履歴データ: {$todayHistory}件\n";
?>