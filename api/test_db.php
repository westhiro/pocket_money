<?php
// MySQL接続テスト
try {
    echo "MySQL接続テストを開始...\n";
    
    // 1. 基本的なMySQL接続テスト
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307', 'root', '');
    echo "✅ MySQL接続成功\n";
    
    // 2. データベース一覧取得
    $stmt = $pdo->query('SHOW DATABASES');
    echo "📋 データベース一覧:\n";
    while ($row = $stmt->fetch()) {
        echo "  - " . $row['Database'] . "\n";
    }
    
    // 3. pocket_moneyデータベース存在確認
    $stmt = $pdo->query("SHOW DATABASES LIKE 'pocket_money'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "✅ pocket_moneyデータベースが存在します\n";
    } else {
        echo "❌ pocket_moneyデータベースが存在しません\n";
    }
    
} catch(PDOException $e) {
    echo "❌ MySQL接続エラー: " . $e->getMessage() . "\n";
} catch(Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "\n";
}
?>