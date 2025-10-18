<?php
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $chat_id = $input['chat_id'];
    $user_id = $input['user_id'];
    $message_text = $input['message_text'];
    $message_type = $input['message_type'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (chat_id, sender_id, message_text, message_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$chat_id, $user_id, $message_text, $message_type]);
        
        $message_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message_id' => $message_id
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
?>