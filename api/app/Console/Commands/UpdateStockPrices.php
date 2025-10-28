<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\StockPriceHistory;
use App\Models\User;
use App\Models\UserStock;
use Carbon\Carbon;

class UpdateStockPrices extends Command
{
    protected $signature = 'stocks:update-prices {--force : 強制的に価格を更新する}';
    protected $description = '1時間ごとに株価をランダムに更新する（新システム）';

    public function handle()
    {
        $this->info('株価更新処理を開始します...');

        $force = $this->option('force');
        $now = Carbon::now();

        // 今回の更新時刻（分・秒を00に丸める）
        $updateTime = $now->copy()->setMinute(0)->setSecond(0);

        // 今時間既に更新されているかチェック（force オプションでスキップ可能）
        if (!$force) {
            $alreadyUpdated = StockPriceHistory::where('recorded_at', $updateTime)->exists();
            if ($alreadyUpdated) {
                $this->warn('この時間は既に株価が更新されています。強制更新するには --force オプションを使用してください。');
                return 0;
            }
        }

        // 1. 緊急イベント発生判定（20%の確率）
        $emergencyEvent = $this->checkForEmergencyEvent();

        // 2. 全株式を取得して更新
        $stocks = Stock::all();
        $updatedCount = 0;

        foreach ($stocks as $stock) {
            $this->updateStockPrice($stock, $emergencyEvent, $updateTime);
            $updatedCount++;

            $trendEmoji = $stock->current_trend === 'upward' ? '📈' : '📉';
            $this->info("[{$updatedCount}/{$stocks->count()}] {$stock->company_name}: {$stock->current_price}円 {$trendEmoji}");
        }

        // イベントが発生した場合の報告
        if ($emergencyEvent) {
            $this->info("\n🚨 緊急イベント発生!");
            $this->info("- {$emergencyEvent['title']}: {$emergencyEvent['description']}");
        }

        $this->info("\n株価更新完了！ {$updatedCount}社の株価を更新しました。");

        // 3. 全ユーザーの資産履歴を記録
        $this->recordAssetHistory($updateTime);

        return 0;
    }

