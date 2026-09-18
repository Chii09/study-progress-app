<?php
session_start();
include 'db_connect.php';

function old($key)
{
    return isset($_SESSION['old'][$key]) ? htmlspecialchars($_SESSION['old'][$key], ENT_QUOTES, 'UTF-8') : '';
}

// 1. 教材登録
if (isset($_POST['submit'])) {
    $title = trim($_POST['title'] ?? '');
    $total_amount = $_POST['total_amount'] ?? '';

    $errors = [];
    if ($title === '' || mb_strlen($title) > 64) {
        $errors[] = '教材名は1〜64文字で入力してください。';
    }
    if (!ctype_digit((string) $total_amount) || (int) $total_amount <= 0) {
        $errors[] = '全体量は1以上の整数（時間）で入力してください。';
    }

    if ($errors) {
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = $_POST;
    } else {
        $sql = "INSERT INTO materials (title, total_amount) VALUES (:title, :total_amount)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':total_amount', (int) $total_amount, PDO::PARAM_INT);
        $stmt->execute();
        $_SESSION['flash_success'] = '教材を登録しました。';
    }
    header('Location: m6_main.php');
    exit;
}

// 2. 進捗記録 （足し算方式）
if (isset($_POST['progress_submit'])) {
    $material_id = $_POST['material_id'] ?? '';
    $add_amount = $_POST['done_amount'] ?? ''; // 今日追加する分
    $memo = trim($_POST['memo'] ?? '');

    $errors = [];
    if (!ctype_digit((string) $material_id)) {
        $errors[] = '教材を選択してください。';
    }
    if (!ctype_digit((string) $add_amount) || (int) $add_amount <= 0) {
        $errors[] = '進んだ量は1以上の整数（分）で入力してください。';
    }

    if (!$errors) {
        $sql_check = 'SELECT id FROM materials WHERE id = :id';
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindValue(':id', (int) $material_id, PDO::PARAM_INT);
        $stmt_check->execute();
        if (!$stmt_check->fetch()) {
            $errors[] = '指定された教材が見つかりません。';
        }
    }

    if ($errors) {
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = $_POST;
    } else {
        // 現在の最新進捗を取得
        $sql_get = 'SELECT done_amount FROM progress WHERE material_id = :material_id ORDER BY created_at DESC LIMIT 1';
        $stmt_get = $pdo->prepare($sql_get);
        $stmt_get->bindValue(':material_id', (int) $material_id, PDO::PARAM_INT);
        $stmt_get->execute();
        $current = $stmt_get->fetch();

        // 現在の累計 + 今日の分
        $current_amount = $current ? (int) $current['done_amount'] : 0;
        $new_done_amount = $current_amount + (int) $add_amount;

        $sql = "INSERT INTO progress (material_id, done_amount, memo) VALUES (:material_id, :done_amount, :memo)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':material_id', (int) $material_id, PDO::PARAM_INT);
        $stmt->bindValue(':done_amount', $new_done_amount, PDO::PARAM_INT);
        $stmt->bindParam(':memo', $memo, PDO::PARAM_STR);
        $stmt->execute();
        $_SESSION['flash_success'] = '進捗を記録しました。';
    }
    header('Location: m6_main.php');
    exit;
}

$flash_errors = $_SESSION['flash_errors'] ?? [];
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_errors'], $_SESSION['flash_success'], $_SESSION['old']);

// 3. 一覧表示
$sql = 'SELECT * FROM materials ORDER BY created_at DESC';
$stmt = $pdo->query($sql);
$materials = $stmt->fetchAll();

$cards = [];
foreach ($materials as $row) {
    // その教材の最新進捗を取ってくる
    $sql2 = 'SELECT * FROM progress WHERE material_id = :material_id ORDER BY created_at DESC LIMIT 1';
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindValue(':material_id', $row['id'], PDO::PARAM_INT);
    $stmt2->execute();
    $progress_row = $stmt2->fetch();

    $cards[] = ['material' => $row, 'progress' => $progress_row];
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Progress App</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>学習進捗管理</h1>
    <p class="subtitle">複数の教材の進捗を記録して、積み上げを見える化する</p>

    <?php if ($flash_success): ?>
        <div class="flash flash-success"><?php echo htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($flash_errors): ?>
        <div class="flash flash-error">
            <ul>
                <?php foreach ($flash_errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label>教材名：<input type="text" name="title" value="<?php echo old('title'); ?>" maxlength="64" required></label>
        <label>全体量：<input type="number" name="total_amount" value="<?php echo old('total_amount'); ?>" min="1" required>時間</label>
        <input type="submit" name="submit" value="登録">
    </form>

    <form method="post" action="">
        <label>教材：
            <select name="material_id" required>
                <option value="">選択してください</option>
                <?php foreach ($materials as $m): ?>
                    <option value="<?php echo (int) $m['id']; ?>">
                        <?php echo htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>今日進んだ量：<input type="number" name="done_amount" min="1" required>分</label><br>
        <!-- textareaを使うことで、改行を含む文章が投稿される -->
        <label>メモ：<textarea name="memo"></textarea></label><br>
        <input type="submit" name="progress_submit" value="進捗を記録">
    </form>

    <?php foreach ($cards as $card): ?>
        <?php
        $row = $card['material'];
        $progress_row = $card['progress'];
        $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
        ?>
        <div class="material-card">
            <div class="material-card-header">
                <h3><?php echo $title; ?></h3>
                <div class="material-card-actions">
                    <a href="m6_edit.php?material_id=<?php echo (int) $row['id']; ?>">編集</a>
                    <form method="post" action="m6_delete.php" class="inline-form" onsubmit="return confirm('「<?php echo $title; ?>」を削除します。進捗記録もすべて削除されます。よろしいですか？');">
                        <input type="hidden" name="material_id" value="<?php echo (int) $row['id']; ?>">
                        <button type="submit" class="link-button">削除</button>
                    </form>
                </div>
            </div>
            <?php echo (int) $row['total_amount']; ?>時間<br>
            <?php if ($progress_row):
                $total_in_minutes = (int) $row['total_amount'] * 60; // 時間 => 分に変換
                $percent = $total_in_minutes > 0 ? min(100, round($progress_row['done_amount'] / $total_in_minutes * 100)) : 0;
                ?>
                進捗： <?php echo (int) $progress_row['done_amount']; ?>分 /
                <?php echo (int) $row['total_amount']; ?>時間 (<?php echo $percent; ?>%)<br>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width:<?php echo $percent; ?>%"></div>
                </div>
                メモ： <?php echo nl2br(htmlspecialchars((string) $progress_row['memo'], ENT_QUOTES, 'UTF-8')); ?><br>
            <?php else: ?>
                進捗： まだ記録がありません<br>
            <?php endif; ?>
            <a href="m6_history.php?material_id=<?php echo (int) $row['id']; ?>">履歴を見る</a>
        </div>
    <?php endforeach; ?>
</body>

</html>
