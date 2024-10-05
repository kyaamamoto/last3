<?php
session_start();
require_once 'funcs.php';

$receiver_id = filter_input(INPUT_GET, 'receiver_id', FILTER_SANITIZE_NUMBER_INT);
$sender_id = $_SESSION['user_id'] ?? null; // 管理者のユーザーID

if (!$receiver_id || !$sender_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Receiver ID or Sender ID is missing.']);
    exit;
}

try {
    $pdo = db_conn();
    
    $query = "
        SELECT cm.*, 
               CASE 
                   WHEN cm.is_admin = 1 THEN u.name  -- 管理者の名前を取得
                   ELSE h.name  -- ユーザーの名前を取得
               END AS sender_name
        FROM chat_messages cm
        LEFT JOIN user_table u ON cm.sender_id = u.id AND cm.is_admin = 1  -- 管理者の場合のみ
        LEFT JOIN holder_table h ON cm.sender_id = h.id AND cm.is_admin = 0  -- ユーザーの場合のみ
        WHERE (cm.sender_id = :sender_id AND cm.receiver_id = :receiver_id) 
           OR (cm.sender_id = :receiver_id AND cm.receiver_id = :sender_id)
        ORDER BY cm.created_at ASC
        LIMIT 100
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':sender_id' => $sender_id, ':receiver_id' => $receiver_id]);
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output = '';
    foreach ($messages as $message) {
        $output .= "<div class='message'>";
        $output .= "<strong>" . htmlspecialchars($message['sender_name']) . ":</strong> ";
        $output .= nl2br(htmlspecialchars($message['message'])) . " <small>(" . htmlspecialchars($message['created_at']) . ")</small>";
        $output .= "</div>";
    }
    
    echo json_encode(['success' => true, 'html' => $output]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching messages: ' . $e->getMessage()]);
}
?>