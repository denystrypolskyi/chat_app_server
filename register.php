<?php
require_once("common.php");

if (!isset($_POST["username"]) || empty(trim($_POST["username"]))) {
    $response = ["status" => "error", "message" => "Username not specified. Please try again."];
    echo json_encode($response);
    die();
}

if (!isset($_POST["email"]) || empty(trim($_POST["email"]))) {
    $response = ["status" => "error", "message" => "Email not specified. Please try again."];
    echo json_encode($response);
    die();
}

if (!isset($_POST["password"]) || empty(trim($_POST["password"]))) {
    $response = ["status" => "error", "message" => "Password not specified. Please try again."];
    echo json_encode($response);
    die();
}

$username = $_POST["username"];
$email = $_POST["email"];
$password = $_POST["password"];

if (!isset($_FILES["uploadedFile"])) {
    $response = ["status" => "error", "message" => "No file uploaded. Please try again."];
    echo json_encode($response);
    die();
}

$uppercase = preg_match('@[A-Z]@', $password);
$lowercase = preg_match('@[a-z]@', $password);
$number    = preg_match('@[0-9]@', $password);
$specialChars = preg_match('@[^\w]@', $password);

if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
    $response = ["status" => "error", "message" => "Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character."];
    echo json_encode($response);
    die();
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$file = $_FILES["uploadedFile"];
$fileName = $file["name"];
$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
$allowedExtensions = array("jpg", "jpeg");

if (in_array($fileExtension, $allowedExtensions)) {
    if ($file["error"] === UPLOAD_ERR_OK) {
        $uploadedFileName = $file['name'];
        $destination = "../client/src/assets/img/" . $uploadedFileName;

        if (move_uploaded_file($file["tmp_name"], $destination)) {
            $response = [
                "status" => "success",
                "message" => "File uploaded successfully!"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Failed to upload the file. Please try again."
            ];
            echo json_encode($response);
            die();
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "Error during file upload. Please try again."
        ];
        echo json_encode($response);
        die();
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid file format. Allowed formats: jpg/jpeg."
    ];
    echo json_encode($response);
    die();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $response = [
        "status" => "error",
        "message" => "User with email $email already exists.",
    ];
    echo json_encode($response);
    die();
}

$insertStmt = $pdo->prepare("INSERT INTO users (username, email, avatar, password) VALUES (:username, :email, :avatar, :hash)");
$insertStmt->bindParam(":username", $username);
$insertStmt->bindParam(":email", $email);
$insertStmt->bindParam(":avatar", $file['name']);
$insertStmt->bindParam(":hash", $hash);

if ($insertStmt->execute()) {
    $response = [
        "status" => "success",
        "message" => "Account created.",
    ];
} else {
    $response = [
        "status" => "error",
        "message" => "Unexpected error. Please try again.",
    ];
}

echo json_encode($response);
