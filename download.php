<?php
require_once('funcs.php');

// 本番環境データベース
$prod_db = "zouuu_zouuu_db";

// 本番環境ホスト
$prod_host = "mysql635.db.sakura.ne.jp";

// 本番環境ID
$prod_id = "zouuu";

// 本番環境PW
$prod_pw = "12345678qju";

// 2. DB接続します
try {
    // ID:'root', Password: xamppは 空白 ''
    $pdo = new PDO('mysql:dbname=' . $prod_db . ';charset=utf8;host=' . $prod_host, $prod_id, $prod_pw);
} catch (PDOException $e) {
    exit('DBConnectError:' . $e->getMessage());
}

//2. データ取得SQL作成
$stmt = $pdo->prepare("SELECT * FROM holder_table");
$status = $stmt->execute();

if ($status == false) {
    //execute（SQL実行時にエラーがある場合）
    $error = $stmt->errorInfo();
    exit("ErrorQuery:".$error[2]);
} else {
    // データをCSV形式に変換
    $csv_data = "id,name,email,created_at\n";
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $csv_data .= '"' . h($result['id']) . '","' . h($result['name']) . '","' . h($result['email']) . '","' . h($result['created_at']) . '"' . "\n";
    }

    // CSVファイルのダウンロード設定
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=member_data.csv');
    echo $csv_data;
    exit();
}
?>