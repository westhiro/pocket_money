<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// ユーザーのコイン残高を更新
$user = \App\Models\User::find(1);
if ($user) {
    $user->update(['coin_balance' => 100000]); // 10万コインに設定
    echo "User coin_balance updated to: " . $user->fresh()->coin_balance . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}