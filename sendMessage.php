<?php
require_once("common.php");

$data = json_decode(file_get_contents("php://input"), true);

$loggedUserId = $_POST["loggedUserId"];
$messageText = $_POST["messageText"];
$senderAvatar = $_POST["senderAvatar"];
$chatRoomId = $_POST['chatRoomId'];

if (empty($messageText)) {
    $response = ["status" => "error", "message" => "You can't send an empty message."];
    echo json_encode($response);
    die();
}

$currentTime = date("Y-m-d H:i:s");

$stmt = $pdo->prepare("INSERT INTO `messages` (`sender_id`, `sender_avatar`, `message_text`, `created_at`, `chat_room_id`) VALUES (?, ?, ?, ?, ?)");

if ($stmt) {
    $stmt->bindParam(1, $loggedUserId);
    $stmt->bindParam(2, $senderAvatar);
    $stmt->bindParam(3, $messageText);
    $stmt->bindParam(4, $currentTime);
    $stmt->bindParam(5, $chatRoomId);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Message sent."];
    } else {
        $response = ["status" => "error", "message" => "Failed to insert the message."];
    }
} else {
    $response = ["status" => "error", "message" => "Failed to prepare the statement."];
}

echo json_encode($response);
