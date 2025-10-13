<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

echo "=== 今日のデータのタイムスタンプと変動率を修正 ===\n";

$today = now()->setTime(15, 0, 0); // 15:00に統一
echo "統一するタイムスタンプ: " . $today->format('Y-m-d H:i:s') . "\n\n";

$stocks = Stock::all();

foreach ($stocks as $stock) {
    echo "=== {$stock->company_name} ===\n";
    
    // 今日のデータを取得
    $todayData = $stock->priceHistory()
        ->whereDate('recorded_at', $today)
        ->first();
    
    if ($todayData) {
        echo "現在のデータ: " . $todayData->recorded_at . " - {$todayData->price}円\n";
        
        // 前日のデータを取得
        $yesterdayData = $stock->priceHistory()
            ->whereDate('recorded_at', '<', $today)
            ->orderBy('recorded_at', 'desc')
            ->first();
        
        if ($yesterdayData) {
            echo "前日のデータ: " . $yesterdayData->recorded_at . " - {$yesterdayData->price}円\n";
            
            // 正しい変動率を計算
            $currentPrice = (float) $todayData->price;
            $previousPrice = (float) $yesterdayData->price;
            $actualChangePercentage = $previousPrice > 0 ? 
                (($currentPrice - $previousPrice) / $previousPrice) * 100 : 0;
            
            echo "正しい変動率: " . round($actualChangePercentage, 2) . "%\n";
            
            // データを更新
            $todayData->update([
                'recorded_at' => $today,
                'change_percentage' => round($actualChangePercentage, 2)
            ]);
            
            echo "更新完了: " . $today->format('Y-m-d H:i:s') . " - {$currentPrice}円 (" . 
                 ($actualChangePercentage >= 0 ? '+' : '') . round($actualChangePercentage, 2) . "%)\n";
        } else {
            echo "前日のデータが見つかりません\n";
            // タイムスタンプのみ修正
            $todayData->update([
                'recorded_at' => $today
            ]);
        }
    } else {
        echo "今日のデータが見つかりません\n";
    }
    echo "\n";
}

echo "今日のデータ修正が完了しました。\n";
?>