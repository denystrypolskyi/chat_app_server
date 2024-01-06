<?php
require_once("common.php");

if (!isset($_POST["email"]) || !isset($_POST["password"])) {
    $response = ["status" => "error", "message" => "Missing email or password in the request."];
    echo json_encode($response);
    exit;
}

$email = $_POST["email"];
$password = $_POST["password"];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response = ["status" => "error", "message" => "Email is not correct. Please try again."];
    echo json_encode($response);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row["is_verified"]) {
            $verified = password_verify($password, $row["password"]);

            if ($verified) {
                $response = ["status" => "success", "loggedUserId" => $row["id"], "avatar" => $row["avatar"]];
                echo json_encode($response);
                exit;
            } else {
                $response = ["status" => "error", "message" => "Password is not correct. Please try again."];
                echo json_encode($response);
                exit;
            }
        } else {
            $response = ["status" => "error", "message" => "Account not verified. Please activate your account through the received email."];
            echo json_encode($response);
            exit;
        }
    } else {
        $response = ["status" => "error", "message" => "User with email $email doesn't exist."];
        echo json_encode($response);
        exit;
    }
} catch (PDOException $e) {
    $response = ["status" => "error", "message" => "Database error: " . $e->getMessage()];
    echo json_encode($response);
    exit;
}
?>
