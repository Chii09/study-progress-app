<?php
session_start();
include 'db_connect.php';

function old_edit($key, $default = '')
{
    return isset($_SESSION['old_edit'][$key])
        ? htmlspecialchars($_SESSION['old_edit'][$key], ENT_QUOTES, 'UTF-8')
        : htmlspecialchars((string) $default, ENT_QUOTES, 'UTF-8');
}

// 更新処理
if (isset($_POST['update'])) {
    $material_id = $_POST['material_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $total_amount = $_POST['total_amount'] ?? '';

    $errors = [];
    if (!ctype_digit((string) $material_id)) {
        $errors[] = '不正な教材IDです。';
    }
    if ($title === '' || mb_strlen($title) > 64) {
        $errors[] = '教材名は1〜64文字で入力してください。';
    }
    if (!ctype_digit((string) $total_amount) || (int) $total_amount <= 0) {
        $errors[] = '全体量は1以上の整数（時間）で入力してください。';
    }

    if ($errors) {
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old_edit'] = $_POST;
        header('Location: m6_edit.php?material_id=' . urlencode((string) $material_id));
        exit;
    }

    $sql = 'UPDATE materials SET title = :title, total_amount = :total_amount WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':total_amount', (int) $total_amount, PDO::PARAM_INT);
    $stmt->bindValue(':id', (int) $material_id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['flash_success'] = '教材を更新しました。';
    header('Location: m6_main.php');
    exit;
}

$material_id = $_GET['material_id'] ?? '';
if (!ctype_digit((string) $material_id)) {
    header('Location: m6_main.php');
    exit;
}

$sql = 'SELECT * FROM materials WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', (int) $material_id, PDO::PARAM_INT);
$stmt->execute();
$material = $stmt->fetch();

if (!$material) {
    header('Location: m6_main.php');
    exit;
}

$flash_errors = $_SESSION['flash_errors'] ?? [];
unset($_SESSION['flash_errors'], $_SESSION['old_edit']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>教材の編集</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h2>教材の編集</h2>
    <a href="m6_main.php"><-一覧に戻る</a>

    <?php if ($flash_errors): ?>
        <div class="flash flash-error">
            <ul>
                <?php foreach ($flash_errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="m6_edit.php">
        <input type="hidden" name="material_id" value="<?php echo (int) $material['id']; ?>">
        <label>教材名：<input type="text" name="title" value="<?php echo old_edit('title', $material['title']); ?>" maxlength="64" required></label>
        <label>全体量：<input type="number" name="total_amount" value="<?php echo old_edit('total_amount', $material['total_amount']); ?>" min="1" required>時間</label>
        <input type="submit" name="update" value="更新する">
    </form>
</body>

</html>
