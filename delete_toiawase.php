<?php
require_once 'admin_session_config.php';
require_once 'funcs.php';

// 管理者認証チェック
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php");
    exit();
}

// POSTデータ取得
$id = $_POST['id'] ?? null;
$csrf_token = $_POST['csrf_token'] ?? '';

// CSRFトークンの検証
if ($csrf_token !== $_SESSION['csrf_token']) {
    exit("不正なリクエストです。");
}

if ($id) {
    // データベース接続
    $pdo = db_conn();

    // データ削除SQL
    $stmt = $pdo->prepare("DELETE FROM toiawase_table WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $status = $stmt->execute();

    // エラー処理
    if ($status == false) {
        $error = $stmt->errorInfo();
        exit("QueryError: " . $error[2]);
    } else {
        // 新しいCSRFトークンを生成
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        header("Location: toiawase_table.php");
        exit();
    }
} else {
    exit("不正なアクセスです。");
}
?>