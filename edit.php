<?php
require_once 'admin_session_config.php';
require_once 'funcs.php';

// 管理者認証チェック
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php");
    exit();
}

// CSRFトークンの生成
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// データベース接続
$pdo = db_conn();

// GETパラメータからIDを取得
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if ($id === false || $id === null) {
    exit('無効なIDです');
}

// データ取得SQL作成
$sql = "SELECT * FROM holder_table WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

// データ取得
if ($status === false) {
    $error = $stmt->errorInfo();
    exit("ErrorQuery:" . $error[2]);
} else {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        exit('指定されたIDのデータが見つかりません');
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ユーザー情報編集</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">ユーザー情報編集</h1>
        <form action="update2.php" method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" value="<?= h($result['id']) ?>">
            
            <div class="form-group">
                <label for="name">お名前: <span class="required">必須</span></label>
                <input type="text" class="form-control" id="name" name="name" value="<?= h($result['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">メールアドレス: <span class="required">必須</span></label>
                <input type="email" class="form-control" id="email" name="email" value="<?= h($result['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="lpw">パスワード: <span class="optional">任意</span></label>
                <input type="password" class="form-control" id="lpw" name="lpw" placeholder="変更する場合のみ入力してください">
            </div>
            
            <div class="form-group">
                <label>管理者権限: <span class="required">必須</span></label>
                <div>
                    <label class="mr-3">
                        <input type="radio" name="kanri_flg" value="0" <?= $result['kanri_flg'] == 0 ? 'checked' : '' ?>> 一般
                    </label>
                    <label>
                        <input type="radio" name="kanri_flg" value="1" <?= $result['kanri_flg'] == 1 ? 'checked' : '' ?>> 管理者
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="life_flg">アカウント状態: <span class="required">必須</span></label>
                <select class="form-control" id="life_flg" name="life_flg" required>
                    <option value="0" <?= $result['life_flg'] == 0 ? 'selected' : '' ?>>有効</option>
                    <option value="1" <?= $result['life_flg'] == 1 ? 'selected' : '' ?>>無効</option>
                </select>
            </div>
            
            <div class="form-group">
                <a href="user_table.php" class="btn btn-secondary">戻る</a>
                <button type="submit" class="btn btn-primary">更新</button>
            </div>
        </form>
    </div>
</body>
</html>