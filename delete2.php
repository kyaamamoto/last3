<?php
require_once 'admin_session_config.php';
require_once 'funcs.php';

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

// デバッグログファイル
$logFile = 'debug_log.txt';

function debugLog($message) {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

debugLog("Script started");

// 管理者認証チェック
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    debugLog("Authentication failed");
    header("Location: login.php");
    exit();
}

debugLog("Authentication passed");

// CSRFトークンの検証
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    debugLog("CSRF token validation failed");
    $_SESSION['error_message'] = '不正なリクエストです。';
    header("Location: user_table.php");
    exit();
}

debugLog("CSRF token validated");

// データベース接続
try {
    $pdo = db_conn();
    debugLog("Database connection successful");
} catch (PDOException $e) {
    debugLog("Database connection failed: " . $e->getMessage());
    $_SESSION['error_message'] = 'データベース接続エラー: ' . $e->getMessage();
    header("Location: user_table.php");
    exit();
}

// IDの取得と検証
$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;

if ($id === false || $id === null) {
    debugLog("Invalid ID");
    $_SESSION['error_message'] = '無効なIDです。';
    header("Location: user_table.php");
    exit();
}

debugLog("Valid ID: " . $id);

// トランザクション開始
$pdo->beginTransaction();

try {
    // value_registration テーブルのレコードを削除
    $stmt = $pdo->prepare("DELETE FROM value_registration WHERE user_id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // notification_recipients テーブルのレコードを削除
    $stmt = $pdo->prepare("DELETE FROM notification_recipients WHERE user_id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // 子テーブル（future_involvement）のレコードを削除
    $stmt = $pdo->prepare("DELETE FROM future_involvement WHERE user_id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // 子テーブル（past_involvement）のレコードを削除
    $stmt = $pdo->prepare("DELETE FROM past_involvement WHERE user_id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // 子テーブル（skill_check）のレコードを削除
    $stmt = $pdo->prepare("DELETE FROM skill_check WHERE user_id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    // 親テーブル（holder_table）のレコードを削除
    $stmt = $pdo->prepare("DELETE FROM holder_table WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    // トランザクションをコミット
    $pdo->commit();
    
    // 削除後、select.phpにリダイレクト
    header('Location: select.php');
    exit();
} catch (PDOException $e) {
    // エラーが発生した場合、ロールバックしてエラーメッセージを表示
    $pdo->rollBack();
    exit('クエリエラー: ' . $e->getMessage());
}
?>