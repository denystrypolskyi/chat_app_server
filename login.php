<?php
require_once("common.php");

$email = $_POST["email"];
$password = $_POST["password"];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response = ["status" => "error", "message" => "Email is not correct. Please try again."];
    echo json_encode($response);
    die();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() == 1) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $verified = password_verify($password, $row["password"]);

    if ($verified) {
        $response = ["status" => "success", "loggedUserId" => $row["id"], "avatar" => $row["avatar"]];
    } else {
        $response = ["status" => "error", "message" => "Password is not correct. Please try again."];
    }
} else {
    $response = ["status" => "error", "message" => "User with email $email doesn't exist."];
}

echo json_encode($response);
