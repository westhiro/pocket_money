<?php
require_once 'vendor/autoload.php';

// Laravelアプリケーションを起動
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

// 5Gネットワークスの株式を探す
$stock = Stock::where('company_name', 'like', '%5G%')->orWhere('company_name', 'like', '%ネットワーク%')->first();

if (!$stock) {
    echo "5Gネットワークス関連の株式が見つかりません。全株式を表示:\n";
    $stocks = Stock::all();
    foreach ($stocks as $s) {
        echo "ID: {$s->id}, 会社名: {$s->company_name}\n";
    }
    exit;
}

echo "=== デバッグ: {$stock->company_name} (ID: {$stock->id}) ===\n";
echo "現在価格: {$stock->current_price}\n\n";

// 1週間のデータを確認
$oneWeekAgo = now()->subDays(7);
$weekData = $stock->priceHistory()
    ->where('recorded_at', '>=', $oneWeekAgo)
    ->orderBy('recorded_at', 'asc')
    ->get();

echo "=== 1週間のデータ (直近7日間) ===\n";
echo "データ件数: " . $weekData->count() . "\n";
if ($weekData->count() > 0) {
    echo "最初: " . $weekData->first()->recorded_at . " - 価格: " . $weekData->first()->price . "\n";
    echo "最後: " . $weekData->last()->recorded_at . " - 価格: " . $weekData->last()->price . "\n";
}

// 1ヶ月のデータを確認
$oneMonthAgo = now()->subDays(30);
$monthData = $stock->priceHistory()
    ->where('recorded_at', '>=', $oneMonthAgo)
    ->orderBy('recorded_at', 'asc')
    ->get();

echo "\n=== 1ヶ月のデータ (直近30日間) ===\n";
echo "データ件数: " . $monthData->count() . "\n";
if ($monthData->count() > 0) {
    echo "最初: " . $monthData->first()->recorded_at . " - 価格: " . $monthData->first()->price . "\n";
    echo "最後: " . $monthData->last()->recorded_at . " - 価格: " . $monthData->last()->price . "\n";
}

// 直近7日間のデータが1ヶ月データの最後の7日間と一致するか確認
echo "\n=== 詳細確認 ===\n";

// 1ヶ月データの最後の7件を取得
$monthLast7Days = $monthData->slice(-7);
echo "1ヶ月データの最後7件:\n";
foreach ($monthLast7Days as $data) {
    echo $data->recorded_at . " - " . $data->price . "\n";
}

echo "\n1週間データ全件:\n";
foreach ($weekData as $data) {
    echo $data->recorded_at . " - " . $data->price . "\n";
}

// 最新の10件のデータを確認
echo "\n=== 最新10件のデータ ===\n";
$latestData = $stock->priceHistory()
    ->orderBy('recorded_at', 'desc')
    ->take(10)
    ->get();
foreach ($latestData as $data) {
    echo $data->recorded_at . " - " . $data->price . "\n";
}
?>