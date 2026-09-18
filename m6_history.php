<?php
include 'db_connect.php';

$material_id = $_GET['material_id'] ?? '';
if (!ctype_digit((string) $material_id)) {
    header('Location: m6_main.php');
    exit;
}

$sql = 'SELECT * FROM materials WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', (int) $material_id, PDO::PARAM_INT);
$stmt->execute();
$material = $stmt->fetch(); // 1件なのでfetch()

if (!$material) {
    header('Location: m6_main.php');
    exit;
}

$sql2 = 'SELECT * FROM progress WHERE material_id = :material_id ORDER BY created_at DESC';
$stmt2 = $pdo->prepare($sql2);
$stmt2->bindValue(':material_id', (int) $material_id, PDO::PARAM_INT);
$stmt2->execute();
$history = $stmt2->fetchAll(); // 全件なのでfetchAll()

$title = htmlspecialchars($material['title'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?>の履歴</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2><?php echo $title; ?>の履歴</h2>
    <a href="m6_main.php"><-一覧に戻る</a>
    <hr>
    <?php if (count($history) > 0): ?>
    <?php foreach ($history as $row): ?>
        <div class="material-card">
            <p><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p>進捗： <?php echo (int) $row['done_amount']; ?>分</p>
            <p>メモ： <?php echo nl2br(htmlspecialchars((string) $row['memo'], ENT_QUOTES, 'UTF-8')); ?></p>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>まだ記録がありません</p>
<?php endif; ?>
    <a href="m6_main.php"><-一覧に戻る</a>
</body>
</html>
