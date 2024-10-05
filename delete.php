<?php
require_once('funcs.php');

// 本番環境データベース
$prod_db = "zouuu_zouuu_db";

// 本番環境ホスト
$prod_host = "mysql635.db.sakura.ne.jp";

// 本番環境ID
$prod_id = "zouuu";

// 本番環境PW
$prod_pw = "パスワード";



// DB接続
try {
    $pdo = new PDO('mysql:dbname=' . $prod_db . ';charset=utf8;host=' . $prod_host, $prod_id, $prod_pw);
} catch (PDOException $e) {
    exit('DBConnectError:' . $e->getMessage());
}

// GETパラメータからIDを取得
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id === null) {
    exit('IDが指定されていません');
}

// SQL文を作成
$table_name = "holder_table"; // 実際のテーブル名に置き換えてください
$sql = "DELETE FROM $table_name WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

// SQL実行
$status = $stmt->execute();

// 結果確認
if ($status === false) {
    $error = $stmt->errorInfo();
    exit("QueryError:" . $error[2]);
} else {
    // 一覧ページにリダイレクト
    header("Location: select.php");
    exit;
}
?>