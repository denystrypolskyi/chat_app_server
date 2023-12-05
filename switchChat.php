<?php
require_once("common.php");

$user1Id = $_GET["user1Id"];
$user2Id = $_GET["user2Id"];

$stmt = $pdo->prepare("SELECT * FROM chat_rooms WHERE (user1_id = :user1Id AND user2_id = :user2Id) OR (user1_id = :user2Id AND user2_id = :user1Id)");
$stmt->bindParam(":user1Id", $user1Id);
$stmt->bindParam(":user2Id", $user2Id);
$stmt->execute();

if ($stmt->rowCount() === 1) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $response = ["status" => "success", "selectedChatId" => $row["id"]];
} else {
    $insertStmt = $pdo->prepare("INSERT INTO chat_rooms (user1_id, user2_id) VALUES (:user1Id, :user2Id)");
    $insertStmt->bindParam(":user1Id", $user1Id);
    $insertStmt->bindParam(":user2Id", $user2Id);
    $insertStmt->execute();

    $newChatStmt = $pdo->prepare("SELECT * FROM chat_rooms WHERE (user1_id = :user1Id AND user2_id = :user2Id) OR (user1_id = :user2Id AND user2_id = :user1Id)");
    $newChatStmt->bindParam(":user1Id", $user1Id);
    $newChatStmt->bindParam(":user2Id", $user2Id);
    $newChatStmt->execute();

    $row = $newChatStmt->fetch(PDO::FETCH_ASSOC);
    $response = ["status" => "success", "selectedChatId" => $row["id"]];
}

echo json_encode($response);
