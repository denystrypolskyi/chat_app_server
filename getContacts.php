<?php
require_once("common.php");

$loggedUserId = $_GET["loggedUserId"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id != :id"); 
$stmt->bindParam(":id", $loggedUserId);
$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0) {
    $response = ["status" => "success", "contacts" => $rows];
} else {
    $response = ["status" => "error", "message" => "No contacts found"];
}

echo json_encode($response);
