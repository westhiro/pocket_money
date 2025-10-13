<?php
echo "MySQL診断開始\n";

// 1. PDO拡張チェック
echo "1. PDO拡張チェック: ";
echo extension_loaded('pdo') ? "OK\n" : "NG\n";
echo "   PDO MySQL拡張: ";
echo extension_loaded('pdo_mysql') ? "OK\n" : "NG\n";

// 2. mysqli拡張チェック  
echo "2. mysqli拡張: ";
echo extension_loaded('mysqli') ? "OK\n" : "NG\n";

// 3. タイムアウト設定チェック
echo "3. PHP設定:\n";
echo "   default_socket_timeout: " . ini_get('default_socket_timeout') . "秒\n";
echo "   mysql.connect_timeout: " . ini_get('mysql.connect_timeout') . "秒\n";

// 4. 簡単なmysqli接続テスト（タイムアウト設定あり）
echo "4. mysqli接続テスト:\n";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    echo "   接続開始...\n";
    $start = time();
    
    $conn = @mysqli_connect('127.0.0.1', 'root', '', '', 3307);
    
    $end = time();
    echo "   時間: " . ($end - $start) . "秒\n";
    
    if ($conn) {
        echo "   結果: 成功！\n";
        mysqli_close($conn);
    } else {
        echo "   結果: 失敗 - " . mysqli_connect_error() . "\n";
    }
    
} catch (Exception $e) {
    echo "   エラー: " . $e->getMessage() . "\n";
}

echo "診断終了\n";
?>