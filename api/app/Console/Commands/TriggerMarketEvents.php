<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\News;
use App\Models\Industry;
use App\Models\Stock;
use App\Models\StockPriceHistory;
use Carbon\Carbon;

class TriggerMarketEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:trigger {--probability=30 : イベント発生確率（%）}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '確率でマーケットイベントを発生させ、業界株価に影響を与える';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $probability = $this->option('probability');
        $this->info("市場イベントチェック開始... (発生確率: {$probability}%)");

        // 確率でイベント発生判定
        $randomNumber = mt_rand(1, 100);
        if ($randomNumber > $probability) {
            $this->info('今回はイベントが発生しませんでした。');
            return 0;
        }

        $this->info('🎰 イベントが発生しました！');

        // 既存のイベントテンプレートからランダムに選択
        $event = Event::where('is_active', true)->inRandomOrder()->first();
        if (!$event) {
            $this->error('イベントデータが見つかりません。');
            return 1;
        }

        // ニュース生成
        $news = $this->generateNews($event);

        // 株価への影響適用
        $this->applyStockImpact($event);

        $this->info("✅ イベント完了: {$event->title}");

        return 0;
    }
    
    private function generateRandomEvent($industry)
    {
        // イベントテンプレート
        $positiveEvents = [
            [
                'title' => '{industry}業界に革新技術登場',
                'description' => '{industry}業界で画期的な新技術が発表され、市場に大きな期待が寄せられています。',
                'impact_range' => [5, 15]
            ],
            [
                'title' => '{industry}関連の大型投資発表',
                'description' => '政府や大手企業による{industry}業界への大規模投資が発表されました。',
                'impact_range' => [3, 12]
            ],
            [
                'title' => '{industry}市場の需要急増',
                'description' => '{industry}関連サービスの需要が急激に増加し、業界全体が活況を呈しています。',
                'impact_range' => [4, 10]
            ]
        ];
        
        $negativeEvents = [
            [
                'title' => '{industry}業界に規制強化',
                'description' => '{industry}業界に対する新たな規制が発表され、市場に不安が広がっています。',
                'impact_range' => [-15, -5]
            ],
            [
                'title' => '{industry}関連の技術問題発生',
                'description' => '{industry}業界で技術的な問題が発覚し、市場の信頼が揺らいでいます。',
                'impact_range' => [-12, -3]
            ],
            [
                'title' => '{industry}市場の競争激化',
                'description' => '{industry}業界での競争が激化し、利益率の低下が懸念されています。',
                'impact_range' => [-10, -2]
            ]
        ];
        
        // ポジティブ・ネガティブをランダム選択
        $isPositive = mt_rand(0, 1);
        $events = $isPositive ? $positiveEvents : $negativeEvents;
        $eventTemplate = $events[array_rand($events)];
        
        // 影響率をランダム決定
        $impactRange = $eventTemplate['impact_range'];
        $impactPercentage = mt_rand($impactRange[0] * 100, $impactRange[1] * 100) / 100;
        
        // イベント作成
        return Event::create([
            'title' => str_replace('{industry}', $industry->name, $eventTemplate['title']),
            'description' => str_replace('{industry}', $industry->name, $eventTemplate['description']),
            'event_type' => $isPositive ? 'positive' : 'negative',
            'industry_id' => $industry->id,
            'impact_percentage' => $impactPercentage,
            'occurred_at' => now()
        ]);
    }
    
    private function generateNews($event)
    {
        $newsContent = $event->description . '\n\n';
        $newsContent .= '投資家の皆様におかれましては、市場動向にご注意ください。';
        
        return News::create([
            'title' => $event->title,
            'content' => $newsContent,
            'news_type' => 'event',
            'event_id' => $event->id,
            'published_at' => now()
        ]);
    }
    
    private function applyStockImpact($event)
    {
        // event_impactsテーブルから影響を受ける業界を取得
        $impacts = \DB::table('event_impacts')
            ->where('event_id', $event->id)
            ->where('target_type', 'industry')
            ->get();

        foreach ($impacts as $impact) {
            // 該当業界の株式を取得
            $stocks = Stock::where('industry_id', $impact->target_id)->get();

            foreach ($stocks as $stock) {
                $currentPrice = $stock->current_price;
                $impactFactor = 1 + ($impact->impact_percentage / 100);
                $newPrice = $currentPrice * $impactFactor;
                $newPrice = round($newPrice, 2);

                // 変動率計算
                $changePercentage = (($newPrice - $currentPrice) / $currentPrice) * 100;

                // 株価履歴に記録
                StockPriceHistory::create([
                    'stock_id' => $stock->id,
                    'price' => $newPrice,
                    'change_percentage' => round($changePercentage, 2),
                    'recorded_at' => now()
                ]);

                // 株価更新
                $stock->update([
                    'current_price' => $newPrice,
                    'last_updated_at' => now()
                ]);

                $industry = Industry::find($impact->target_id);
                $this->line("  - {$stock->company_name} ({$industry->name}): {$currentPrice}円 → {$newPrice}円 (" . sprintf('%+.2f', $changePercentage) . "%)");
            }
        }
    }
}
