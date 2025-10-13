<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

// 5Gネットワークスの株式を探す
$stock = Stock::where('company_name', 'like', '%5G%')->orWhere('company_name', 'like', '%ネットワーク%')->first();

echo "=== チャートロジックのテスト ===\n";

// 1週間の期間を計算
$oneWeekAgo = now()->subDays(7);
echo "1週間前の基準日: " . $oneWeekAgo->format('Y-m-d H:i:s') . "\n";

$weekData = $stock->priceHistory()
    ->where('recorded_at', '>=', $oneWeekAgo)
    ->orderBy('recorded_at', 'asc')
    ->get();

echo "1週間データの件数: " . $weekData->count() . "\n";
foreach ($weekData as $data) {
    echo "  " . $data->recorded_at . " - " . $data->price . "\n";
}

// 1ヶ月の期間を計算
$oneMonthAgo = now()->subDays(30);
echo "\n1ヶ月前の基準日: " . $oneMonthAgo->format('Y-m-d H:i:s') . "\n";

$monthData = $stock->priceHistory()
    ->where('recorded_at', '>=', $oneMonthAgo)
    ->orderBy('recorded_at', 'asc')
    ->get();

echo "1ヶ月データの件数: " . $monthData->count() . "\n";

// 1ヶ月データの最後の7日間を抽出
$monthLast7 = $monthData->slice(-7);
echo "\n1ヶ月データの最後7日間:\n";
foreach ($monthLast7 as $data) {
    echo "  " . $data->recorded_at . " - " . $data->price . "\n";
}

// 比較
echo "\n=== 一致確認 ===\n";
$weekDates = $weekData->pluck('recorded_at')->map(function($date) {
    return $date->format('Y-m-d H:i:s');
})->toArray();

$monthLast7Dates = $monthLast7->pluck('recorded_at')->map(function($date) {
    return $date->format('Y-m-d H:i:s');
})->toArray();

echo "1週間データの日付:\n";
foreach ($weekDates as $date) {
    echo "  $date\n";
}

echo "\n1ヶ月データの最後7日間の日付:\n";
foreach ($monthLast7Dates as $date) {
    echo "  $date\n";
}

echo "\n一致する日付:\n";
$matching = array_intersect($weekDates, $monthLast7Dates);
foreach ($matching as $date) {
    echo "  $date\n";
}
?>