<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ニュースデータの確認 ===\n";

$newsCount = DB::table('news')->count();
echo "ニュース数: {$newsCount}\n\n";

if ($newsCount > 0) {
    $recentNews = DB::table('news')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    echo "最新のニュース:\n";
    foreach ($recentNews as $news) {
        $date = date('Y-m-d H:i', strtotime($news->created_at));
        echo "- [{$date}] {$news->title}\n";
        echo "  {$news->content}\n";
        echo "  種類: {$news->news_type} | 公開: " . ($news->is_published ? '済み' : '未公開') . "\n\n";
    }
} else {
    echo "ニュースデータがありません。\n";
}
?>