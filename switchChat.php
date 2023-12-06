<?php
require_once("common.php");

$user1Id = $_GET["user1Id"];
$user2Id = $_GET["user2Id"];

if (!is_numeric($user1Id) || !is_numeric($user2Id)) {
    $response = ["status" => "error", "message" => "Invalid user IDs"];
    echo json_encode($response);
    exit();
}

try {
    $selectStmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE (user1_id = :user1Id AND user2_id = :user2Id) OR (user1_id = :user2Id AND user2_id = :user1Id)");
    $selectStmt->bindParam(":user1Id", $user1Id);
    $selectStmt->bindParam(":user2Id", $user2Id);
    $selectStmt->execute();

    $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $response = ["status" => "success", "selectedChatId" => $row["id"]];
        echo json_encode($response);
        exit;
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO chat_rooms (user1_id, user2_id) VALUES (:user1Id, :user2Id)");
        $insertStmt->bindParam(":user1Id", $user1Id);
        $insertStmt->bindParam(":user2Id", $user2Id);

        if ($insertStmt->execute()) {
            $newChatStmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE (user1_id = :user1Id AND user2_id = :user2Id) OR (user1_id = :user2Id AND user2_id = :user1Id)");
            $newChatStmt->bindParam(":user1Id", $user1Id);
            $newChatStmt->bindParam(":user2Id", $user2Id);
            $newChatStmt->execute();

            $row = $newChatStmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $response = ["status" => "success", "message" => "The new chat has been successfully created", "selectedChatId" => $row["id"]];
                echo json_encode($response);
                exit;
            } else {
                $response = ["status" => "error", "message" => "Failed to retrieve the new chat room ID."];
                echo json_encode($response);
                exit;
            }
        } else {
            $response = ["status" => "error", "message" => "Failed to create the chat room."];
            echo json_encode($response);
            exit;
        }
    }
} catch (PDOException $e) {
    $response = ["status" => "error", "message" => "Database error: " . $e->getMessage()];
    echo json_encode($response);
    exit;
}
?>
