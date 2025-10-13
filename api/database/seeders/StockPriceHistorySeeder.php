<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;
use App\Models\StockPriceHistory;

class StockPriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        $stocks = Stock::all();
        
        foreach ($stocks as $stock) {
            $basePrice = $stock->current_price;
            $previousPrice = $basePrice;
            
            // 1年分の株価履歴を作成（日毎データ、1日1件）
            for ($i = 365; $i >= 1; $i--) {
                $date = now()->subDays($i)->setTime(15, 0, 0); // 15:00に統一
                
                // 価格変動の計算（前日比-3%～+3%のランダム変動）
                if ($i === 365) {
                    // 365日前の初期価格（現在価格の80-120%の範囲）
                    $price = $basePrice * (0.8 + (rand(0, 40) / 100));
                } else {
                    // 前日比-3%～+3%の変動
                    $changePercent = (rand(-300, 300) / 100); // -3.00 to +3.00
                    $price = $previousPrice * (1 + ($changePercent / 100));
                    
                    // 極端な値を避けるため、ベース価格の60%-140%の範囲に制限
                    $price = max($basePrice * 0.6, min($basePrice * 1.4, $price));
                }
                
                $price = round($price, 2);
                
                StockPriceHistory::create([
                    'stock_id' => $stock->id,
                    'price' => $price,
                    'change_percentage' => isset($changePercent) ? $changePercent : 0,
                    'recorded_at' => $date,
                ]);
                
                $previousPrice = $price;
            }
            
            // 今日のデータ（現在価格）
            $today = now()->setTime(15, 0, 0);
            StockPriceHistory::create([
                'stock_id' => $stock->id,
                'price' => $basePrice,
                'change_percentage' => 0,
                'recorded_at' => $today,
            ]);
        }
    }
}