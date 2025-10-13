<?php
require_once 'vendor/autoload.php';

// Laravelアプリケーションを起動
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

echo "=== データベースの状態確認 ===\n";
echo "Stock count: " . Stock::count() . "\n";
echo "StockPriceHistory count: " . StockPriceHistory::count() . "\n";

if (StockPriceHistory::count() > 0) {
    $sample = StockPriceHistory::with('stock')->first();
    echo "Sample history: Stock=" . $sample->stock->company_name . ", Price=" . $sample->price . ", Date=" . $sample->recorded_at . "\n";
    
    // 最新の履歴データの日付範囲を確認
    $oldest = StockPriceHistory::orderBy('recorded_at', 'asc')->first();
    $newest = StockPriceHistory::orderBy('recorded_at', 'desc')->first();
    echo "Date range: " . $oldest->recorded_at . " to " . $newest->recorded_at . "\n";
} else {
    echo "No price history data found\n";
}
?>