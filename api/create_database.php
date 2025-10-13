<?php
try {
    // 環境設定を読み込み
    $env = file_get_contents('.env');
    preg_match('/DB_HOST=(.+)/', $env, $host_match);
    preg_match('/DB_PORT=(.+)/', $env, $port_match);
    preg_match('/DB_USERNAME=(.+)/', $env, $user_match);
    preg_match('/DB_PASSWORD=(.*)/', $env, $pass_match);
    
    $host = trim($host_match[1] ?? '127.0.0.1');
    $port = trim($port_match[1] ?? '3306');
    $username = trim($user_match[1] ?? 'root');
    $password = trim($pass_match[1] ?? '');
    
    // MySQLに接続（データベースを指定せずに）
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // データベースを作成
    $pdo->exec("CREATE DATABASE IF NOT EXISTS pocket_money CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "データベース 'pocket_money' を作成しました。\n";
    
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage() . "\n";
}
?>