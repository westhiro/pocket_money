<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Stock;
use App\Models\StockPriceHistory;

echo "=== 前日比計算のテスト ===\n";

// いくつかの株式で前日比を確認
$stocks = Stock::take(3)->get();

foreach ($stocks as $stock) {
    echo "\n=== {$stock->company_name} ===\n";
    echo "現在価格: {$stock->current_price}円\n";
    
    // 最新2件の履歴を取得
    $history = $stock->priceHistory()->take(2)->get();
    
    if ($history->count() >= 2) {
        $current = $history[0]->price;
        $previous = $history[1]->price;
        $changeAmount = $current - $previous;
        $changePercentage = (($current - $previous) / $previous) * 100;
        
        echo "今日の価格: {$current}円\n";
        echo "前日の価格: {$previous}円\n";
        echo "変動金額: " . ($changeAmount >= 0 ? '+' : '') . number_format($changeAmount, 2) . "円\n";
        echo "変動率: " . ($changePercentage >= 0 ? '+' : '') . number_format($changePercentage, 2) . "%\n";
        
        // モデルのメソッドと比較
        $modelChangePercent = $stock->getPriceChangePercentage();
        echo "モデル計算結果: " . ($modelChangePercent >= 0 ? '+' : '') . number_format($modelChangePercent, 2) . "%\n";
        
        echo "履歴データの変動率: " . ($history[0]->change_percentage >= 0 ? '+' : '') . $history[0]->change_percentage . "%\n";
    } else {
        echo "履歴データが不足しているため前日比を計算できません\n";
    }
    
    // 最新5日分の履歴を表示
    echo "\n最新5日の履歴:\n";
    $recentHistory = $stock->priceHistory()->take(5)->get();
    foreach ($recentHistory as $index => $record) {
        $date = $record->recorded_at->format('Y-m-d');
        $change = $record->change_percentage >= 0 ? '+' : '';
        echo "  {$date}: {$record->price}円 ({$change}{$record->change_percentage}%)\n";
    }
}

echo "\n=== 日次更新システムの仕組み ===\n";
echo "1. 毎日15:00に自動実行（php artisan schedule:work）\n";
echo "2. 前日の価格を基準に±3%の変動で新価格を計算\n";
echo "3. 新しい価格をstock_price_historyテーブルに追加\n";
echo "4. stocksテーブルのcurrent_priceを更新\n";
echo "5. 投資ページのチャートが自動で新しいデータを反映\n";
echo "\n手動実行: php artisan stocks:update-prices\n";
echo "強制実行: php artisan stocks:update-prices --force\n";
?>