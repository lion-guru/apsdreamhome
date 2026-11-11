<?php

/**
 * Migration: Add critical database indexes for performance
 * Phase 1: Critical DB Indexes
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getConnection();

echo "=== Adding Critical Database Indexes ===\n\n";

// Critical indexes for leads table (38K rows)
$indexes = [
    // Leads table (38K rows) - most queried
    "CREATE INDEX idx_leads_status_created ON leads(status, created_at)" => "Leads by status + date",
    "CREATE INDEX idx_leads_assigned_status ON leads(assigned_to, status)" => "Leads by assignee + status",
    "CREATE INDEX idx_leads_source_created ON leads(source, created_at)" => "Leads by source + date",
    "CREATE INDEX idx_leads_customer_status ON leads(customer_id, status)" => "Leads by customer + status",
    
    // Bookings table - payment queries
    "CREATE INDEX idx_bookings_customer_status ON plot_bookings(customer_id, status)" => "Bookings by customer + status",
    "CREATE INDEX idx_bookings_plot_status ON plot_bookings(plot_id, status)" => "Bookings by plot + status",
    "CREATE INDEX idx_bookings_created_status ON plot_bookings(created_at, status)" => "Bookings by date + status",
    
    // Notifications - real-time queries
    "CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at)" => "Notifications by user + read status + date",
    "CREATE INDEX idx_notifications_type_created ON notifications(type, created_at)" => "Notifications by type + date",
    
    // Payments - financial queries
    "CREATE INDEX idx_payments_customer_status ON payments(customer_id, status)" => "Payments by customer + status",
    "CREATE INDEX idx_payments_booking_status ON payments(booking_id, status)" => "Payments by booking + status",
    "CREATE INDEX idx_payments_created_status ON payments(created_at, status)" => "Payments by date + status",
    
    // Township operations
    "CREATE INDEX idx_maintenance_dues_customer_status ON maintenance_dues(customer_id, status)" => "Maintenance dues by customer + status",
    "CREATE INDEX idx_township_grievances_customer_status ON township_resident_grievances(customer_id, status)" => "Grievances by customer + status",
    "CREATE INDEX idx_site_visits_user_status ON site_visits(user_id, status)" => "Site visits by user + status",
    
    // Communications
    "CREATE INDEX idx_email_queue_status_created ON email_queue(status, created_at)" => "Email queue by status + date",
    "CREATE INDEX idx_sms_queue_status_created ON sms_queue(status, created_at)" => "SMS queue by status + date",
    
    // MLM/Commissions
    "CREATE INDEX idx_mlm_commission_ledger_user_type ON mlm_commission_ledger(user_id, type)" => "Commission ledger by user + type",
    "CREATE INDEX idx_mlm_commission_ledger_created ON mlm_commission_ledger(created_at)" => "Commission ledger by date",
    
    // Audit logs
    "CREATE INDEX idx_audit_logs_user_created ON audit_logs(user_id, created_at)" => "Audit logs by user + date",
    "CREATE INDEX idx_audit_logs_entity_created ON audit_logs(entity_type, entity_id, created_at)" => "Audit logs by entity + date",
];

$success = 0;
$failed = 0;

foreach ($indexes as $sql => $description) {
    try {
        // Check if index already exists
        $checkSql = "SELECT COUNT(*) as cnt FROM information_schema.statistics 
                     WHERE table_schema = DATABASE() 
                     AND index_name = '" . explode(' ', $sql)[2] . "'";
        $stmt = $pdo->query($checkSql);
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            echo "⏭️  SKIP: $description (already exists)\n";
            continue;
        }
        
        $pdo->exec($sql);
        echo "✅ CREATE: $description\n";
        $success++;
    } catch (PDOException $e) {
        echo "❌ FAILED: $description - " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n=== Summary ===\n";
echo "Successful: $success\n";
echo "Failed: $failed\n";
echo "Total: " . count($indexes) . "\n";