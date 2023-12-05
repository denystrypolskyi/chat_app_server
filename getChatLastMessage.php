<?php
require_once("common.php");

$user1Id = $_GET['user1Id'];
$user2Id = $_GET['user2Id'];

$stmt = $pdo->prepare("SELECT * FROM chat_rooms
    WHERE (user1_id = :user1Id AND user2_id = :user2Id) OR (user1_id = :user2Id AND user2_id = :user1Id)");

$stmt->bindParam(':user1Id', $user1Id, PDO::PARAM_INT);
$stmt->bindParam(':user2Id', $user2Id, PDO::PARAM_INT);

$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $chatRoomId = $result['id'];

    $query = "SELECT message_text, DATE_FORMAT(created_at, '%d/%m %H:%i') AS lastMessageSentAt
              FROM messages
              WHERE chat_room_id = :chatRoomId
              ORDER BY created_at DESC
              LIMIT 1";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":chatRoomId", $chatRoomId, PDO::PARAM_INT);
    $stmt->execute();

    $messageResult = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($messageResult) {
        $response = ["status" => "success", "lastMessage" => $messageResult["message_text"], "lastMessageSentAt" => $messageResult['lastMessageSentAt']];
    } else {
        $response = [
            "status" => "error", "message" => "I couldn't find the last message. Maybe the chat history is clear?"
        ];
    }
} else {
    $response = [
        "status" => "error", "message" => "Unknown error"
    ];
}

echo json_encode($response);
