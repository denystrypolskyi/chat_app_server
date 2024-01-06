<?php 
    require_once("common.php");

    if (empty(trim($_POST["userId"]))) {
        $response = ["status" => "error", "message" => "User id not specified. Please try again."];
        echo json_encode($response);
        exit;
    }

    $userId = $_POST["userId"];

    $file = $_FILES["uploadedFile"];
    $allowedExtensions = array("jpg", "jpeg", "png");

    $fileName = $file["name"];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    if (!in_array($fileExtension, $allowedExtensions)) {
        $response = ["status" => "error", "message" => "Invalid file format. Allowed formats: jpg/jpeg."];
        echo json_encode($response);
        exit;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $response = ["status" => "error", "message" => "Error during file upload. Please try again."];
        echo json_encode($response);
        exit;
    }

    $destination = "../client/src/assets/img/" . $fileName;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        $response = ["status" => "error", "message" => "Failed to upload the file. Please try again."];
        echo json_encode($response);
        exit;
    }

    $updateStmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :userId");    
    $updateStmt->bindParam(":avatar", $fileName);
    $updateStmt->bindParam(":userId", $userId);
    $updateStmt->execute();

    $updateStmt = $pdo->prepare("UPDATE messages SET sender_avatar = :avatar WHERE sender_id = :userId");    
    $updateStmt->bindParam(":avatar", $fileName);
    $updateStmt->bindParam(":userId", $userId);
    
    if ($updateStmt->execute()) {
        $response = ["status" => "success", "message" => "Avatar successfully updated."];
        echo json_encode($response);
        exit;
    } else {
        $response = ["status" => "error", "message" => "Unexpected error. Please try again."];
        echo json_encode($response);
        exit;
    }
?>