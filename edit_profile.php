<?php
session_start();
require_once 'funcs.php';

// ユーザーがログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// CSRFトークンの生成
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// データベース接続
$pdo = db_conn();

// ユーザー情報の取得
$stmt = $pdo->prepare("SELECT * FROM user_table WHERE id = :id");
$stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = 'ユーザー情報の取得に失敗しました。';
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>プロフィール編集 - ZOUUU Platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .navbar-custom {
            background-color: #0c344e;
        }
        .navbar-custom .nav-link, .navbar-custom .navbar-brand {
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
                    <span class="nav-link">ようこそ <?php echo h($user['name']); ?> さん</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">ホーム</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">ログアウト</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">プロフィール編集</h1>

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
                <form action="update_profile.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                    
                    <div class="form-group">
                        <label for="name">名前：</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo h($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lid">ログインID：</label>
                        <input type="text" class="form-control" id="lid" value="<?php echo h($user['lid']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">新しいパスワード（変更する場合のみ入力）：</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">新しいパスワード（確認）：</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        <a href="index.php" class="btn btn-secondary mr-2">戻る</a>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // パスワード確認のバリデーション
        document.querySelector('form').addEventListener('submit', function(e) {
            var password = document.getElementById('new_password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('新しいパスワードと確認用パスワードが一致しません。');
            }
        });
    </script>
</body>
</html>