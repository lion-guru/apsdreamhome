<?php
require_once __DIR__ . '/../app/Core/Database/Database.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $conn = $db->getConnection();
    $stmt = $conn->query("DESCRIBE properties");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($fields, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}?>