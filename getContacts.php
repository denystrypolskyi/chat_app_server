<?php
require_once("common.php");

try {
    if (!isset($_GET["loggedUserId"])) {
        $response = [
            "status" => "error",
            "message" => "loggedUserId not specified in the request",
            "contacts" => []
        ];
        echo json_encode($response);
        exit;
    }

    $loggedUserId = $_GET["loggedUserId"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id != :id");
    $stmt->bindParam(":id", $loggedUserId);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $contactCount = count($rows);

    $response = [
        "status" => "success",
        "contacts" => $contactCount > 0 ? $rows : [],
        "message" => $contactCount > 0 ? "" : "No contacts found"
    ];
    echo json_encode($response);
    exit;
} catch (PDOException $e) {
    $response = [
        "status" => "error",
        "message" => "Database error: " . $e->getMessage(),
        "contacts" => []
    ];
    echo json_encode($response);
    exit;
}
?>