    /**
     * 全ユーザーの資産履歴を記録
     */
    private function recordAssetHistory($recordedAt)
    {
        $this->info("\n資産履歴を記録中...");

        $users = User::all();
        $recordedCount = 0;

        foreach ($users as $user) {
            // 保有株式の合計価値を計算
            $userStocks = UserStock::where('user_id', $user->id)
                ->where('quantity', '>', 0)
                ->with('stock')
                ->get();

            $totalStockValue = $userStocks->sum(function($userStock) {
                return $userStock->stock->current_price * $userStock->quantity;
            });

            $totalAssets = $user->current_coins + $totalStockValue;

            // 資産履歴を記録
            \DB::table('asset_histories')->insert([
                'user_id' => $user->id,
                'total_assets' => $totalAssets,
                'stock_value' => $totalStockValue,
                'coin_balance' => $user->current_coins,
                'recorded_at' => $recordedAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $recordedCount++;
        }

        $this->info("資産履歴記録完了！ {$recordedCount}ユーザーの資産を記録しました。");
    }

    /**
     * 緊急イベント発生判定（20%の確率）
     */
    private function checkForEmergencyEvent()
    {
        $randomValue = rand(1, 100);

        if ($randomValue <= 20) { // 20%の確率
            // 全ての有効なイベントからランダムに1つ選択
            $event = \DB::table('events')->where('is_active', true)->inRandomOrder()->first();

            if ($event) {
                // ニュースとして記録
                \DB::table('news')->insert([
                    'title' => $event->title,
                    'content' => $event->description,
                    'news_type' => 'event',
                    'event_id' => $event->id,
                    'is_published' => true,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_type' => $event->event_type,
                    'impact_type' => $event->impact_type
                ];
            }
        }

        return null;
    }

    /**
     * 個別株価を更新
     */
    private function updateStockPrice(Stock $stock, $emergencyEvent, $updateTime)
    {
        $currentPrice = (float) $stock->current_price;
        $changePercentage = 0;

        // 緊急イベント中または回復中の株をチェック
        if ($stock->in_emergency_event) {
            // 緊急イベント終了処理
            $this->handleEventRecovery($stock, $currentPrice, $updateTime);
            return;
        }

        if ($stock->needs_event_recovery) {
            // イベント後の回復処理
            $this->handlePostEventRecovery($stock, $currentPrice, $updateTime);
            return;
        }

        // 緊急イベントが発生し、この株が影響を受けるか確認
        if ($emergencyEvent && $this->isStockAffectedByEvent($stock, $emergencyEvent)) {
            $this->handleEmergencyEvent($stock, $emergencyEvent, $currentPrice, $updateTime);
            return;
        }

        // 通常時の処理
        $this->handleNormalUpdate($stock, $currentPrice, $updateTime);
    }

    /**
     * 緊急イベントの影響を受けるか判定
     */
    private function isStockAffectedByEvent($stock, $event)
    {
        $impact = \DB::table('event_impacts')
            ->where('event_id', $event['id'])
            ->where('target_type', 'industry')
            ->where('target_id', $stock->industry_id)
            ->first();

        return $impact !== null;
    }

    /**
     * 緊急イベント処理
     */
    private function handleEmergencyEvent($stock, $event, $currentPrice, $updateTime)
    {
        // イベント影響を取得
        $impact = \DB::table('event_impacts')
            ->where('event_id', $event['id'])
            ->where('target_type', 'industry')
            ->where('target_id', $stock->industry_id)
            ->first();

        if (!$impact) return;

        $changePercentage = $impact->impact_percentage;
        $newPrice = $currentPrice * (1 + ($changePercentage / 100));
        $newPrice = round($newPrice, 2);

        // 株価履歴に記録
        StockPriceHistory::create([
            'stock_id' => $stock->id,
            'price' => $newPrice,
            'change_percentage' => round($changePercentage, 2),
            'recorded_at' => $updateTime
        ]);

        // 4%以上の変動があった場合、次回調整が必要
        $needsCorrection = abs($changePercentage) >= 4.0;

        $stock->update([
            'current_price' => $newPrice,
            'last_change_percentage' => $changePercentage,
            'in_emergency_event' => true,
            'needs_event_recovery' => $needsCorrection,
            'last_updated_at' => now()
        ]);

        $this->info("  🚨 {$stock->company_name} がイベント影響: {$changePercentage}%");
    }

    /**
     * イベント終了処理（イベント発生から1時間後）
     */
    private function handleEventRecovery($stock, $currentPrice, $updateTime)
    {
        // イベント終了後の変動: -3%〜+5%
        $recoveryChange = (rand(-300, 500) / 100);
        $newPrice = $currentPrice * (1 + ($recoveryChange / 100));
        $newPrice = round($newPrice, 2);

        StockPriceHistory::create([
            'stock_id' => $stock->id,
            'price' => $newPrice,
            'change_percentage' => round($recoveryChange, 2),
            'recorded_at' => $updateTime
        ]);

        $stock->update([
            'current_price' => $newPrice,
            'last_change_percentage' => $recoveryChange,
            'in_emergency_event' => false,
            'last_updated_at' => now()
        ]);

        $this->info("  🔄 {$stock->company_name} イベント終了: {$recoveryChange}%");
    }

    /**
     * イベント後の調整処理（4%以上変動した翌時間に1%戻る）
     */
    private function handlePostEventRecovery($stock, $currentPrice, $updateTime)
    {
        // 1%戻る
        $correctionChange = $stock->last_change_percentage > 0 ? -1.0 : 1.0;
        $newPrice = $currentPrice * (1 + ($correctionChange / 100));
        $newPrice = round($newPrice, 2);

        StockPriceHistory::create([
            'stock_id' => $stock->id,
            'price' => $newPrice,
            'change_percentage' => round($correctionChange, 2),
            'recorded_at' => $updateTime
        ]);

        $stock->update([
            'current_price' => $newPrice,
            'last_change_percentage' => $correctionChange,
            'needs_event_recovery' => false,
            'needs_correction' => false,
            'last_updated_at' => now()
        ]);

        $this->info("  ↩️  {$stock->company_name} 調整: {$correctionChange}%");
    }

    /**
     * 通常時の株価更新
     */
    private function handleNormalUpdate($stock, $currentPrice, $updateTime)
    {
        // 前回の変動で調整が必要な場合
        if ($stock->needs_correction) {
            $this->handleCorrection($stock, $currentPrice, $updateTime);
            return;
        }

        // 50%の確率でトレンドを変更
        if (rand(1, 100) <= 50) {
            $newTrend = $stock->current_trend === 'upward' ? 'downward' : 'upward';
            $stock->update([
                'current_trend' => $newTrend,
                'trend_updated_at' => now()
            ]);

            // トレンド変更のニュースは作成しない（イベントベースのニュースのみ表示）
            // $this->createTrendNews($stock, $newTrend);
        }

        // トレンドに基づいた変動
        if ($stock->current_trend === 'upward') {
            // 上昇傾向: +0.5%〜+1.5%
            $changePercentage = (rand(50, 150) / 100);
        } else {
            // 減少傾向: -0.5%〜-1.5%
            $changePercentage = (rand(-150, -50) / 100);
        }

        $newPrice = $currentPrice * (1 + ($changePercentage / 100));
        $newPrice = round($newPrice, 2);

        // 株価履歴に記録
        StockPriceHistory::create([
            'stock_id' => $stock->id,
            'price' => $newPrice,
            'change_percentage' => round($changePercentage, 2),
            'recorded_at' => $updateTime
        ]);

        // 1.0%を超える変動があった場合、次回調整が必要
        $needsCorrection = abs($changePercentage) > 1.0;

        $stock->update([
            'current_price' => $newPrice,
            'last_change_percentage' => $changePercentage,
            'needs_correction' => $needsCorrection,
            'last_updated_at' => now()
        ]);
    }

    /**
     * 調整処理（1.0%を超える変動の翌時間）
     */
    private function handleCorrection($stock, $currentPrice, $updateTime)
    {
        if ($stock->current_trend === 'upward' && $stock->last_change_percentage > 1.0) {
            // 上昇時の調整: -0.1%〜-0.3%
            $changePercentage = (rand(-30, -10) / 100);
        } elseif ($stock->current_trend === 'downward' && $stock->last_change_percentage < -1.0) {
            // 下落時の調整: +0.05%〜+0.3%
            $changePercentage = (rand(5, 30) / 100);
        } else {
            // 通常の変動に戻る
            $this->handleNormalUpdate($stock, $currentPrice, $updateTime);
            return;
        }

        $newPrice = $currentPrice * (1 + ($changePercentage / 100));
        $newPrice = round($newPrice, 2);

        StockPriceHistory::create([
            'stock_id' => $stock->id,
            'price' => $newPrice,
            'change_percentage' => round($changePercentage, 2),
            'recorded_at' => $updateTime
        ]);

        $stock->update([
            'current_price' => $newPrice,
            'last_change_percentage' => $changePercentage,
            'needs_correction' => false,
            'last_updated_at' => now()
        ]);

        $this->info("  ⚖️  {$stock->company_name} 調整: {$changePercentage}%");
    }

    /**
     * トレンド変更ニュース作成
     */
    private function createTrendNews($stock, $newTrend)
    {
        $industry = \DB::table('industries')->where('id', $stock->industry_id)->first();
        $industryName = $industry ? $industry->name : '不明';

        if ($newTrend === 'upward') {
            $title = "📈 {$stock->company_name}が上昇トレンドに";
            $content = "{$industryName}業界の{$stock->company_name}が上昇傾向に転じました。今後の成長が期待されます。";
            $newsType = 'good';
        } else {
            $title = "📉 {$stock->company_name}が下降トレンドに";
            $content = "{$industryName}業界の{$stock->company_name}が下降傾向に転じました。市場の動向に注意が必要です。";
            $newsType = 'bad';
        }

        \DB::table('news')->insert([
            'title' => $title,
            'content' => $content,
            'news_type' => $newsType,
            'is_published' => true,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
