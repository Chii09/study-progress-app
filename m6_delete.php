<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: m6_main.php');
    exit;
}

$material_id = $_POST['material_id'] ?? '';

if (ctype_digit((string) $material_id)) {
    $sql = 'DELETE FROM materials WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', (int) $material_id, PDO::PARAM_INT);
    $stmt->execute();
    $_SESSION['flash_success'] = '教材を削除しました。';
} else {
    $_SESSION['flash_errors'] = ['不正な教材IDです。'];
}

header('Location: m6_main.php');
exit;
