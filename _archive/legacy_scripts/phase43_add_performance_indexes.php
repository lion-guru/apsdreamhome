<?php
/**
 * Add performance indexes to hot tables (uses raw PDO for standalone execution)
 */

$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$indexes = [
    ['leads', 'idx_status_created', 'status, created_at'],
    ['leads', 'idx_source', 'source'],
    ['leads', 'idx_assigned', 'assigned_to, status'],
    ['leads', 'idx_email', 'email'],
    ['leads', 'idx_phone', 'phone'],
    ['users', 'idx_role_status', 'role, status'],
    ['users', 'idx_email_status', 'email, status'],
    ['users', 'idx_phone', 'phone'],
    ['user_properties', 'idx_status', 'status'],
    ['user_properties', 'idx_listing_type', 'listing_type'],
    ['user_properties', 'idx_user_id', 'user_id'],
    ['user_properties', 'idx_created', 'created_at'],
    ['plots', 'idx_colony_status', 'colony_id, status'],
    ['plots', 'idx_type', 'type'],
    ['bookings', 'idx_user_id', 'user_id'],
    ['bookings', 'idx_plot_id', 'plot_id'],
    ['bookings', 'idx_status_created', 'status, created_at'],
    ['audit_log', 'idx_status', 'status'],
    ['audit_log', 'idx_ip', 'ip_address'],
    ['audit_log', 'idx_user_role', 'user_role, created_at'],
    ['newsletter_subscribers', 'idx_active', 'is_active'],
    ['inquiries', 'idx_status', 'status'],
    ['inquiries', 'idx_email', 'email'],
    ['inquiries', 'idx_created', 'created_at'],
    ['properties', 'idx_status', 'status'],
    ['properties', 'idx_type', 'type'],
    ['realtime_notifications', 'idx_user_channel', 'user_id, channel_name'],
    ['realtime_notifications', 'idx_created', 'created_at'],
    ['webhook_deliveries', 'idx_endpoint_status', 'endpoint_id, status'],
    ['webhook_deliveries', 'idx_event', 'event_type'],
    ['api_keys', 'idx_user_active', 'user_id, is_active'],
    ['mlm_profiles', 'idx_sponsor', 'sponsor_user_id'],
    ['commissions', 'idx_user_status', 'user_id, status'],
    ['commissions', 'idx_created', 'created_at'],
    ['commissions', 'idx_booking', 'booking_id'],
    ['payouts', 'idx_user_status', 'user_id, status'],
    ['payouts', 'idx_created', 'created_at'],
    ['workflow_automations', 'idx_active', 'is_active, trigger_event'],
    ['mlm_commission_ledger', 'idx_beneficiary', 'beneficiary_user_id'],
    ['mlm_commission_ledger', 'idx_created', 'created_at'],
];

$added = 0; $skipped = 0; $errors = 0;
foreach ($indexes as $idx) {
    [$table, $name, $cols] = $idx;
    try {
        $db->exec("ALTER TABLE `$table` ADD INDEX `$name` ($cols)");
        echo "OK Added: $table.$name ($cols)\n";
        $added++;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Duplicate') !== false || strpos($msg, 'already exists') !== false || strpos($msg, 'check that column/key exists') !== false || strpos($msg, "doesn't exist") !== false) {
            echo "-- Skipped: $table.$name ($msg)\n";
            $skipped++;
        } else {
            echo "!! Error: $table.$name - $msg\n";
            $errors++;
        }
    }
}
echo "\nSummary: $added added, $skipped skipped, $errors errors\n";?>