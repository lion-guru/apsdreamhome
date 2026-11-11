<?php
/**
 * Consolidate Campaign Services - Ensure tables support unified service
 * Session 374 - Phase 2
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "Consolidating Campaign Services - Database Schema Updates...\n";
    
    // Check existing tables
    $tables = ['campaigns', 'marketing_campaigns', 'drip_campaigns', 'drip_emails', 
               'campaign_deliveries', 'marketing_campaign_recipients', 
               'marketing_campaign_templates'];
    
    foreach ($tables as $table) {
        try {
            $st = $pdo->query("DESCRIBE `$table`");
            echo "\nTable: $table\n";
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $col) {
                echo "  {$col['Field']} {$col['Type']}\n";
            }
        } catch (Throwable $e) {
            echo "\nTable: $table - NOT EXISTS\n";
        }
    }
    
    echo "\nDone checking tables.\n";
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}