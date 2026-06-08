<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = ['blocked_ips','failed_login_attempts','users','api_keys','api_developers','webhook_endpoints','rera_compliance_log','gst_returns','tds_register','kyc_requests','user_properties','plot_bookings','leads','payment_transactions','emi_plans','booking_payment_schedules','ai_call_sessions','ai_call_extracted_leads','ai_call_logs','ai_calling_schedule','ai_intent_patterns','ai_learning_data','ai_price_models','gst_transactions','bank_accounts_master','bank_reconciliation','audit_log','notification_settings','penalty_audit'];

foreach ($tables as $t) {
    try {
        $r = $pdo->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM);
        echo "=== $t ===\n" . $r[1] . "\n\n";
    } catch (Exception $e) {
        echo "=== $t === MISSING: " . $e->getMessage() . "\n";
    }
}
