<?php
require_once("common.php");
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
header('Content-Type: application/json');


if (empty(trim($_POST["username"])) || empty(trim($_POST["email"])) || empty(trim($_POST["password"])) || !isset($_FILES["uploadedFile"])) {
    $response = ["status" => "error", "message" => "Username, email, password, or profile picture not specified. Please try again."];
    echo json_encode($response);
    exit;
}

$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response = ["status" => "error", "message" => "Invalid email format. Please try again."];
    echo json_encode($response);
    exit;
}

$uppercase = preg_match('@[A-Z]@', $password);
$lowercase = preg_match('@[a-z]@', $password);
$number    = preg_match('@[0-9]@', $password);
$specialChars = preg_match('@[^\w]@', $password);

if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
    $response = ["status" => "error", "message" => "Password should be at least 8 characters in length and include at least one upper case letter, one number, and one special character."];
    echo json_encode($response);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

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

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $response = ["status" => "error", "message" => "User with email $email already exists."];
    echo json_encode($response);
    exit;
}

$verificationCode = mt_rand(100000, 999999);

$isVerified = false;

$insertStmt = $pdo->prepare("INSERT INTO users (username, email, avatar, password, verification_code, is_verified) VALUES (:username, :email, :avatar, :hash, :verification_code, :is_verified)");
$insertStmt->bindParam(":username", $username);
$insertStmt->bindParam(":email", $email);
$insertStmt->bindParam(":avatar", $fileName);
$insertStmt->bindParam(":hash", $hash);
$insertStmt->bindParam(":verification_code", $verificationCode, PDO::PARAM_INT);
$insertStmt->bindParam(":is_verified", $isVerified, PDO::PARAM_BOOL);

if ($insertStmt->execute()) {
    try {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                   
        $mail->SMTPAuth   = true;                                 
        $mail->Username   = 'dennistripolskiy@gmail.com';                    
        $mail->Password   = 'gegpebuyyqivgloh';                              
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465;                                   
    
        $mail->setFrom('dennistripolskiy@gmail.com');
        $mail->addAddress($email);    
    
        $mail->isHTML(true);  
        $mail->Subject = 'Account Verification';
    
        $verificationLink = "http://localhost/server/emailVerification.php?token=$verificationCode"; 
        $mail->Body = "Thank you for signing up! Please click the following link to verify your account: <a href='{$verificationLink}'>Verify Account</a>";   
    
        $mail->send();
    } catch (Exception $e) {
        $response = ["status" => "error", "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
        echo json_encode($response);
        exit();
    }

    $response = ["status" => "success", "message" => "Account creation successful! Instructions on how to proceed have been sent to your email."];
    echo json_encode($response);
    exit();
} else {
    $response = ["status" => "error", "message" => "Unexpected error. Please try again."];
    echo json_encode($response);
    exit();
}
?>
