<?php
/**
 * Test script to verify legal_services.php works
 */

// Include the same files as legal_services.php
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/includes/db_connection.php';

echo "Testing legal_services functionality...\n\n";

try {
    // Get database connection
    $pdo = getDbConnection();
    
    if ($pdo === null) {
        throw new Exception("Failed to connect to the database");
    }
    
    echo "✓ Database connection successful\n";
    
    // Test query for legal services (same as in legal_services.php)
    $legalServicesQuery = "SELECT * FROM legal_services WHERE status = 'active' ORDER BY display_order ASC";
    $legalServicesStmt = $pdo->query($legalServicesQuery);
    $legalServices = $legalServicesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✓ Legal services query successful. Found: " . count($legalServices) . " services\n";
    
    // Test query for team members
    $teamMembersQuery = "SELECT * FROM team_members WHERE status = 'active' ORDER BY display_order ASC";
    $teamMembersStmt = $pdo->query($teamMembersQuery);
    $teamMembers = $teamMembersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✓ Team members query successful. Found: " . count($teamMembers) . " members\n";
    
    // Test query for FAQs
    $faqsQuery = "SELECT * FROM faqs WHERE status = 'active' ORDER BY display_order ASC";
    $faqsStmt = $pdo->query($faqsQuery);
    $faqs = $faqsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✓ FAQs query successful. Found: " . count($faqs) . " FAQs\n";
    
    echo "\n✅ All database queries from legal_services.php are working correctly!\n";
    echo "The legal services page should now load without database errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>