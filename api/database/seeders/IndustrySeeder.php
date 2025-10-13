<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            ['name' => 'テクノロジー', 'description' => 'IT、AI、ソフトウェア関連企業', 'icon' => 'computer'],
            ['name' => '製造業', 'description' => '自動車、機械、化学製品の製造', 'icon' => 'factory'],
            ['name' => '金融', 'description' => '銀行、証券、保険業界', 'icon' => 'bank'],
            ['name' => 'ヘルスケア', 'description' => '医薬品、医療機器、バイオテクノロジー', 'icon' => 'medical'],
            ['name' => 'エネルギー', 'description' => '石油、ガス、再生可能エネルギー', 'icon' => 'energy'],
            ['name' => '小売・消費財', 'description' => '小売店、消費者向け製品', 'icon' => 'shopping'],
            ['name' => '不動産', 'description' => '不動産開発、建設、REIT', 'icon' => 'building'],
            ['name' => '通信', 'description' => '通信キャリア、インターネットサービス', 'icon' => 'signal']
        ];

        foreach ($industries as $industry) {
            \DB::table('industries')->insert([
                'name' => $industry['name'],
                'description' => $industry['description'],
                'icon' => $industry['icon'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
