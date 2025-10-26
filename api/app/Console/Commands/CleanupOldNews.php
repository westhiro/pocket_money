<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;

class CleanupOldNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:cleanup-old {--force : 確認なしで削除}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'event_idがNULLの古いニュース（トレンドニュース）を削除する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('古いニュースのクリーンアップを開始します...');

        // event_idがNULLのニュースを取得
        $oldNews = News::whereNull('event_id')->get();
        $count = $oldNews->count();

        if ($count === 0) {
            $this->info('削除対象のニュースはありません。');
            return 0;
        }

        $this->warn("削除対象: {$count}件のニュース");

        // --forceオプションがある場合は確認せずに削除
        if ($this->option('force') || $this->confirm('これらのニュースを削除しますか？')) {
            News::whereNull('event_id')->delete();
            $this->info("✅ {$count}件の古いニュースを削除しました。");
        } else {
            $this->info('キャンセルされました。');
        }

        return 0;
    }
}
