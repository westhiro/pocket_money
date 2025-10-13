<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 順番重要: 依存関係の順でSeeder実行
        $this->call([
            IndustrySeeder::class,
            StockSeeder::class,
            LearningVideoSeeder::class,
            EventSeeder::class,
        ]);

        // テストユーザー作成
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
