<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

echo "=== 重複データの確認と整理 ===\n";

$today = now()->format('Y-m-d');
echo "今日の日付: {$today}\n\n";

$stocks = Stock::all();

foreach ($stocks as $stock) {
    echo "=== {$stock->company_name} ===\n";
    
    // 今日のデータを全て取得
    $todayData = $stock->priceHistory()
        ->whereDate('recorded_at', $today)
        ->orderBy('recorded_at', 'desc')
        ->get();
    
    echo "今日のデータ件数: " . $todayData->count() . "\n";
    
    if ($todayData->count() > 1) {
        echo "重複データが存在します:\n";
        foreach ($todayData as $index => $data) {
            echo "  {$index}: " . $data->recorded_at . " - {$data->price}円\n";
        }
        
        // 最新のデータを残して、古いデータを削除
        $latestData = $todayData->first();
        $oldData = $todayData->skip(1);
        
        echo "最新データを残します: " . $latestData->recorded_at . " - {$latestData->price}円\n";
        echo "削除するデータ: " . $oldData->count() . "件\n";
        
        foreach ($oldData as $old) {
            echo "  削除: " . $old->recorded_at . " - {$old->price}円\n";
            $old->delete();
        }
        
        // 現在価格を最新データと同期
        if ($stock->current_price != $latestData->price) {
            $stock->update(['current_price' => $latestData->price]);
            echo "現在価格を更新: {$latestData->price}円\n";
        }
    } else {
        echo "重複なし\n";
        if ($todayData->count() === 1) {
            $data = $todayData->first();
            echo "今日のデータ: " . $data->recorded_at . " - {$data->price}円\n";
            
            // 現在価格と同期
            if ($stock->current_price != $data->price) {
                $stock->update(['current_price' => $data->price]);
                echo "現在価格を同期: {$data->price}円\n";
            }
        }
    }
    echo "\n";
}

echo "重複データの整理が完了しました。\n";
?>