<?php
require_once 'admin_session_config.php';
require_once 'funcs.php';

// 管理者認証チェック
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php");
    exit();
}

$pdo = db_conn();

// カテゴリリストの定義
$categories = [
    '防災・防犯対策' => '防災・防犯対策',
    '子育て支援' => '子育て支援',
    '福祉・保健衛生' => '福祉・保健衛生',
    '環境対策' => '環境対策',
    '地域活性化' => '地域活性化',
    '人口対策' => '人口対策',
    '文化振興' => '文化振興',
    '都市基盤整備' => '都市基盤整備',
    '教育' => '教育'
];

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRFトークンの検証
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = '不正なリクエストです。';
    } else {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_STRING);
        $youtube_url = filter_input(INPUT_POST, 'youtube_url', FILTER_SANITIZE_URL);
        $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);

        // バリデーション
        if (empty($name) || empty($description) || empty($tags) || empty($category)) {
            $error_message = '全ての必須項目を入力してください。';
        } elseif (!array_key_exists($category, $categories)) {
            $error_message = '無効なカテゴリが選択されました。';
        } elseif (!empty($youtube_url) && !filter_var($youtube_url, FILTER_VALIDATE_URL)) {
            $error_message = '無効なYouTube URLです。';
        } else {
            // 画像アップロード処理
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = array('jpg', 'jpeg', 'png', 'gif');
                $filename = $_FILES['image']['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!in_array($ext, $allowed)) {
                    $error_message = '許可されていないファイル形式です。';
                } else {
                    $upload_dir = './uploads/';
                    $image_name = uniqid() . '_' . $filename;
                    $upload_file = $upload_dir . $image_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                        $image_url = $upload_file;
                    } else {
                        $error_message = '画像のアップロードに失敗しました。';
                    }
                }
            } else {
                $error_message = '画像をアップロードしてください。';
            }

            if (empty($error_message)) {
                // セッションにデータを保存
                $_SESSION['frontier_data'] = [
                    'name' => $name,
                    'description' => $description,
                    'image_url' => $image_url,
                    'youtube_url' => $youtube_url,
                    'tags' => $tags,
                    'category' => $category
                ];

                // 次のページにリダイレクト
                header("Location: frontier_details.php");
                exit();
            }
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
    <title>地域フロンティア登録 - ZOUUU Platform</title>

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
                <a class="nav-link" href="logoutmypage.php">ログアウト</a>
            </li>
        </ul>
    </div>
</nav>

<!-- パンくずリスト -->
<nav aria-label="breadcrumb" class="mt-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="cms.php">ホーム</a></li>
    <li class="breadcrumb-item active" aria-current="page">地域フロンティア登録</li>
  </ol>
</nav>

<div class="container mt-5">
    <h1 class="mb-4">地域フロンティア登録</h1>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo h($error_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo h($success_message); ?></div>
    <?php endif; ?>


    <form action="" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">会社名・屋号もしくは名前：</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="description">説明：</label>
        <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
    </div>
    
    <div class="form-group">
        <label for="image">画像：</label>
        <input type="file" id="image" name="image" class="form-control-file" accept="image/*" required>
    </div>
    
    <div class="form-group">
        <label for="youtube_url">YouTube URL：</label>
        <input type="url" id="youtube_url" name="youtube_url" class="form-control">
    </div>
    
    <div class="form-group">
        <label for="tags">タグ（カンマ区切り）：</label>
        <input type="text" id="tags" name="tags" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="category">カテゴリ：</label>
        <select id="category" name="category" class="form-control" required>
            <option value="">カテゴリーを選択してください</option>
            <?php foreach ($categories as $value => $label): ?>
                <option value="<?php echo h($value); ?>"><?php echo h($label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- CSRFトークンを追加 -->
    <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
    
    <div class="form-group text-center">
        <a href="cms.php" class="btn btn-secondary mr-2">戻る</a>
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