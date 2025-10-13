<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== イベントシステムの現状確認 ===\n";

// イベントデータを確認
$events = DB::table('events')->get();
echo "登録済みイベント数: " . $events->count() . "\n\n";

foreach ($events as $event) {
    echo "=== {$event->title} ===\n";
    echo "説明: {$event->description}\n";
    echo "種類: {$event->event_type}\n";
    echo "影響: {$event->impact_type}\n";
    echo "確率: {$event->probability_weight}%\n";
    echo "活性: " . ($event->is_active ? '有効' : '無効') . "\n";
    
    // このイベントの影響を確認
    $impacts = DB::table('event_impacts')->where('event_id', $event->id)->get();
    if ($impacts->count() > 0) {
        echo "影響範囲:\n";
        foreach ($impacts as $impact) {
            if ($impact->target_type === 'industry') {
                $industry = DB::table('industries')->where('id', $impact->target_id)->first();
                $industryName = $industry ? $industry->name : "不明(ID: {$impact->target_id})";
                echo "  業界: {$industryName} ({$impact->impact_percentage}%)\n";
            } else {
                echo "  {$impact->target_type}: ID {$impact->target_id} ({$impact->impact_percentage}%)\n";
            }
        }
    } else {
        echo "影響範囲: なし\n";
    }
    echo "\n";
}

// 業界一覧を確認
echo "=== 業界一覧 ===\n";
$industries = DB::table('industries')->get();
foreach ($industries as $industry) {
    $stockCount = DB::table('stocks')->where('industry_id', $industry->id)->count();
    echo "{$industry->id}. {$industry->name} ({$stockCount}社)\n";
}

echo "\n=== 今後の実装予定 ===\n";
echo "1. 株価更新時にイベント発生判定\n";
echo "2. イベントが発生した場合の影響計算\n";
echo "3. ニュースとして表示\n";
echo "4. 影響を受けた業界/企業の株価変動\n";
?>