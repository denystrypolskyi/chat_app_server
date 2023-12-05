<?php
require_once("common.php");

$chatRoomId = $_GET["selectedChatId"];

$stmt = $pdo->prepare("SELECT * FROM messages WHERE chat_room_id = :chatRoomId");
$stmt->bindParam(":chatRoomId", $chatRoomId);
$stmt->execute();

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($messages) > 0) {
    $response = ["status" => "success", "messages" => $messages];
} else {
    $response = ["status" => "success", "messages" => []];
}

echo json_encode($response);
