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

        // テストユーザー作成（既存の場合はスキップ）
        $existingUser = User::where('email', 'test@example.com')->first();
        if (!$existingUser) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
            echo "Test user created.\n";
        } else {
            echo "Test user 'test@example.com' already exists, skipping...\n";
        }
    }
}
