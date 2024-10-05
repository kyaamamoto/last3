<?php
require_once 'admin_session_config.php';
require_once 'funcs.php';

// 管理者認証チェック
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php");
    exit();
}

// 一般的なログインチェック（loginCheck関数を使用）
loginCheck();

// データベース接続
$pdo = db_conn();

// 地域フロンティアの一覧を取得
$stmt = $pdo->prepare("SELECT * FROM gs_chiiki_frontier ORDER BY created_at DESC");
$status = $stmt->execute();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZOUUU Platform - 地域フロンティア一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .navbar-custom {
            background-color: #0c344e;
        }
        .navbar-custom .nav-link, .navbar-custom .navbar-brand {
            color: white;
        }
        .centered-button {
            display: flex;
            justify-content: center;
        }
        .centered-button .btn {
            background-color: #d3d3d3; /* グレー */
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
                <span class="nav-link">ようこそ <?php echo htmlspecialchars($_SESSION['name']); ?> さん</span>
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
    <h2>地域フロンティア一覧</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>画像</th>
                <th>名前</th>
                <th>説明</th>
                <th>カテゴリ</th>
                <th>タグ</th>
                <th>作成日時</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($status == false) {
            sql_error($stmt);
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
            <tr>
                <td><?php echo h($row['id']); ?></td>
                <td><img src="<?php echo h($row['image_url']); ?>" alt="<?php echo h($row['name']); ?>" style="height:50px;"></td>
                <td><?php echo h($row['name']); ?></td>
                <td><?php echo h(mb_substr($row['description'], 0, 50)) . '...'; ?></td>
                <td><?php echo h($row['category']); ?></td>
                <td><?php echo h($row['tags']); ?></td>
                <td><?php echo h($row['created_at']); ?></td>
            </tr>
        <?php
            }
        }
        ?>
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-3">
            <a href="cms.php" class="btn btn-secondary mr-2">戻る</a>
        </div>
</div>

<footer class="footer bg-light text-center py-3 mt-4">
    <div class="container">
        <span class="text-muted">Copyright &copy; 2024 <a href="#">ZOUUU</a>. All rights reserved.</span>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>