<?php 
    require_once("common.php");

    if (empty(trim($_GET["userId"]))) {
        $response = ["status" => "error", "message" => "User id not specified. Please try again."];
        echo json_encode($response);
        exit;
    }


    $userId = $_GET["userId"];

    $selectStmt = $pdo->prepare("SELECT avatar FROM users WHERE id = :userId");
    $selectStmt->bindParam(":userId", $userId);

    if ($selectStmt->execute()) {
        $avatarResult = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if ($avatarResult) {
            $response = ["status" => "success", "avatar" => $avatarResult["avatar"]];
            echo json_encode($response);
            exit;
        } else {
            $response = ["status" => "error", "message" => "User not found."];
            echo json_encode($response);
            exit;
        }
    } else {
        $response = ["status" => "error", "message" => "Unexpected error. Please try again."];
        echo json_encode($response);
        exit;
    }
?>