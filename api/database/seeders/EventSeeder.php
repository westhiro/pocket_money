<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // イベントデータ
        $events = [
            [
                'title' => '新AI技術発表',
                'description' => '革新的なAI技術が発表され、関連企業の業績向上が期待される',
                'event_type' => 'industry',
                'impact_type' => 'positive',
                'probability_weight' => 15
            ],
            [
                'title' => 'サイバーセキュリティ事件',
                'description' => '大規模なサイバー攻撃により、IT業界の信頼性に影響',
                'event_type' => 'industry',
                'impact_type' => 'negative',
                'probability_weight' => 8
            ],
            [
                'title' => '環境規制強化',
                'description' => '政府が環境規制を強化、製造業のコスト増加が懸念',
                'event_type' => 'political',
                'impact_type' => 'negative',
                'probability_weight' => 12
            ],
            [
                'title' => 'グリーンエネルギー補助金',
                'description' => '再生可能エネルギー企業への補助金制度が拡充',
                'event_type' => 'political',
                'impact_type' => 'positive',
                'probability_weight' => 10
            ],
            [
                'title' => '金利上昇',
                'description' => '中央銀行が政策金利を引き上げ、金融業界に影響',
                'event_type' => 'economic',
                'impact_type' => 'negative',
                'probability_weight' => 18
            ],
            [
                'title' => '新薬承認',
                'description' => '画期的な新薬が薬事承認され、ヘルスケア業界に追い風',
                'event_type' => 'industry',
                'impact_type' => 'positive',
                'probability_weight' => 12
            ],
            [
                'title' => '自然災害発生',
                'description' => '大規模災害により製造業の生産に影響が発生',
                'event_type' => 'natural_disaster',
                'impact_type' => 'negative',
                'probability_weight' => 5
            ],
            [
                'title' => 'インフラ投資計画',
                'description' => '政府が大規模インフラ投資を発表、建設・不動産業界に好影響',
                'event_type' => 'political',
                'impact_type' => 'positive',
                'probability_weight' => 14
            ],
            [
                'title' => '5G普及加速',
                'description' => '5G通信網の普及が加速、通信業界の成長期待が高まる',
                'event_type' => 'industry',
                'impact_type' => 'positive',
                'probability_weight' => 16
            ],
            [
                'title' => '消費税増税',
                'description' => '消費税増税により消費者の購買力減少、小売業界に悪影響',
                'event_type' => 'economic',
                'impact_type' => 'negative',
                'probability_weight' => 8
            ]
        ];

        // イベント影響データ（業界別）
        $eventImpacts = [
            // 新AI技術発表 → テクノロジー業界に+20%
            ['event_id' => 1, 'target_type' => 'industry', 'target_id' => 1, 'impact_percentage' => 20.0],
            
            // サイバーセキュリティ事件 → テクノロジー業界に-12%
            ['event_id' => 2, 'target_type' => 'industry', 'target_id' => 1, 'impact_percentage' => -12.0],
            
            // 環境規制強化 → 製造業に-15%
            ['event_id' => 3, 'target_type' => 'industry', 'target_id' => 2, 'impact_percentage' => -15.0],
            
            // グリーンエネルギー補助金 → エネルギー業界に+25%
            ['event_id' => 4, 'target_type' => 'industry', 'target_id' => 5, 'impact_percentage' => 25.0],
            
            // 金利上昇 → 金融業界に-8%, 不動産業界に-18%
            ['event_id' => 5, 'target_type' => 'industry', 'target_id' => 3, 'impact_percentage' => -8.0],
            ['event_id' => 5, 'target_type' => 'industry', 'target_id' => 7, 'impact_percentage' => -18.0],
            
            // 新薬承認 → ヘルスケア業界に+30%
            ['event_id' => 6, 'target_type' => 'industry', 'target_id' => 4, 'impact_percentage' => 30.0],
            
            // 自然災害 → 製造業に-20%, エネルギー業界に-10%
            ['event_id' => 7, 'target_type' => 'industry', 'target_id' => 2, 'impact_percentage' => -20.0],
            ['event_id' => 7, 'target_type' => 'industry', 'target_id' => 5, 'impact_percentage' => -10.0],
            
            // インフラ投資 → 不動産業界に+22%
            ['event_id' => 8, 'target_type' => 'industry', 'target_id' => 7, 'impact_percentage' => 22.0],
            
            // 5G普及 → 通信業界に+28%
            ['event_id' => 9, 'target_type' => 'industry', 'target_id' => 8, 'impact_percentage' => 28.0],
            
            // 消費税増税 → 小売業界に-14%
            ['event_id' => 10, 'target_type' => 'industry', 'target_id' => 6, 'impact_percentage' => -14.0]
        ];

        // イベント挿入
        foreach ($events as $index => $event) {
            \DB::table('events')->insert([
                'id' => $index + 1,
                'title' => $event['title'],
                'description' => $event['description'],
                'event_type' => $event['event_type'],
                'impact_type' => $event['impact_type'],
                'probability_weight' => $event['probability_weight'],
                'impact_percentage' => 10.0, // デフォルトの影響率
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // イベント影響挿入
        foreach ($eventImpacts as $impact) {
            \DB::table('event_impacts')->insert([
                'event_id' => $impact['event_id'],
                'target_type' => $impact['target_type'],
                'target_id' => $impact['target_id'],
                'impact_percentage' => $impact['impact_percentage'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
