<?php
// Direct table check
require_once __DIR__ . '/includes/db_connection.php';

try {
    $pdo = getDbConnection();
    
    // Check legal_services
    $result1 = $pdo->query("SHOW TABLES LIKE 'legal_services'");
    echo "legal_services: " . ($result1->fetch() ? "EXISTS" : "MISSING") . "\n";
    
    // Check team_members
    $result2 = $pdo->query("SHOW TABLES LIKE 'team_members'");
    echo "team_members: " . ($result2->fetch() ? "EXISTS" : "MISSING") . "\n";
    
    // Check faqs
    $result3 = $pdo->query("SHOW TABLES LIKE 'faqs'");
    echo "faqs: " . ($result3->fetch() ? "EXISTS" : "MISSING") . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>