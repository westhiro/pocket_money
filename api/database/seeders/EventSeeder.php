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
        // 新しいイベントデータ
        $events = [
            [
                'title' => '台風接近',
                'description' => '停電や配送遅延の懸念が高まる',
                'event_type' => 'natural_disaster',
                'impact_type' => 'negative',
                'probability_weight' => 20, // 20%の確率で発生
                'genre' => '災害'
            ],
            [
                'title' => '猛暑到来',
                'description' => '電力・飲料需要が急増',
                'event_type' => 'natural_disaster',
                'impact_type' => 'positive',
                'probability_weight' => 20,
                'genre' => '災害'
            ],
            [
                'title' => '大規模ネット障害',
                'description' => '通信が広範囲でダウン',
                'event_type' => 'industry',
                'impact_type' => 'negative',
                'probability_weight' => 20,
                'genre' => '治安'
            ],
            [
                'title' => '大手との業務提携',
                'description' => '新サービスを共同開発',
                'event_type' => 'company',
                'impact_type' => 'positive',
                'probability_weight' => 20,
                'genre' => '社内（企業内イベント）'
            ],
            [
                'title' => '省エネ設備の導入',
                'description' => '電力使用量の削減に成功',
                'event_type' => 'company',
                'impact_type' => 'positive',
                'probability_weight' => 20,
                'genre' => '社内（企業内イベント）'
            ],
            [
                'title' => '健康志向ブーム',
                'description' => '高たんぱく・低糖商品が人気',
                'event_type' => 'economic',
                'impact_type' => 'positive',
                'probability_weight' => 20,
                'genre' => '世の中（トレンド・景気・生活）'
            ],
            [
                'title' => '送料値上げ',
                'description' => 'ネット通販に逆風',
                'event_type' => 'economic',
                'impact_type' => 'negative',
                'probability_weight' => 20,
                'genre' => '世の中（トレンド・景気・生活）'
            ],
            [
                'title' => 'インフラ投資計画発表',
                'description' => '公共工事の見通しが改善',
                'event_type' => 'political',
                'impact_type' => 'positive',
                'probability_weight' => 20,
                'genre' => '政治（政策・規制・金利）'
            ],
            [
                'title' => '通信・プライバシー規制',
                'description' => '広告やトラッキングの制限が強化',
                'event_type' => 'political',
                'impact_type' => 'negative',
                'probability_weight' => 20,
                'genre' => '政治（政策・規制・金利）'
            ]
        ];

        // イベント影響データ（業界ID対応）
        // 1: エネルギー, 2: 原材料（素材）, 3: 機械・プラント, 4: 日常の楽しみ・便利サービス
        // 5: 食品・日用品, 6: 医療・健康サービス, 7: 銀行・保険などお金の仕事
        // 8: スマホ・パソコン関連, 9: SNS・動画サイトなど通信, 10: 電気・ガス・水道など公益, 11: ビル・土地の運営
        $eventImpacts = [
            // 1. 台風接近 → 電気・ガス・水道など公益(10), 日常の楽しみ・便利サービス(4)
            ['event_id' => 1, 'target_type' => 'industry', 'target_id' => 10, 'impact_percentage' => rand(-5, -1)],
            ['event_id' => 1, 'target_type' => 'industry', 'target_id' => 4, 'impact_percentage' => rand(-5, -1)],

            // 2. 猛暑到来 → 電気・ガス・水道など公益(10), 食品・日用品(5)
            ['event_id' => 2, 'target_type' => 'industry', 'target_id' => 10, 'impact_percentage' => rand(1, 5)],
            ['event_id' => 2, 'target_type' => 'industry', 'target_id' => 5, 'impact_percentage' => rand(1, 5)],

            // 3. 大規模ネット障害 → SNS・動画サイトなど通信(9)
            ['event_id' => 3, 'target_type' => 'industry', 'target_id' => 9, 'impact_percentage' => rand(-5, -1)],

            // 4. 大手との業務提携 → スマホ・パソコン関連(8), 日常の楽しみ・便利サービス(4), 医療・健康サービス(6)
            ['event_id' => 4, 'target_type' => 'industry', 'target_id' => 8, 'impact_percentage' => rand(1, 5)],
            ['event_id' => 4, 'target_type' => 'industry', 'target_id' => 4, 'impact_percentage' => rand(1, 5)],
            ['event_id' => 4, 'target_type' => 'industry', 'target_id' => 6, 'impact_percentage' => rand(1, 5)],

            // 5. 省エネ設備の導入 → エネルギー(1), 機械・プラント(3)
            ['event_id' => 5, 'target_type' => 'industry', 'target_id' => 1, 'impact_percentage' => rand(1, 5)],
            ['event_id' => 5, 'target_type' => 'industry', 'target_id' => 3, 'impact_percentage' => rand(1, 5)],

            // 6. 健康志向ブーム → 食品・日用品(5), 医療・健康サービス(6)
            ['event_id' => 6, 'target_type' => 'industry', 'target_id' => 5, 'impact_percentage' => rand(1, 5)],
            ['event_id' => 6, 'target_type' => 'industry', 'target_id' => 6, 'impact_percentage' => rand(1, 5)],

            // 7. 送料値上げ → 日常の楽しみ・便利サービス(4)
            ['event_id' => 7, 'target_type' => 'industry', 'target_id' => 4, 'impact_percentage' => rand(-5, -1)],

            // 8. インフラ投資計画発表 → ビル・土地の運営(11), 機械・プラント(3), 原材料（素材）(2)
            ['event_id' => 8, 'target_type' => 'industry', 'target_id' => 11, 'impact_percentage' => rand(1, 5)],
            ['event_id' => 8, 'target_type' => 'industry', 'target_id' => 3, 'impact_percentage' => rand(1, 5)],
            ['event_id' => 8, 'target_type' => 'industry', 'target_id' => 2, 'impact_percentage' => rand(1, 5)],

            // 9. 通信・プライバシー規制 → SNS・動画サイトなど通信(9), スマホ・パソコン関連(8)
            ['event_id' => 9, 'target_type' => 'industry', 'target_id' => 9, 'impact_percentage' => rand(-5, -1)],
            ['event_id' => 9, 'target_type' => 'industry', 'target_id' => 8, 'impact_percentage' => rand(-5, -1)]
        ];

        // イベント挿入
        foreach ($events as $index => $event) {
            // 既存のイベントがあれば更新、なければ挿入
            \DB::table('events')->updateOrInsert(
                ['id' => $index + 1],
                [
                    'title' => $event['title'],
                    'description' => $event['description'],
                    'event_type' => $event['event_type'],
                    'genre' => $event['genre'],
                    'impact_type' => $event['impact_type'],
                    'probability_weight' => $event['probability_weight'],
                    'impact_percentage' => 0.00,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
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
