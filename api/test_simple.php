<?php
// 簡単な接続テスト
echo "簡単なMySQL接続テスト\n";

$mysqli = new mysqli('127.0.0.1', 'root', '', '', 3307);

if ($mysqli->connect_error) {
    echo "接続失敗: " . $mysqli->connect_error . "\n";
} else {
    echo "接続成功！\n";
    $mysqli->close();
}
?>