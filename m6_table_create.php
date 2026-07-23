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
echo "materialsテーブル作成完了<br>";

// 2つ目：progress
$sql = "CREATE TABLE IF NOT EXISTS progress"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "material_id INT,"
        . "done_amount INT,"
        . "memo TEXT,"
        . "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        . ");";

$stmt = $pdo->query($sql);
echo "progress テーブル作成完了<br>";
?>
