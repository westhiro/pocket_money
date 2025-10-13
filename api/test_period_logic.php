<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

// 5Gネットワークスの株式を探す
$stock = Stock::where('company_name', 'like', '%5G%')->orWhere('company_name', 'like', '%ネットワーク%')->first();

echo "=== 期間別データポイント数のテスト ===\n";
echo "株式: {$stock->company_name} (ID: {$stock->id})\n\n";

// 期間別の設定をテスト
$periods = ['1w', '1m', '1y'];

foreach ($periods as $period) {
    echo "=== {$period} のテスト ===\n";
    
    $periodConfig = [
        '1w' => ['days' => 7, 'points' => 7, 'interval' => 1],
        '1m' => ['days' => 30, 'points' => 30, 'interval' => 1],
        '1y' => ['days' => 365, 'points' => 12, 'interval' => 30]
    ];
    
    $config = $periodConfig[$period];
    $days = $config['days'];
    $targetPoints = $config['points'];
    $interval = $config['interval'];
    
    // 全データから指定日数分を取得
    $allData = $stock->priceHistory()
        ->orderBy('recorded_at', 'desc')
        ->take($days)
        ->get()
        ->reverse()
        ->values();
    
    echo "全データ件数: " . $allData->count() . "\n";
    
    // 期間に応じてデータを間引く
    $chartData = collect();
    if ($period === '1y') {
        // 1年の場合：月次データを取得
        for ($i = 0; $i < $targetPoints && $i * $interval < $allData->count(); $i++) {
            $index = $i * $interval;
            if ($index < $allData->count()) {
                $chartData->push($allData[$index]);
            }
        }
        
        // 最新のデータを必ず含める
        if ($allData->count() > 0) {
            $latestData = $allData->last();
            if (!$chartData->contains('recorded_at', $latestData->recorded_at)) {
                $chartData->push($latestData);
            }
        }
    } else {
        $chartData = $allData;
    }
    
    echo "表示データポイント数: " . $chartData->count() . "\n";
    echo "データ範囲: " . $chartData->first()->recorded_at . " ～ " . $chartData->last()->recorded_at . "\n";
    
    if ($period === '1y') {
        echo "月次データ:\n";
        foreach ($chartData as $index => $data) {
            echo "  " . ($index + 1) . ". " . $data->recorded_at . " - " . $data->price . "\n";
        }
    }
    
    echo "\n";
}

// 1ヶ月と1年の最新データが一致するかをチェック
$monthData = $stock->priceHistory()
    ->orderBy('recorded_at', 'desc')
    ->take(30)
    ->get()
    ->reverse()
    ->values();

$yearData = collect();
$allYearData = $stock->priceHistory()
    ->orderBy('recorded_at', 'desc')
    ->take(365)
    ->get()
    ->reverse()
    ->values();

for ($i = 0; $i < 12 && $i * 30 < $allYearData->count(); $i++) {
    $index = $i * 30;
    if ($index < $allYearData->count()) {
        $yearData->push($allYearData[$index]);
    }
}

if ($allYearData->count() > 0) {
    $latestData = $allYearData->last();
    if (!$yearData->contains('recorded_at', $latestData->recorded_at)) {
        $yearData->push($latestData);
    }
}

echo "=== 一致確認 ===\n";
echo "1ヶ月の最新データ: " . $monthData->last()->recorded_at . " - " . $monthData->last()->price . "\n";
echo "1年の最新データ: " . $yearData->last()->recorded_at . " - " . $yearData->last()->price . "\n";

if ($monthData->last()->recorded_at->eq($yearData->last()->recorded_at) && 
    $monthData->last()->price == $yearData->last()->price) {
    echo "✅ 最新データが一致しています！\n";
} else {
    echo "❌ 最新データが一致していません\n";
}
?>