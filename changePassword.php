<?php 
    require_once("common.php");

    if (empty($_POST["userId"]) || empty($_POST["currentPassword"]) || empty($_POST["newPassword"]) || empty($_POST["repeatNewPassword"])) {
        $response = ["status" => "error", "message" => "All fields are required."];
        echo json_encode($response);
        exit();
    }

    $userId = $_POST["userId"];
    $currentPassword = $_POST["currentPassword"];
    $newPassword = $_POST["newPassword"];
    $repeatNewPassword = $_POST["repeatNewPassword"];
    
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $response = ["status" => "error", "message" => "Incorrect current password."];
            echo json_encode($response);
            exit();
        }
    
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
    
        $response = ["status" => "success", "message" => "Your password has been updated."];
        echo json_encode($response);
        exit();
    }
    catch (PDOException $e) {
        $response = ["status" => "error", "message" => "Database error: " . $e->getMessage()];
        echo json_encode($response);
        exit();
    }
?>