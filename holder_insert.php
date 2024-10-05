<?php
session_start();
require_once 'funcs.php';

validateToken($_POST['csrf_token'] ?? '');

$name = sanitize($_POST["name"] ?? '');
$email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
$lpw = $_POST["lpw"] ?? '';

if (empty($name) || empty($email) || empty($lpw)) {
    $_SESSION['registration_error'] = "全ての項目を入力してください。";
    redirect('mypage_entry.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['registration_error'] = "有効なメールアドレスを入力してください。";
    redirect('mypage_entry.php');
}

try {
    $pdo = db_conn();
    
    // メールアドレスの重複チェック
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM holder_table WHERE email = :email");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['registration_error'] = "このメールアドレスは既に使用されています。";
        redirect('mypage_entry.php');
    }

    // データ登録SQL作成
    $sql = "INSERT INTO holder_table (name, email, lpw, life_flg) VALUES (:name, :email, :lpw, :life_flg)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':lpw', password_hash($lpw, PASSWORD_DEFAULT), PDO::PARAM_STR);
    $stmt->bindValue(':life_flg', 0, PDO::PARAM_INT);

    $status = $stmt->execute();

    if ($status === false) {
        throw new PDOException($stmt->errorInfo()[2]);
    } else {
        $_SESSION['registration_success'] = true;
        redirect('login_holder.php');
    }
} catch (PDOException $e) {
    error_log("登録エラー: " . $e->getMessage());
    $_SESSION['registration_error'] = "登録中にエラーが発生しました。管理者にお問い合わせください。";
    redirect('mypage_entry.php');
}
?>