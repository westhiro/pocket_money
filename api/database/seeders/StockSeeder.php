<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stocks = [
            // 1. エネルギー
            ['industry_id' => 1, 'company_name' => 'エネルギー', 'stock_symbol' => 'ENGY', 'current_price' => 2800.00, 'description' => '地球から石油やガスを掘り出して、ガソリンや電気のもとを作る'],

            // 2. 原材料（素材）
            ['industry_id' => 2, 'company_name' => '原材料（素材）', 'stock_symbol' => 'MTAL', 'current_price' => 3200.00, 'description' => '鉄や化学薬品、ガスなど、いろんなものの"もと"となる素材を作る'],

            // 3. 機械・プラント
            ['industry_id' => 3, 'company_name' => '機械・プラント', 'stock_symbol' => 'MACH', 'current_price' => 4500.00, 'description' => '飛行機のエンジンや建設機械、工場の設備など大きな機械を作る'],

            // 4. 日常の楽しみ・便利サービス
            ['industry_id' => 4, 'company_name' => '日常の楽しみ・便利サービス', 'stock_symbol' => 'LIFE', 'current_price' => 5200.00, 'description' => 'ネット通販や電気自動車など、みんなが便利・ワクワクするサービスを提供'],

            // 5. 食品・日用品
            ['industry_id' => 5, 'company_name' => '食品・日用品', 'stock_symbol' => 'FOOD', 'current_price' => 1680.00, 'description' => 'スーパーで売っている食べ物やシャンプー、トイレットペーパーなどを作る'],

            // 6. 医療・健康サービス
            ['industry_id' => 6, 'company_name' => '医療・健康サービス', 'stock_symbol' => 'HLTH', 'current_price' => 5800.00, 'description' => 'お薬を開発したり、健康保険を運営したりして、病気の予防や治療をサポート'],

            // 7. 銀行・保険などお金の仕事
            ['industry_id' => 7, 'company_name' => '銀行・保険などお金の仕事', 'stock_symbol' => 'BANK', 'current_price' => 1850.00, 'description' => '銀行でお金を貸したり、投資したり、保険でリスクを管理したりする'],

            // 8. スマホ・パソコン関連
            ['industry_id' => 8, 'company_name' => 'スマホ・パソコン関連', 'stock_symbol' => 'TECH', 'current_price' => 8900.00, 'description' => 'iPhoneやパソコンを作ったり、映像処理チップを開発したりする'],

            // 9. SNS・動画サイトなど通信
            ['industry_id' => 9, 'company_name' => 'SNS・動画サイトなど通信', 'stock_symbol' => 'COMU', 'current_price' => 4800.00, 'description' => 'FacebookやYouTubeみたいに、インターネット上でつながるサービスを提供'],

            // 10. 電気・ガス・水道など公益
            ['industry_id' => 10, 'company_name' => '電気・ガス・水道など公益', 'stock_symbol' => 'UTIL', 'current_price' => 2400.00, 'description' => '家や学校に電気や水を届けたり、道路の街灯を管理したりする'],

            // 11. ビル・土地の運営
            ['industry_id' => 11, 'company_name' => 'ビル・土地の運営', 'stock_symbol' => 'REAL', 'current_price' => 3500.00, 'description' => 'オフィスビルや倉庫、携帯基地局の土地・建物を管理・貸し出す']
        ];

        foreach ($stocks as $stock) {
            $stockId = \DB::table('stocks')->insertGetId([
                'industry_id' => $stock['industry_id'],
                'company_name' => $stock['company_name'],
                'stock_symbol' => $stock['stock_symbol'],
                'current_price' => $stock['current_price'],
                'description' => $stock['description'],
                'current_trend' => rand(0, 1) === 0 ? 'upward' : 'downward', // ランダムにトレンド設定
                'trend_updated_at' => now(),
                'needs_correction' => false,
                'last_change_percentage' => 0,
                'in_emergency_event' => false,
                'needs_event_recovery' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 株価履歴（過去30日分のランダムデータ）
            $previousPrice = $stock['current_price'];

            for ($i = 30; $i >= 0; $i--) {
                // 1時間ごとのデータを24時間分生成
                for ($hour = 0; $hour < 24; $hour++) {
                    // より現実的な株価変動を生成（±1%程度）
                    $dailyChangePercent = (rand(-100, 100) / 100); // -1% ~ +1%の変動
                    $price = $previousPrice * (1 + $dailyChangePercent / 100);

                    // 株価が極端な値にならないよう制限
                    $minPrice = $stock['current_price'] * 0.7; // 基準価格の70%まで
                    $maxPrice = $stock['current_price'] * 1.3; // 基準価格の130%まで
                    $price = max($minPrice, min($maxPrice, $price));

                    $recordedAt = now()->subDays($i)->setHour($hour)->setMinute(0)->setSecond(0);

                    \DB::table('stock_price_history')->insert([
                        'stock_id' => $stockId,
                        'price' => round($price, 2),
                        'change_percentage' => round($dailyChangePercent, 2),
                        'recorded_at' => $recordedAt,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // 次の時間の計算のために現在の価格を保存
                    $previousPrice = $price;
                }
            }
        }
    }
}
