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
            ['name' => 'エネルギー', 'description' => '地球から石油やガスを掘り出して、ガソリンや電気のもとを作る', 'icon' => 'energy'],
            ['name' => '原材料（素材）', 'description' => '鉄や化学薬品、ガスなど、いろんなものの"もと"となる素材を作る', 'icon' => 'science'],
            ['name' => '機械・プラント', 'description' => '飛行機のエンジンや建設機械、工場の設備など大きな機械を作る', 'icon' => 'precision_manufacturing'],
            ['name' => '日常の楽しみ・便利サービス', 'description' => 'ネット通販や電気自動車など、みんなが便利・ワクワクするサービスを提供', 'icon' => 'shopping_cart'],
            ['name' => '食品・日用品', 'description' => 'スーパーで売っている食べ物やシャンプー、トイレットペーパーなどを作る', 'icon' => 'local_grocery_store'],
            ['name' => '医療・健康サービス', 'description' => 'お薬を開発したり、健康保険を運営したりして、病気の予防や治療をサポート', 'icon' => 'local_hospital'],
            ['name' => '銀行・保険などお金の仕事', 'description' => '銀行でお金を貸したり、投資したり、保険でリスクを管理したりする', 'icon' => 'account_balance'],
            ['name' => 'スマホ・パソコン関連', 'description' => 'iPhoneやパソコンを作ったり、映像処理チップを開発したりする', 'icon' => 'computer'],
            ['name' => 'SNS・動画サイトなど通信', 'description' => 'FacebookやYouTubeみたいに、インターネット上でつながるサービスを提供', 'icon' => 'forum'],
            ['name' => '電気・ガス・水道など公益', 'description' => '家や学校に電気や水を届けたり、道路の街灯を管理したりする', 'icon' => 'bolt'],
            ['name' => 'ビル・土地の運営', 'description' => 'オフィスビルや倉庫、携帯基地局の土地・建物を管理・貸し出す', 'icon' => 'apartment']
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
