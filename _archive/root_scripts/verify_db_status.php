<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    
    echo "=== DATABASE VERIFICATION ===\n\n";
    
    // Check lead_deals columns
    $cols = $db->query('SHOW COLUMNS FROM lead_deals')->fetchAll();
    $colNames = array_column($cols, 'Field');
    echo "1. lead_deals table columns:\n";
    echo "   - stage: " . (in_array('stage', $colNames) ? '✅ EXISTS' : '❌ MISSING') . "\n";
    echo "   - assigned_to: " . (in_array('assigned_to', $colNames) ? '✅ EXISTS' : '❌ MISSING') . "\n";
    echo "   - property_id: " . (in_array('property_id', $colNames) ? '✅ EXISTS' : '❌ MISSING') . "\n";
    echo "   - notes: " . (in_array('notes', $colNames) ? '✅ EXISTS' : '❌ MISSING') . "\n";
    
    // Check lead_scoring table
    $tables = $db->query("SHOW TABLES LIKE 'lead_scoring'")->fetchAll();
    echo "\n2. lead_scoring table: " . (empty($tables) ? '❌ NOT EXISTS' : '✅ EXISTS') . "\n";
    
    // Check users table for admin
    $admin = $db->query("SELECT id, name, email, role FROM users WHERE email = 'admin@apsdreamhome.com' LIMIT 1")->fetch();
    echo "\n3. Admin user: " . ($admin ? '✅ EXISTS (ID: ' . $admin['id'] . ')' : '❌ NOT FOUND') . "\n";
    
    echo "\n=== VERIFICATION COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
