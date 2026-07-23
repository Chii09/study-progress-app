<?php
include 'db_connect.php';

// 1. 教材登録
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $total_amount = $_POST['total_amount'];

    $sql = "INSERT INTO materials (title, total_amount) VALUES (:title, :total_amount)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_INT);
    $stmt->execute();
}

// 2. 進捗記録 （足し算方式）
if (isset($_POST['progress_submit'])) {
    $material_id = $_POST['material_id'];
    $add_amount = $_POST['done_amount']; // 今日追加する分
    $memo = $_POST['memo'];

    // 現在の最新進捗を取得
    $sql_get = 'SELECT done_amount FROM progress WHERE material_id = :material_id ORDER BY created_at DESC LIMIT 1';
    $stmt_get = $pdo->prepare($sql_get);
    $stmt_get->bindParam(':material_id', $material_id, PDO::PARAM_INT);
    $stmt_get->execute();
    $current = $stmt_get->fetch();

    // 現在の累計 + 今日の分
    $current_amount = $current ? $current['done_amount'] : 0;
    $new_done_amount = $current_amount + $add_amount;

    $sql = "INSERT INTO progress (material_id, done_amount, memo) VALUES (:material_id, :done_amount, :memo)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':material_id', $material_id, PDO::PARAM_INT);
    $stmt->bindParam(':done_amount', $new_done_amount, PDO::PARAM_INT);
    $stmt->bindParam(':memo', $memo, PDO::PARAM_STR);
    $stmt->execute();
}

// 2. 一覧表示
$sql = 'SELECT * FROM materials';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
foreach ($results as $row) {
    // その教材の最新進捗を取ってくる
    $sql2 = 'SELECT * FROM progress WHERE material_id = :material_id ORDER BY created_at DESC LIMIT 1';
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindParam(':material_id', $row['id'], PDO::PARAM_INT);
    $stmt2->execute();
    $progress_row = $stmt2->fetch();

    if ($progress_row) {
        $total_in_minutes = $row['total_amount'] * 60; // 時間 => 分に変換
        $percent = round($progress_row['done_amount'] / $total_in_minutes * 100);
        echo '<div class="material-card">';
        echo '<h3>' . $row['title'] . '</h3>';
        echo $row['total_amount'] . '時間<br>';
        echo '進捗： ' . $progress_row['done_amount'] . '分 / ';
        echo $row['total_amount'] . '時間 (' . $percent . '%)<br>';
        // 進捗バー
        echo '<div class="progress-bar-bg">';
        echo '<div class="progress-bar-fill" style="width:' . $percent . '%"></div>';
        echo '</div>';
        echo 'メモ： ' . $progress_row['memo'] . '<br>';
        echo '<a href="m6_history.php?material_id=' . $row['id'] . '">履歴を見る</a>';
        echo '</div>';
    } else {
        echo '<div class="material-card">';
        echo '<h3>' . $row['title'] . '</h3>';
        echo $row['total_amount'] . '時間<br>';
        echo '進捗： まだ記録がありません<br>';
        echo '<a href="m6_history.php?material_id=' . $row['id'] . '">履歴を見る</a>';
        echo '</div>';
    }

    echo "<hr>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Progress App</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>学習進捗管理</h1>
    <p class="subtitle">複数の教材の進捗を記録して、積み上げを見える化する</p>
    <form method="post" action="">
        教材名：<input type="text" name="title">
        全体量：<input type="number" name="total_amount">
        <input type="submit" name="submit" value="登録">
    </form>
    <form method="post" action="">
        教材ID：<input type="number" name="material_id"><br>
        今日進んだ量：<input type="number" name="done_amount">分<br>
        <!-- textareaを使うことで、改行を含む文章が投稿される -->
        メモ：<textarea name="memo"></textarea><br>
        <input type="submit" name="progress_submit" value="進捗を記録">
    </form>
</body>

</html>