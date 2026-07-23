<?php
include 'db_connect.php';

$material_id = $_GET['material_id'];

$sql = 'SELECT * FROM materials WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $material_id, PDO::PARAM_INT);
$stmt->execute();
$material = $stmt->fetch(); // 1件なのでfetch()

$sql2 = 'SELECT * FROM progress WHERE material_id = :material_id ORDER BY created_at DESC';
$stmt2 = $pdo->prepare($sql2);
$stmt2->bindParam(':material_id', $material_id, PDO::PARAM_INT);
$stmt2->execute();
$history = $stmt2->fetchAll(); // 全件なのでfetchAll()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $material['title']; ?>の履歴</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2><?php echo $material['title']; ?>の履歴</h2>
    <a href="m6_main.php"><-一覧に戻る</a>
    <hr>
    <?php if (count($history) > 0): ?>
    <?php foreach ($history as $row): ?>
        <div class="material-card">
            <p><?php echo $row['created_at']; ?></p>
            <p>メモ： <?php echo $row['memo']; ?></p>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>まだ記録がありません</p>
<?php endif; ?>
    <a href="m6_main.php"><-一覧に戻る</a>
</body>
</html>