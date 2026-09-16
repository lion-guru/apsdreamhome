<?php
// Phase 2: drop exact-duplicate indexes (idempotent). NEVER drops a UNIQUE index
// while keeping a non-unique twin; in that case it reports and skips.
// Run: php scripts/migrate_phase2_dedup_indexes.php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pairs = [
    // [table, DROP, KEEP]
    ['leads', 'idx_lead_assigned', 'idx_leads_assigned_to'],
    ['leads', 'idx_lead_created', 'idx_leads_created_at'],
    ['leads', 'idx_lead_source', 'idx_leads_source'],
    ['bookings', 'ix_bookings_customer_id', 'idx_bookings_customer_id'],
    ['bookings', 'idx_booking_customer', 'idx_bookings_customer_id'],
    ['bookings', 'idx_book_status', 'idx_bookings_status'],
    ['bookings', 'idx_book_plot', 'idx_booking_plot'],
    ['bookings', 'idx_booking_colony', 'idx_bookings_colony'],
    ['bookings', 'idx_booking_date', 'idx_bookings_created_at'],
    ['bookings', 'idx_booking_status', 'idx_bookings_status'],
    ['plots', 'idx_plot_colony', 'idx_plots_colony'],
    ['plots', 'idx_plots_colony_id', 'idx_plots_colony'],
    ['plots', 'idx_plot_number', 'idx_plots_plot_number'],
    ['plots', 'idx_plot_status', 'idx_plots_status'],
    ['users', 'idx_user_role', 'idx_users_role'],
    ['users', 'idx_user_status', 'idx_status'],
    ['users', 'idx_user_tenant', 'idx_users_tenant'],
    ['notifications', 'ix_notifications_user_id', 'idx_notif_user'],
    ['notifications', 'idx_notif_created', 'idx_created_at'],
    ['notifications', 'idx_notif_read', 'idx_is_read'],
    ['ad_placements', 'slot_key', 'idx_slot_key'],
    ['bank_reconciliation', 'idx_br_bank_account_id', 'idx_br_bank'],
    ['crm_settings', 'setting_key', 'idx_setting_key'],
    ['daily_cash_book', 'idx_dcb_bank_account_id', 'idx_dcb_bank'],
    ['daily_sales_report', 'report_date', 'idx_dsr_date'],
    ['digilocker_sessions', 'session_id', 'idx_session'],
    ['esign_transactions', 'transaction_id', 'idx_transaction'],
    ['lead_activities', 'idx_lead_activity_lead', 'idx_lead_activities_lead_id'],
    ['lead_deals', 'lead_id', 'idx_lead_deal_lead'],
    ['messaging_participants', 'uk_conv_user', 'idx_conversation_user'],
    ['mlm_network_tree', 'idx_parent', 'idx_mlm_tree_parent'],
    ['mlm_network_tree', 'idx_unique_associate', 'idx_associate'],
    ['property_images', 'ix_property_images_property_id', 'idx_property_id'],
    ['reward_redemptions', 'redemption_code', 'idx_code'],
    ['site_visits', 'idx_sv_visit_date', 'idx_site_visits_visit_date'],
    ['subscription_plans', 'slug', 'idx_slug'],
    ['tenants', 'slug', 'idx_slug'],
    ['upi_payments', 'transaction_ref', 'idx_transaction'],
    ['user_sessions', 'session_token', 'idx_us_token'],
    ['vendor_payments', 'idx_vp_vendor_id', 'idx_vp_vendor'],
    ['whatsapp_templates', 'template_code', 'idx_code'],
];

function indexInfo($pdo, $table, $name) {
    $rows = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    $cols = []; $nonUnique = null;
    foreach ($rows as $r) {
        if ($r['Key_name'] === $name) { $cols[(int)$r['Seq_in_index']] = $r['Column_name']; $nonUnique = (int)$r['Non_unique']; }
    }
    if (!$cols) return null;
    ksort($cols);
    return ['cols' => array_values($cols), 'non_unique' => $nonUnique];
}

$dropped = 0; $skipped = 0;
foreach ($pairs as [$table, $drop, $keep]) {
    try {
        $d = indexInfo($pdo, $table, $drop);
        $k = indexInfo($pdo, $table, $keep);
        if ($d === null) { echo "OK $table.$drop already gone\n"; continue; }
        if ($k === null) { echo "SKIP $table.$drop: keeper $keep MISSING — keeping $drop\n"; $skipped++; continue; }
        if ($d['cols'] !== $k['cols']) { echo "SKIP $table.$drop: column list differs from $keep — manual review\n"; $skipped++; continue; }
        if ($d['non_unique'] === 0 && $k['non_unique'] === 1) {
            echo "SKIP $table.$drop: $drop is UNIQUE but $keep is not — constraint would be lost\n"; $skipped++; continue;
        }
        $pdo->exec("ALTER TABLE `$table` DROP INDEX `$drop`");
        echo "DROPPED $table.$drop (kept $keep on " . implode(',', $k['cols']) . ")\n";
        $dropped++;
    } catch (Exception $e) {
        echo "ERR $table.$drop: " . substr($e->getMessage(), 0, 200) . "\n"; $skipped++;
    }
}
echo "DONE phase2: dropped=$dropped skipped=$skipped\n";
