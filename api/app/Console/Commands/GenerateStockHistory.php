<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\StockPriceHistory;
use Carbon\Carbon;

class GenerateStockHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:generate-history {--days=30 : 生成する日数} {--force : 既存データを削除して再生成}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '株価チャート用の履歴データを人工的に生成する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');
        
        $this->info("株価履歴データを{$days}日分生成します...");
        
        $stocks = Stock::all();
        
        if ($force) {
            $this->info('既存の履歴データを削除中...');
            StockPriceHistory::truncate();
        }
        
        $totalGenerated = 0;
        
        foreach ($stocks as $stock) {
            $this->info("[{$stock->id}] {$stock->company_name} の履歴データ生成中...");
            
            $basePrice = (float) $stock->current_price;
            $minPrice = $stock->min_price ? (float) $stock->min_price : $basePrice * 0.7;
            $maxPrice = $stock->max_price ? (float) $stock->max_price : $basePrice * 1.3;
            
            // 過去のデータから現在に向かって生成
            for ($i = $days; $i >= 1; $i--) {
                $date = now()->subDays($i)->setTime(9, 0, 0); // 午前9時に設定
                
                // 既存データがあるかチェック
                $existingData = StockPriceHistory::where('stock_id', $stock->id)
                    ->whereDate('recorded_at', $date->toDateString())
                    ->exists();
                
                if ($existingData && !$force) {
                    continue; // 既存データがある場合はスキップ
                }
                
                // 前日の価格を取得、なければベース価格を使用
                $previousPrice = $this->getPreviousPrice($stock->id, $date) ?? $basePrice;
                
                // ランダムな変動率 (-10% 〜 +10%)
                $changeRate = (mt_rand(-1000, 1000) / 10000); // -0.1 to 0.1
                $newPrice = $previousPrice * (1 + $changeRate);
                
                // 価格を最小・最大範囲内に調整
                $newPrice = max($minPrice, min($maxPrice, $newPrice));
                $newPrice = round($newPrice, 2);
                
                // 変動率計算
                $changePercentage = (($newPrice - $previousPrice) / $previousPrice) * 100;
                
                StockPriceHistory::create([
                    'stock_id' => $stock->id,
                    'price' => $newPrice,
                    'change_percentage' => round($changePercentage, 2),
                    'recorded_at' => $date
                ]);
                
                $totalGenerated++;
            }
            
            // ストックの現在価格も更新
            $latestHistory = StockPriceHistory::where('stock_id', $stock->id)
                ->latest('recorded_at')
                ->first();
            
            if ($latestHistory) {
                $stock->update([
                    'current_price' => $latestHistory->price,
                    'last_updated_at' => $latestHistory->recorded_at
                ]);
            }
        }
        
        $this->info("✅ 履歴データ生成完了！{$totalGenerated}件のデータを生成しました。");
        return 0;
    }
    
    private function getPreviousPrice($stockId, $date)
    {
        $previousHistory = StockPriceHistory::where('stock_id', $stockId)
            ->where('recorded_at', '<', $date)
            ->latest('recorded_at')
            ->first();
            
        return $previousHistory ? $previousHistory->price : null;
    }
}
