<?php 
require_once("common.php");

if (empty(trim($_GET["token"]))) {
    $response = ["status" => "error", "message" => "Token not specified. Please try again."];
    echo json_encode($response);
    exit;
}

$token = trim($_GET["token"]);

$stmt = $pdo->prepare("SELECT * FROM users WHERE verification_code = :token");
$stmt->bindParam(":token", $token);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user['is_verified']) {
        $updateStmt = $pdo->prepare("UPDATE users SET is_verified = true WHERE verification_code = :token");
        $updateStmt->bindParam(":token", $token);
        
        if ($updateStmt->execute()) {
            $response = ["status" => "success", "message" => "Account successfully verified."];

            echo json_encode($response);
            header("Location: http://localhost:3000/home");
            exit();
        } else {
            $response = ["status" => "error", "message" => "Error updating verification status. Please try again."];
        }
    } else {
        $response = ["status" => "error", "message" => "Account is already verified."];
    }
} else {
    $response = ["status" => "error", "message" => "Invalid token. Please check the token and try again."];
}

echo json_encode($response);
exit;
?>
