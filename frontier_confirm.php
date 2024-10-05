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
        try {
            $pdo->beginTransaction();
            
            // gs_chiiki_frontierテーブルに基本情報を挿入
            $stmt = $pdo->prepare("INSERT INTO gs_chiiki_frontier (name, description, image_url, youtube_url, tags, category) VALUES (:name, :description, :image_url, :youtube_url, :tags, :category)");
            $stmt->execute([
                ':name' => $_SESSION['frontier_data']['name'],
                ':description' => $_SESSION['frontier_data']['description'],
                ':image_url' => $_SESSION['frontier_data']['image_url'],
                ':youtube_url' => $_SESSION['frontier_data']['youtube_url'],
                ':tags' => $_SESSION['frontier_data']['tags'],
                ':category' => $_SESSION['frontier_data']['category'],
            ]);
            $frontier_id = $pdo->lastInsertId();

            // frontier_detailsテーブルに詳細情報を挿入
            $stmt = $pdo->prepare("INSERT INTO frontier_details (gs_chiiki_frontier_id, company_name, prefecture, city, contact_email, interview_availability, online_lecture_availability, experiential_learning_availability, detailed_activities) VALUES (:frontier_id, :company_name, :prefecture, :city, :contact_email, :interview_availability, :online_lecture_availability, :experiential_learning_availability, :detailed_activities)");
            $stmt->execute([
                ':frontier_id' => $frontier_id,
                ':company_name' => $_SESSION['frontier_data']['company_name'],
                ':prefecture' => $_SESSION['frontier_data']['prefecture'],
                ':city' => $_SESSION['frontier_data']['city'],
                ':contact_email' => $_SESSION['frontier_data']['contact_email'],
                ':interview_availability' => $_SESSION['frontier_data']['interview_availability'],
                ':online_lecture_availability' => $_SESSION['frontier_data']['online_lecture_availability'],
                ':experiential_learning_availability' => $_SESSION['frontier_data']['experiential_learning_availability'],
                ':detailed_activities' => $_SESSION['frontier_data']['detailed_activities'],
            ]);

            // learning_contentsテーブルに学習コンテンツ情報を挿入
            $stmt = $pdo->prepare("INSERT INTO learning_contents (gs_chiiki_frontier_id, title, youtube_video_id, learning_objective, difficulty, estimated_time, inquiry_theme, inquiry_process, expected_approach, evaluation_criteria, resources, tasks) VALUES (:frontier_id, :title, :youtube_video_id, :learning_objective, :difficulty, :estimated_time, :inquiry_theme, :inquiry_process, :expected_approach, :evaluation_criteria, :resources, :tasks)");
            $stmt->execute([
                ':frontier_id' => $frontier_id,
                ':title' => $_SESSION['frontier_data']['title'],
                ':youtube_video_id' => $_SESSION['frontier_data']['youtube_video_id'],
                ':learning_objective' => $_SESSION['frontier_data']['learning_objective'],
                ':difficulty' => $_SESSION['frontier_data']['difficulty'],
                ':estimated_time' => $_SESSION['frontier_data']['estimated_time'],
                ':inquiry_theme' => $_SESSION['frontier_data']['inquiry_theme'],
                ':inquiry_process' => $_SESSION['frontier_data']['inquiry_process'],
                ':expected_approach' => $_SESSION['frontier_data']['expected_approach'],
                ':evaluation_criteria' => $_SESSION['frontier_data']['evaluation_criteria'],
                ':resources' => $_SESSION['frontier_data']['resources'],
                ':tasks' => $_SESSION['frontier_data']['tasks'],
            ]);

            $pdo->commit();

            // 成功メッセージの表示
            $_SESSION['success_message'] = '地域フロンティアが正常に登録されました。';
            header("Location: cms.php");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = 'データベースエラー: ' . $e->getMessage();
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
    <title>確認画面 - ZOUUU Platform</title>

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
    <li class="breadcrumb-item active" aria-current="page">確認画面</li>
  </ol>
</nav>

<div class="container mt-5">
    <h1 class="mb-4">確認画面</h1>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo h($error_message); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">基本情報</h5>
            <p class="card-text"><strong>名前：</strong><?php echo h($_SESSION['frontier_data']['name']); ?></p>
            <p class="card-text"><strong>説明：</strong><?php echo h($_SESSION['frontier_data']['description']); ?></p>
            <p class="card-text"><strong>タグ：</strong><?php echo h($_SESSION['frontier_data']['tags']); ?></p>
            <p class="card-text"><strong>カテゴリ：</strong><?php echo h($_SESSION['frontier_data']['category']); ?></p>
            <p class="card-text"><strong>画像URL：</strong><a href="<?php echo h($_SESSION['frontier_data']['image_url']); ?>" target="_blank">画像を見る</a></p>
            <p class="card-text"><strong>YouTube URL：</strong><?php echo h($_SESSION['frontier_data']['youtube_url']); ?></p>
        </div>

        <div class="card-body">
            <h5 class="card-title">フロンティア詳細情報</h5>
            <p class="card-text"><strong>会社名または個人名：</strong><?php echo h($_SESSION['frontier_data']['company_name']); ?></p>
            <p class="card-text"><strong>都道府県：</strong><?php echo h($_SESSION['frontier_data']['prefecture']); ?></p>
            <p class="card-text"><strong>市区町村：</strong><?php echo h($_SESSION['frontier_data']['city']); ?></p>
            <p class="card-text"><strong>担当者メールアドレス：</strong><?php echo h($_SESSION['frontier_data']['contact_email']); ?></p>
            <p class="card-text"><strong>取材可否：</strong><?php echo $_SESSION['frontier_data']['interview_availability'] ? '可' : '不可'; ?></p>
            <p class="card-text"><strong>オンライン講師可否：</strong><?php echo $_SESSION['frontier_data']['online_lecture_availability'] ? '可' : '不可'; ?></p>
            <p class="card-text"><strong>体験学習有無：</strong><?php echo $_SESSION['frontier_data']['experiential_learning_availability'] ? '有' : '無'; ?></p>
            <p class="card-text"><strong>詳細な取組内容：</strong><?php echo h($_SESSION['frontier_data']['detailed_activities']); ?></p>
        </div>

        <div class="card-body">
            <h5 class="card-title">学習コンテンツ情報</h5>
            <p class="card-text"><strong>コンテンツタイトル：</strong><?php echo h($_SESSION['frontier_data']['title']); ?></p>
            <p class="card-text"><strong>YouTubeビデオID：</strong><?php echo h($_SESSION['frontier_data']['youtube_video_id']); ?></p>
            <p class="card-text"><strong>学習目標：</strong><?php echo h($_SESSION['frontier_data']['learning_objective']); ?></p>
            <p class="card-text"><strong>難易度：</strong><?php echo h($_SESSION['frontier_data']['difficulty']); ?></p>
            <p class="card-text"><strong>推定時間：</strong><?php echo h($_SESSION['frontier_data']['estimated_time']); ?> 分</p>
            <p class="card-text"><strong>探究テーマ：</strong><?php echo h($_SESSION['frontier_data']['inquiry_theme']); ?></p>
            <p class="card-text"><strong>探究プロセス：</strong><?php echo h($_SESSION['frontier_data']['inquiry_process']); ?></p>
            <p class="card-text"><strong>期待されるアプローチ：</strong><?php echo h($_SESSION['frontier_data']['expected_approach']); ?></p>
            <p class="card-text"><strong>評価基準：</strong><?php echo h($_SESSION['frontier_data']['evaluation_criteria']); ?></p>
            <p class="card-text"><strong>リソース：</strong><?php echo h($_SESSION['frontier_data']['resources']); ?></p>
            <p class="card-text"><strong>タスク：</strong><?php echo h($_SESSION['frontier_data']['tasks']); ?></p>
        </div>
    </div>

    <form action="" method="POST" class="mt-4">
        <!-- CSRFトークンを追加 -->
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">

        <div class="form-group text-center">
            <a href="learning_contents.php" class="btn btn-secondary mr-2">戻る</a>
            <button type="submit" class="btn btn-primary">登録する</button>
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