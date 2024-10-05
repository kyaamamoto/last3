<?php
require_once 'funcs.php';

// データベース接続
$pdo = db_conn();

// NG日程を取得
$stmt = $pdo->query("SELECT date, start_time, end_time FROM unavailable_slots");
$unavailable_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// レスポンスをJSON形式で返す
header('Content-Type: application/json');
echo json_encode($unavailable_slots);
?>