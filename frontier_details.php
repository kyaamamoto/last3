<?php
require_once 'admin_session_config.php';
require_once 'funcs.php';

// 管理者認証チェック
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php");
    exit();
}

$pdo = db_conn();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRFトークンの検証
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = '不正なリクエストです。';
    } else {
        $company_name = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING);
        $prefecture = filter_input(INPUT_POST, 'prefecture', FILTER_SANITIZE_STRING);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_SANITIZE_EMAIL);
        $interview_availability = filter_input(INPUT_POST, 'interview_availability', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $online_lecture_availability = filter_input(INPUT_POST, 'online_lecture_availability', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $experiential_learning_availability = filter_input(INPUT_POST, 'experiential_learning_availability', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $detailed_activities = filter_input(INPUT_POST, 'detailed_activities', FILTER_SANITIZE_STRING);

        // バリデーション部分
        if (
            empty($company_name) || empty($prefecture) || empty($city) || 
            empty($contact_email) || !filter_var($contact_email, FILTER_VALIDATE_EMAIL) || 
            $interview_availability === null || 
            $online_lecture_availability === null || $experiential_learning_availability === null || 
            empty($detailed_activities)
        ) {
            $error_message = '全ての必須項目を正しく入力してください。';
        } else {
            // セッションにデータを追加保存
            $_SESSION['frontier_data']['company_name'] = $company_name;
            $_SESSION['frontier_data']['prefecture'] = $prefecture;
            $_SESSION['frontier_data']['city'] = $city;
            $_SESSION['frontier_data']['contact_email'] = $contact_email;
            $_SESSION['frontier_data']['interview_availability'] = $interview_availability;
            $_SESSION['frontier_data']['online_lecture_availability'] = $online_lecture_availability;
            $_SESSION['frontier_data']['experiential_learning_availability'] = $experiential_learning_availability;
            $_SESSION['frontier_data']['detailed_activities'] = $detailed_activities;

            // 次のページにリダイレクト
            header("Location: learning_contents.php");
            exit();
        }
    }
}

// 新しいCSRFトークンを取得（表示用）
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>フロンティア詳細情報 - ZOUUU Platform</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
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
    <a class="navbar-brand" href="cms.php">
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

<!-- パンくずリスト -->
<nav aria-label="breadcrumb" class="mt-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="cms.php">ホーム</a></li>
    <li class="breadcrumb-item active" aria-current="page">フロンティア詳細情報</li>
  </ol>
</nav>

<div class="container mt-5">
    <h1 class="mb-4">フロンティア詳細情報</h1>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo h($error_message); ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="company_name">名前：</label>
            <input type="text" id="company_name" name="company_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="prefecture">都道府県：</label>
            <input type="text" id="prefecture" name="prefecture" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="city">市区町村：</label>
            <input type="text" id="city" name="city" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="contact_email">担当者メールアドレス：</label>
            <input type="email" id="contact_email" name="contact_email" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="interview_availability">取材可否：</label>
            <select id="interview_availability" name="interview_availability" class="form-control" required>
                <option value="1">可</option>
                <option value="0">不可</option>
            </select>
        </div>

        <div class="form-group">
            <label for="online_lecture_availability">オンライン講師可否：</label>
            <select id="online_lecture_availability" name="online_lecture_availability" class="form-control" required>
                <option value="1">可</option>
                <option value="0">不可</option>
            </select>
        </div>

        <div class="form-group">
            <label for="experiential_learning_availability">体験学習有無：</label>
            <select id="experiential_learning_availability" name="experiential_learning_availability" class="form-control" required>
                <option value="1">有</option>
                <option value="0">無</option>
            </select>
        </div>

        <div class="form-group">
            <label for="detailed_activities">詳細な取組内容：</label>
            <textarea id="detailed_activities" name="detailed_activities" class="form-control" rows="5" required></textarea>
        </div>
        
        <!-- CSRFトークンを追加 -->
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
        
        <div class="form-group text-center">
            <a href="frontier_register.php" class="btn btn-secondary mr-2">戻る</a>
            <button type="submit" class="btn btn-primary">次へ</button>
        </div>
    </form>
</div>

<footer class="footer bg-light text-center py-3 mt-4">
    <div class="container">
        <span class="text-muted">Copyright &copy; 2024 <a href="#">ZOUUU</a>. All rights reserved.</span>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
</body>
</html>