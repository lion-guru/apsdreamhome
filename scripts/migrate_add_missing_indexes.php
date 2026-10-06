<?php
/**
 * Migration: Add missing indexes for performance
 * Run: php scripts/migrate_add_missing_indexes.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance()->getPdo();

$indexes = [
    // plots table
    "ALTER TABLE plots ADD INDEX idx_plots_block (block)" => "idx_plots_block",
    "ALTER TABLE plots ADD INDEX idx_plots_phase (phase)" => "idx_plots_phase",
    "ALTER TABLE plots ADD INDEX idx_plots_customer_id (customer_id)" => "idx_plots_customer_id",
    "ALTER TABLE plots ADD INDEX idx_plots_status_block (status, block)" => "idx_plots_status_block",
    
    // colony_development_costs
    "ALTER TABLE colony_development_costs ADD INDEX idx_cdc_cost_type (cost_type)" => "idx_cdc_cost_type",
    
    // land_records
    "ALTER TABLE land_records ADD INDEX idx_land_records_colony_id (colony_id)" => "idx_land_records_colony_id",
    
    // colonies
    "ALTER TABLE colonies ADD INDEX idx_colonies_district_id (district_id)" => "idx_colonies_district_id",
    "ALTER TABLE colonies ADD INDEX idx_colonies_pipeline_stage (pipeline_stage)" => "idx_colonies_pipeline_stage",
    
    // plot_bookings
    "ALTER TABLE plot_bookings ADD INDEX idx_pb_customer_id (customer_id)" => "idx_pb_customer_id",
    "ALTER TABLE plot_bookings ADD INDEX idx_pb_plot_id (plot_id)" => "idx_pb_plot_id",
    
    // booking_payment_schedules
    "ALTER TABLE booking_payment_schedules ADD INDEX idx_bps_status_due (status, due_date)" => "idx_bps_status_due",
    
    // leads
    "ALTER TABLE leads ADD INDEX idx_leads_assigned_to (assigned_to)" => "idx_leads_assigned_to",
    "ALTER TABLE leads ADD INDEX idx_leads_status_source (status, source_id)" => "idx_leads_status_source",
    
    // users
    "ALTER TABLE users ADD INDEX idx_users_role_status (role, status)" => "idx_users_role_status",
    
    // payments
    "ALTER TABLE payments ADD INDEX idx_payments_gateway_txn (gateway_transaction_id)" => "idx_payments_gateway_txn",
    "ALTER TABLE payments ADD INDEX idx_payments_reference (reference_id)" => "idx_payments_reference",
    
    // mlm_network_tree
    "ALTER TABLE mlm_network_tree ADD INDEX idx_mnt_parent_id (parent_id)" => "idx_mnt_parent_id",
    "ALTER TABLE mlm_network_tree ADD INDEX idx_mnt_associate_id (associate_id)" => "idx_mnt_associate_id",
    
    // mlm_commission_ledger
    "ALTER TABLE mlm_commission_ledger ADD INDEX idx_mcl_beneficiary (beneficiary_user_id)" => "idx_mcl_beneficiary",
    "ALTER TABLE mlm_commission_ledger ADD INDEX idx_mcl_source (source_user_id)" => "idx_mcl_source",
    "ALTER TABLE mlm_commission_ledger ADD INDEX idx_mcl_booking (booking_id)" => "idx_mcl_booking",
];

echo "Adding missing indexes...\n";
$success = 0;
$failed = 0;

foreach ($indexes as $sql => $name) {
    try {
        $db->exec($sql);
        echo "✓ $name\n";
        $success++;
    } catch (PDOException $e) {
        if ($e->getCode() === '42000' && strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "- $name already exists\n";
            $success++;
        } else {
            echo "✗ $name: " . $e->getMessage() . "\n";
            $failed++;
        }
    }
}

echo "\nDone: $success succeeded, $failed failed\n";