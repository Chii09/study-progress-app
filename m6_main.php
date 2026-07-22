<?php
include 'db_connect.php';

// 1. 登録処理
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $total_amount = $_POST['total_amount'];

    $sql = "INSERT INTO materials (title, total_amount) VALUES (:title, :total_amount)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_INT);
    $stmt->execute();
}

// 2. 一覧表示
$sql = 'SELECT * FROM materials';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
foreach ($results as $row) {
    echo $row['id'] . ',';
    echo $row['title'] . ',';
    echo $row['total_amount'] . '<br>';
    echo "<hr>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Progress App</title>
</head>
<body>
    <form method = "post" action="">
        教材名：<input type="text" name="title">
        全体量：<input type="number" name="total_amount">
        <input type="submit" name="submit" value="登録">
</form>
</body>
</html>
