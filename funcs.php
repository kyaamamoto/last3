<?php
//共通に使う関数を記述

// XSS対応（echoする場所で使用！それ以外はNG）
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// データベース接続関数
function db_conn() {
    // エラーレポートを有効にする
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // 本番環境では0に設定
    ini_set('log_errors', 'On');
    ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/error.log'); // 適切なパスを指定

    // データベース接続情報
    $dsn = 'mysql:dbname=zouuu_zouuu_db;host=mysql635.db.sakura.ne.jp;charset=utf8mb4';
    $user = 'zouuu';
    $password = '12345678qju';

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("データベース接続失敗: " . $e->getMessage());
        exit("データベース接続エラーが発生しました。管理者にお問い合わせください。");
    }
}

// ログインチェック関数
function loginCheck() {
    // セッションが開始されていない場合は開始
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        // ログインしていない場合はエラーメッセージをセットしてリダイレクト
        $_SESSION['error_message'] = "ログインしてください。";
        header("Location: login_holder.php");
        exit();
    } else {
        // ログインしている場合はセッションIDを再生成
        // ただし、頻繁な再生成を避けるため、最後の再生成から5分以上経過している場合のみ実行
        if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// CSRF対策用トークン生成関数
function generateToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF対策用トークン検証関数
function validateToken($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        exit("不正なリクエストです。");
    }
}

// 入力値のサニタイズ関数
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// リダイレクト関数
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// ユーザー情報取得関数
function getUserInfo($user_id) {
    $pdo = db_conn();
    $stmt = $pdo->prepare("SELECT * FROM holder_table WHERE id = :id");
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ふるさとIDランク取得関数（仮の実装）
function getFurusatoIdRanks($user_id) {
    // 実際のデータベースクエリに置き換えてください
    return [
        '旅行・観光' => 'ゴールド',
        '食' => 'レギュラー',
        'まちづくり' => 'プラチナ',
        '地場産業' => 'シルバー'
    ];
}

// 申請状況取得関数（仮の実装）
function getApplicationStatus($user_id) {
    // 実際のデータベースクエリに置き換えてください
    return '審査中';
}

// カテゴリーの取得
function getCategories($pdo) {
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM gs_chiiki_frontier");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ユーザーの特定のフィールドを更新
function updateUserField($pdo, $user_id, $field, $value) {
    $stmt = $pdo->prepare("UPDATE holder_table SET $field = :value WHERE id = :user_id");
    $stmt->execute([':value' => $value, ':user_id' => $user_id]);
}

// カテゴリー別進捗の計算
function calculateCategoryProgress($frontierProgress) {
    $categoryProgress = [];
    foreach ($frontierProgress as $frontier) {
        $category = $frontier['category'];
        if (!isset($categoryProgress[$category])) {
            $categoryProgress[$category] = ['total' => 0, 'completed' => 0];
        }
        $categoryProgress[$category]['total']++;
        if ($frontier['status'] == 'completed') {
            $categoryProgress[$category]['completed']++;
        }
    }
    return $categoryProgress;
}

?>