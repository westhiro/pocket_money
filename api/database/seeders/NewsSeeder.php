<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\News;
use Carbon\Carbon;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存のニュースを削除
        News::truncate();

        // イベントテーブルから全イベントを取得
        $events = Event::all();

        // 各イベントに対してニュースを生成
        foreach ($events as $index => $event) {
            // 過去7日間のランダムな日時を生成
            $daysAgo = rand(0, 6);
            $publishedAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            News::create([
                'title' => $event->title,
                'content' => $event->description,
                'news_type' => 'event',
                'event_id' => $event->id,
                'is_published' => true,
                'published_at' => $publishedAt
            ]);
        }

        $this->command->info('イベントベースのニュースを' . $events->count() . '件作成しました。');
    }
}
