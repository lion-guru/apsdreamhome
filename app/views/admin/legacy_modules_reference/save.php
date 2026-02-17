<?php
/**
 * Save Data - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

header("Content-Type: application/json");

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}
?>
