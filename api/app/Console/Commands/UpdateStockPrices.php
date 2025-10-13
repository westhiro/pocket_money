<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\StockPriceHistory;
use Carbon\Carbon;

class UpdateStockPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:update-prices {--force : 強制的に価格を更新する}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '一日一回、株価をランダムに更新する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('株価更新処理を開始します...');
        
        $force = $this->option('force');
        $today = Carbon::today();
        
        // 今日既に更新されているかチェック（force オプションでスキップ可能）
        if (!$force) {
            $alreadyUpdatedToday = Stock::whereDate('last_updated_at', $today)->exists();
            if ($alreadyUpdatedToday) {
                $this->warn('今日は既に株価が更新されています。強制更新するには --force オプションを使用してください。');
                return 0;
            }
        }
        
        // イベント発生判定を実行
        $triggeredEvents = $this->checkForEvents();
        
        $stocks = Stock::all();
        $updatedCount = 0;
        
        foreach ($stocks as $stock) {
            $this->updateStockPrice($stock, $triggeredEvents);
            $updatedCount++;
            
            $this->info("[{$updatedCount}/{$stocks->count()}] {$stock->company_name}: {$stock->current_price}円");
        }
        
        // イベントが発生した場合の報告
        if (!empty($triggeredEvents)) {
            $this->info("\n📰 今日発生したイベント:");
            foreach ($triggeredEvents as $event) {
                $this->info("- {$event['title']}: {$event['description']}");
            }
        }
        
        $this->info("株価更新完了！ {$updatedCount}社の株価を更新しました。");
        return 0;
    }
    
    /**
     * イベント発生判定
     */
    private function checkForEvents()
    {
        $triggeredEvents = [];
        
        // 全ての有効なイベントを取得
        $events = \DB::table('events')->where('is_active', true)->get();
        
        foreach ($events as $event) {
            // 確率判定（probability_weightが発生確率%）
            $randomValue = rand(1, 100);
            
            if ($randomValue <= $event->probability_weight) {
                $triggeredEvents[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_type' => $event->event_type,
                    'impact_type' => $event->impact_type
                ];
                
                // ニュースとして記録
                \DB::table('news')->insert([
                    'title' => $event->title,
                    'content' => $event->description,
                    'news_type' => $event->impact_type === 'positive' ? 'good' : 'bad',
                    'is_published' => true,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $this->info("🎲 イベント発生: {$event->title} (確率: {$event->probability_weight}%)");
            }
        }
        
        return $triggeredEvents;
    }
    
    private function updateStockPrice(Stock $stock, $triggeredEvents = [])
    {
        // 最新の履歴データを取得して、それを基準に価格を設定
        $latestHistory = $stock->priceHistory()->latest('recorded_at')->first();
        
        if ($latestHistory) {
            // 履歴データが存在する場合、最新履歴を現在価格として使用
            $currentPrice = (float) $latestHistory->price;
            
            // 現在価格を履歴の最新価格に同期
            if ($stock->current_price != $currentPrice) {
                $stock->update(['current_price' => $currentPrice]);
            }
        } else {
            $currentPrice = (float) $stock->current_price;
        }
        
        // 最小値・最大値を現在価格の適切な範囲に設定
        $stock->update([
            'min_price' => $currentPrice * 0.6,  // 現在価格の60%
            'max_price' => $currentPrice * 1.4   // 現在価格の140%
        ]);
        
        // 基本変動率を計算（通常の±3%）
        $baseChangePercentage = (rand(-300, 300) / 100); // -3.00 to +3.00
        
        // イベント影響を計算
        $eventImpact = $this->calculateEventImpact($stock, $triggeredEvents);
        
        // 最終的な変動率 = 基本変動 + イベント影響
        $totalChangePercentage = $baseChangePercentage + $eventImpact;
        
        // 新価格を計算
        $newPrice = $currentPrice * (1 + ($totalChangePercentage / 100));
        
        // イベント影響があった場合の表示
        if ($eventImpact != 0) {
            $this->info("  💥 イベント影響: {$eventImpact}% (基本: {$baseChangePercentage}% + イベント: {$eventImpact}%)");
        }
        
        // 最小値・最大値の範囲内に制限
        $newPrice = max($stock->min_price, min($stock->max_price, $newPrice));
        $newPrice = round($newPrice, 2);
        
        // 実際の変動率を再計算
        $actualChangePercentage = $currentPrice > 0 ? (($newPrice - $currentPrice) / $currentPrice) * 100 : 0;
        
        // 株価履歴に記録（今日15:00のタイムスタンプで統一）
        $today = now()->setTime(15, 0, 0);
        
        // 今日既に履歴があるかチェック
        $existingToday = $stock->priceHistory()->whereDate('recorded_at', $today)->first();
        
        if (!$existingToday) {
            StockPriceHistory::create([
                'stock_id' => $stock->id,
                'price' => $newPrice,
                'change_percentage' => round($actualChangePercentage, 2),
                'recorded_at' => $today
            ]);
        } else {
            // 今日の履歴が既に存在する場合は更新
            $existingToday->update([
                'price' => $newPrice,
                'change_percentage' => round($actualChangePercentage, 2)
            ]);
        }
        
        // 株価を更新
        $stock->update([
            'current_price' => $newPrice,
            'last_updated_at' => now()
        ]);
    }
    
    /**
     * イベントが株価に与える影響を計算
     */
    private function calculateEventImpact(Stock $stock, $triggeredEvents)
    {
        $totalImpact = 0.0;
        
        foreach ($triggeredEvents as $event) {
            // このイベントがこの株式に影響するかチェック
            $impacts = \DB::table('event_impacts')
                ->where('event_id', $event['id'])
                ->get();
            
            foreach ($impacts as $impact) {
                $isAffected = false;
                $impactPercentage = 0.0;
                
                if ($impact->target_type === 'industry') {
                    // 業界への影響
                    if ($stock->industry_id == $impact->target_id) {
                        $isAffected = true;
                        $impactPercentage = $impact->impact_percentage;
                    }
                } elseif ($impact->target_type === 'stock') {
                    // 個別株式への影響
                    if ($stock->id == $impact->target_id) {
                        $isAffected = true;
                        $impactPercentage = $impact->impact_percentage;
                    }
                }
                
                if ($isAffected) {
                    $totalImpact += $impactPercentage;
                    
                    // 業界名を取得して表示
                    if ($impact->target_type === 'industry') {
                        $industry = \DB::table('industries')->where('id', $impact->target_id)->first();
                        $industryName = $industry ? $industry->name : "不明";
                        $this->info("  📈 {$stock->company_name}が{$event['title']}の影響を受けます（{$industryName}業界: {$impactPercentage}%）");
                    }
                }
            }
        }
        
        return $totalImpact;
    }
}
