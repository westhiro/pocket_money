<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;
use Carbon\Carbon;

class CleanOldNews extends Command
{
    protected $signature = 'news:clean-old {--days=7 : 削除する日数}';
    protected $description = '指定日数以上前の古いニュースを削除する';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("【ニュース削除処理開始】");
        $this->info("削除対象: {$cutoffDate->format('Y-m-d H:i:s')} より前のニュース");

        // 古いニュースをカウント
        $oldNewsCount = News::where('published_at', '<', $cutoffDate)->count();

        if ($oldNewsCount === 0) {
            $this->info("削除対象のニュースはありません。");
            return 0;
        }

        $this->info("削除対象ニュース数: {$oldNewsCount}件");

        // 削除実行
        $deleted = News::where('published_at', '<', $cutoffDate)->delete();

        $this->info("✅ {$deleted}件のニュースを削除しました。");

        return 0;
    }
}
