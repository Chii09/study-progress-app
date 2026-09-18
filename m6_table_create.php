<?php
include 'db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS materials"
            ." ("
            . "id INT AUTO_INCREMENT PRIMARY KEY,"
            . "title CHAR(64) NOT NULL,"
            . "total_amount INT NOT NULL,"
            . "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "materialsテーブル作成完了<br>";

    // 2つ目：progress
    $sql = "CREATE TABLE IF NOT EXISTS progress"
            ." ("
            . "id INT AUTO_INCREMENT PRIMARY KEY,"
            . "material_id INT NOT NULL,"
            . "done_amount INT NOT NULL,"
            . "memo TEXT,"
            . "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,"
            . "FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "progressテーブル作成完了<br>";
} catch (PDOException $e) {
    echo "テーブル作成に失敗しました： " . htmlspecialchars($e->getMessage());
}
?>
