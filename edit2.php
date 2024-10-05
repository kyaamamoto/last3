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
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === null) {
    $_SESSION['error_message'] = 'IDが指定されていません';
    header("Location: user_table.php");
    exit();
}

// データ取得SQL作成
$sql = "SELECT * FROM holder_table WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

// データ取得
if ($status === false) {
    $error = $stmt->errorInfo();
    $_SESSION['error_message'] = "データ取得エラー: " . $error[2];
    header("Location: user_table.php");
    exit();
} else {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $_SESSION['error_message'] = '指定されたIDのデータが見つかりません';
        header("Location: user_table.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ユーザー情報更新 - ZOUUU Platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .navbar-custom {
            background-color: #0c344e;
        }
        .navbar-custom .nav-link, .navbar-custom .navbar-brand {
            color: white;
        }
        .thead-custom {
            background-color: #0c344e;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <a class="navbar-brand" href="#">
            <img src="./img/ZOUUU.png" alt="ZOUUU Logo" class="d-inline-block align-top" height="30">
            ZOUUU Platform
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="nav-link">ようこそ <?php echo h($_SESSION['name']); ?> さん</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cms.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">ログアウト</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">ユーザー情報更新</h1>

        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . h($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . h($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        ?>

        <div class="card">
            <div class="card-body">
                <form action="update_profile.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id" value="<?= h($result['id']) ?>">
                    
                    <div class="form-group">
                        <label for="name">お名前：</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= h($result['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">メールアドレス：</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= h($result['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lpw">パスワード（変更する場合のみ入力）：</label>
                        <input type="password" class="form-control" id="lpw" name="lpw">
                    </div>
                    
                    <div class="form-group">
                        <label for="life_flg">アカウント状態：</label>
                        <select class="form-control" id="life_flg" name="life_flg">
                            <option value="0" <?= $result['life_flg'] == 0 ? 'selected' : '' ?>>有効</option>
                            <option value="1" <?= $result['life_flg'] == 1 ? 'selected' : '' ?>>無効</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        <a href="select.php" class="btn btn-secondary mr-2">戻る</a>
                        <button type="submit" class="btn btn-primary">更新</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer bg-light text-center py-3 mt-4">
        <div class="container">
            <span class="text-muted">Copyright &copy; 2024 <a href="#">ZOUUU</a>. All rights reserved.</span>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>