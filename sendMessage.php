<?php
require_once("common.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_POST["loggedUserId"]) || !isset($_POST["messageText"]) || !isset($_POST["senderAvatar"]) || !isset($_POST["chatRoomId"])) {
    $response = ["status" => "error", "message" => "Required POST variables not specified."];
    echo json_encode($response);
    exit;
}

$loggedUserId = $_POST["loggedUserId"];
$messageText = $_POST["messageText"];
$senderAvatar = $_POST["senderAvatar"];
$chatRoomId = $_POST['chatRoomId'];

if (empty($messageText)) {
    $response = ["status" => "error", "message" => "You can't send an empty message."];
    echo json_encode($response);
    exit;
}

// To prevent XSS attacks
$messageText = htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8');

$currentTime = date("Y-m-d H:i:s");

try {
    $stmt = $pdo->prepare("INSERT INTO `messages` (`sender_id`, `sender_avatar`, `message_text`, `created_at`, `chat_room_id`) VALUES (?, ?, ?, ?, ?)");

    $stmt->bindParam(1, $loggedUserId);
    $stmt->bindParam(2, $senderAvatar);
    $stmt->bindParam(3, $messageText);
    $stmt->bindParam(4, $currentTime);
    $stmt->bindParam(5, $chatRoomId);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Message sent."];
        echo json_encode($response);
        exit;
    } else {
        $response = ["status" => "error", "message" => "Failed to send message."];
        echo json_encode($response);
        exit;
    }
} catch (PDOException $e) {
    $response = ["status" => "error", "message" => "Database error: " . $e->getMessage()];
    echo json_encode($response);
    exit;
}
?>
