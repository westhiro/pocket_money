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
            // テクノロジー (industry_id: 1)
            ['industry_id' => 1, 'company_name' => 'クラウドテック', 'stock_symbol' => 'CTEC', 'current_price' => 2850.00, 'description' => 'クラウドインフラサービス大手'],
            ['industry_id' => 1, 'company_name' => 'AI Revolution', 'stock_symbol' => 'AIRV', 'current_price' => 4200.00, 'description' => '人工知能ソリューション開発'],
            ['industry_id' => 1, 'company_name' => 'デジタルソフト', 'stock_symbol' => 'DSFT', 'current_price' => 1680.00, 'description' => '業務用ソフトウェア開発'],
            
            // 製造業 (industry_id: 2)
            ['industry_id' => 2, 'company_name' => 'モビリティ工業', 'stock_symbol' => 'MOBI', 'current_price' => 3200.00, 'description' => '次世代自動車部品製造'],
            ['industry_id' => 2, 'company_name' => 'グリーン製造', 'stock_symbol' => 'GREN', 'current_price' => 2100.00, 'description' => '環境配慮型製造業'],
            
            // 金融 (industry_id: 3)
            ['industry_id' => 3, 'company_name' => 'フィンテックバンク', 'stock_symbol' => 'FINB', 'current_price' => 1250.00, 'description' => 'デジタル銀行サービス'],
            ['industry_id' => 3, 'company_name' => '投資パートナーズ', 'stock_symbol' => 'INVP', 'current_price' => 950.00, 'description' => '資産運用・投資顧問'],
            
            // ヘルスケア (industry_id: 4)
            ['industry_id' => 4, 'company_name' => 'バイオファーマ', 'stock_symbol' => 'BIOP', 'current_price' => 5400.00, 'description' => 'バイオ医薬品開発'],
            ['industry_id' => 4, 'company_name' => 'メディカルデバイス', 'stock_symbol' => 'MDEV', 'current_price' => 3800.00, 'description' => '医療機器製造'],
            
            // エネルギー (industry_id: 5)
            ['industry_id' => 5, 'company_name' => 'サステナブルエナジー', 'stock_symbol' => 'SUEN', 'current_price' => 1890.00, 'description' => '再生可能エネルギー'],
            ['industry_id' => 5, 'company_name' => 'エナジーソリューション', 'stock_symbol' => 'ENSOL', 'current_price' => 2340.00, 'description' => 'エネルギー効率化'],
            
            // 小売・消費財 (industry_id: 6)
            ['industry_id' => 6, 'company_name' => 'ライフスタイル', 'stock_symbol' => 'LIFE', 'current_price' => 820.00, 'description' => 'ライフスタイル商品小売'],
            ['industry_id' => 6, 'company_name' => 'エコマース', 'stock_symbol' => 'ECOM', 'current_price' => 1420.00, 'description' => 'EC・オンライン小売'],
            
            // 不動産 (industry_id: 7)
            ['industry_id' => 7, 'company_name' => 'アーバン開発', 'stock_symbol' => 'URBN', 'current_price' => 2600.00, 'description' => '都市部不動産開発'],
            ['industry_id' => 7, 'company_name' => 'スマートビルディング', 'stock_symbol' => 'SMBL', 'current_price' => 1750.00, 'description' => 'スマートビル建設'],
            
            // 通信 (industry_id: 8)
            ['industry_id' => 8, 'company_name' => '5G ネットワークス', 'stock_symbol' => '5GNW', 'current_price' => 3100.00, 'description' => '5G通信インフラ'],
            ['industry_id' => 8, 'company_name' => 'コネクト通信', 'stock_symbol' => 'CONN', 'current_price' => 1560.00, 'description' => 'モバイル通信サービス']
        ];

        foreach ($stocks as $stock) {
            $stockId = \DB::table('stocks')->insertGetId([
                'industry_id' => $stock['industry_id'],
                'company_name' => $stock['company_name'],
                'stock_symbol' => $stock['stock_symbol'],
                'current_price' => $stock['current_price'],
                'description' => $stock['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 株価履歴（過去30日分のランダムデータ）
            for ($i = 30; $i >= 0; $i--) {
                $basePrice = $stock['current_price'];
                $randomChange = rand(-10, 10) / 100; // -10% ~ +10%
                $price = $basePrice * (1 + $randomChange);
                
                \DB::table('stock_price_history')->insert([
                    'stock_id' => $stockId,
                    'price' => round($price, 2),
                    'change_percentage' => round($randomChange * 100, 2),
                    'recorded_at' => now()->subDays($i),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
