<?php
include 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS materials"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "title CHAR(64),"
        . "total_amount INT,"
        . "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        . ");";

$stmt = $pdo->query($sql);
echo "テーブル作成完了";
?>
