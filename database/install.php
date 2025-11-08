<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

try {
    // Read the schema file
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    if ($sql === false) {
        throw new Exception('Failed to read schema file');
    }
    
    // Get database connection
    $db = new PDO(
        "mysql:host=" . $config->get('database.host') . 
        ";port=" . $config->get('database.port') . 
        ";charset=utf8mb4",
        $config->get('database.user'),
        $config->get('database.pass'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Create database if not exists
    $dbName = $config->get('database.name');
    $db->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db->exec("USE `$dbName`");
    
    // Split SQL into individual statements
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each query
    foreach ($queries as $query) {
        if (!empty($query)) {
            $db->exec($query);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database schema installed successfully',
        'admin_credentials' => [
            'email' => 'admin@apsdreamhomes.com',
            'password' => 'admin@123'
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
