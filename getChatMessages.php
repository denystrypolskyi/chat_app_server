<?php
require_once("common.php");

try {
    if (!isset($_GET["selectedChatId"])) {
        $response = [
            "status" => "error",
            "message" => "selectedChatId not specified in the request",
            "messages" => []
        ];
        echo json_encode($response);
        exit;
    }

    $chatRoomId = $_GET["selectedChatId"];

    $stmt = $pdo->prepare("SELECT * FROM messages WHERE chat_room_id = :chatRoomId");
    $stmt->bindParam(":chatRoomId", $chatRoomId);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $messageCount = count($messages);

    $response = [
        "status" => "success",
        "messages" => $messageCount > 0 ? $messages : [],
        "message" => $messageCount > 0 ? "" : "No messages yet"
    ];
    echo json_encode($response);
    exit;
} catch (PDOException $e) {
    $response = [
        "status" => "error",
        "message" => "Database error: " . $e->getMessage(),
        "messages" => []
    ];
    echo json_encode($response);
    exit;
}
?>
