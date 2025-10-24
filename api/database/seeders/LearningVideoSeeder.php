<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LearningVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videos = [
            // 初級
            [
                'title' => '投資の基本：株式とは何か',
                'description' => '株式投資の基本概念と仕組みを学ぼう',
                'video_url' => '/videos/stock-basics.mp4',
                'thumbnail_url' => '/images/thumbnails/stock-basics.jpg',
                'duration_seconds' => 480,
                'coin_reward' => 100.00,
                'category' => '株式投資',
                'difficulty_level' => 'beginner'
            ],
            [
                'title' => 'リスクとリターンの関係',
                'description' => '投資におけるリスクとリターンの基本的な関係性',
                'video_url' => '/videos/risk-return.mp4',
                'thumbnail_url' => '/images/thumbnails/risk-return.jpg',
                'duration_seconds' => 600,
                'coin_reward' => 120.00,
                'category' => '投資理論',
                'difficulty_level' => 'beginner'
            ],
            [
                'title' => '分散投資の重要性',
                'description' => 'ポートフォリオ分散によるリスク軽減方法',
                'video_url' => '/videos/diversification.mp4',
                'thumbnail_url' => '/images/thumbnails/diversification.jpg',
                'duration_seconds' => 540,
                'coin_reward' => 110.00,
                'category' => '投資戦略',
                'difficulty_level' => 'beginner'
            ],
            
            // 中級
            [
                'title' => '企業分析の基本指標',
                'description' => 'PER、PBR、ROEなど基本的な財務指標の見方',
                'video_url' => '/videos/company-analysis.mp4',
                'thumbnail_url' => '/images/thumbnails/company-analysis.jpg',
                'duration_seconds' => 720,
                'coin_reward' => 180.00,
                'category' => '企業分析',
                'difficulty_level' => 'intermediate'
            ],
            [
                'title' => 'チャート分析入門',
                'description' => 'ローソク足チャートの読み方と基本パターン',
                'video_url' => '/videos/chart-analysis.mp4',
                'thumbnail_url' => '/images/thumbnails/chart-analysis.jpg',
                'duration_seconds' => 840,
                'coin_reward' => 200.00,
                'category' => '技術分析',
                'difficulty_level' => 'intermediate'
            ],
            [
                'title' => '市場の仕組みと注文方法',
                'description' => '株式市場の構造と様々な注文方法について',
                'video_url' => '/videos/market-structure.mp4',
                'thumbnail_url' => '/images/thumbnails/market-structure.jpg',
                'duration_seconds' => 660,
                'coin_reward' => 170.00,
                'category' => '市場知識',
                'difficulty_level' => 'intermediate'
            ],
            
            // 上級
            [
                'title' => 'マクロ経済と株価の関係',
                'description' => '金利、インフレ、GDP等が株価に与える影響',
                'video_url' => '/videos/macro-economics.mp4',
                'thumbnail_url' => '/images/thumbnails/macro-economics.jpg',
                'duration_seconds' => 900,
                'coin_reward' => 250.00,
                'category' => '経済学',
                'difficulty_level' => 'advanced'
            ],
            [
                'title' => '高度な投資戦略',
                'description' => 'バリュー投資、成長株投資等の投資哲学',
                'video_url' => '/videos/investment-strategies.mp4',
                'thumbnail_url' => '/images/thumbnails/investment-strategies.jpg',
                'duration_seconds' => 1080,
                'coin_reward' => 300.00,
                'category' => '投資戦略',
                'difficulty_level' => 'advanced'
            ],
            [
                'title' => '行動経済学と投資心理',
                'description' => '投資家心理が市場に与える影響と対策',
                'video_url' => '/videos/behavioral-finance.mp4',
                'thumbnail_url' => '/images/thumbnails/behavioral-finance.jpg',
                'duration_seconds' => 780,
                'coin_reward' => 230.00,
                'category' => '投資心理',
                'difficulty_level' => 'advanced'
            ]
        ];

        foreach ($videos as $video) {
            // 既存の動画があればスキップ
            $existingVideo = \DB::table('learning_videos')
                ->where('title', $video['title'])
                ->first();

            if ($existingVideo) {
                echo "Video '{$video['title']}' already exists, skipping...\n";
                continue;
            }

            \DB::table('learning_videos')->insert([
                'title' => $video['title'],
                'description' => $video['description'],
                'video_url' => $video['video_url'],
                'thumbnail_url' => $video['thumbnail_url'],
                'duration_seconds' => $video['duration_seconds'],
                'coin_reward' => $video['coin_reward'],
                'category' => $video['category'],
                'difficulty_level' => $video['difficulty_level'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
