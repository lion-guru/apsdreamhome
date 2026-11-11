<?php
/**
 * Add Critical Database Indexes for Performance
 * Session 374 - Phase 1
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    echo "Adding critical database indexes...\n";
    
    // 1. Leads table indexes (38K rows)
    $indexes = [
        "CREATE INDEX idx_leads_status_created ON leads(status, created_at)",
        "CREATE INDEX idx_leads_assigned_status ON leads(assigned_to, status)",
        "CREATE INDEX idx_leads_source_created ON leads(source, created_at)",
        
        // Bookings (payment queries)
        "CREATE INDEX idx_plot_bookings_customer_status ON plot_bookings(customer_id, status)",
        "CREATE INDEX idx_plot_bookings_plot_status ON plot_bookings(plot_id, status)",
        
        // Notifications (real-time)
        "CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at)",
        
        // Leads activities (timeline queries)
        "CREATE INDEX idx_lead_activities_lead_created ON lead_activities(lead_id, created_at)",
        
        // Payments (reconciliation)
        "CREATE INDEX idx_payments_booking_status ON payments(booking_id, status)",
    ];
    
    $success = 0;
    $skipped = 0;
    
    foreach ($indexes as $sql) {
        try {
            // Check if index already exists
            $indexName = '';
            if (preg_match('/CREATE INDEX (\w+)/', $sql, $matches)) {
                $indexName = $matches[1];
            }
            
            // Check if index exists
            $checkSql = "SELECT COUNT(*) FROM information_schema.statistics 
                         WHERE table_schema = DATABASE() AND index_name = ?";
            $stmt = $pdo->prepare($checkSql);
            $stmt->execute([$indexName]);
            $exists = $stmt->fetchColumn();
            
            if ($exists) {
                echo "SKIP: Index $indexName already exists\n";
                $skipped++;
                continue;
            }
            
            $pdo->exec($sql);
            echo "OK: Created index $indexName\n";
            $success++;
            
        } catch (Throwable $e) {
            echo "ERROR: Failed to create index: " . $e->getMessage() . "\n";
        }
    }
    
    $pdo->commit();
    echo "\nDone! Created: $success, Skipped: $skipped\n";
    
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}