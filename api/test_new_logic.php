<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

// 5Gネットワークスの株式を探す
$stock = Stock::where('company_name', 'like', '%5G%')->orWhere('company_name', 'like', '%ネットワーク%')->first();

echo "=== 新しいロジックのテスト ===\n";

// 1週間データ（最新7件）
$weekData = $stock->priceHistory()
    ->orderBy('recorded_at', 'desc')
    ->take(7)
    ->get()
    ->reverse()
    ->values();

echo "1週間データ（最新7件）:\n";
foreach ($weekData as $data) {
    echo "  " . $data->recorded_at . " - " . $data->price . "\n";
}

// 1ヶ月データ（最新30件）
$monthData = $stock->priceHistory()
    ->orderBy('recorded_at', 'desc')
    ->take(30)
    ->get()
    ->reverse()
    ->values();

echo "\n1ヶ月データ（最新30件）:\n";
echo "開始: " . $monthData->first()->recorded_at . " - " . $monthData->first()->price . "\n";
echo "終了: " . $monthData->last()->recorded_at . " - " . $monthData->last()->price . "\n";

// 1ヶ月データの最後の7件
$monthLast7 = $monthData->slice(-7);
echo "\n1ヶ月データの最後7件:\n";
foreach ($monthLast7 as $data) {
    echo "  " . $data->recorded_at . " - " . $data->price . "\n";
}

// 一致確認
echo "\n=== 一致確認 ===\n";
$weekDates = $weekData->pluck('recorded_at')->map(fn($date) => $date->format('Y-m-d H:i:s'))->toArray();
$monthLast7Dates = $monthLast7->pluck('recorded_at')->map(fn($date) => $date->format('Y-m-d H:i:s'))->toArray();

echo "一致する日付の数: " . count(array_intersect($weekDates, $monthLast7Dates)) . "\n";

if (count(array_intersect($weekDates, $monthLast7Dates)) === 7) {
    echo "✅ 完全に一致しています！\n";
} else {
    echo "❌ 一致していません\n";
    echo "1週間データの日付:\n";
    foreach ($weekDates as $date) {
        echo "  $date\n";
    }
    echo "1ヶ月データの最後7日間の日付:\n";
    foreach ($monthLast7Dates as $date) {
        echo "  $date\n";
    }
}
?>