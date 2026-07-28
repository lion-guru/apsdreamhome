<?php
/**
 * Comprehensive migration: Add tenant_id to ALL tables that don't have it.
 * Safe to run multiple times (checks column existence first).
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get all tables
$stmt = $db->query("SHOW TABLES");
$allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Tables that should NOT get tenant_id (system, config, reference, or cross-tenant)
$skipTables = [
    // Framework/system
    'admin', 'roles', 'user_roles', 'permissions', 'migrations', 'sessions',
    'cache', 'cache_locks', 'job_batches',
    // Reference data (shared across tenants)
    'states', 'cities', 'districts', 'pincodes', 'countries',
    'property_types', 'property_categories', 'lead_sources', 'lead_statuses',
    'document_types', 'document_categories',
    'chart_of_accounts', 'tax_types', 'tax_slabs',
    'leave_types', 'shift_types', 'supported_locales',
    'stamp_duty_config', 'stamp_duty_rates',
    'circle_rates', 'payment_plans', 'emi_plans',
    'budgets', 'budget_expenses', 'budget_planning',
    // Tenant config
    'tenants', 'tenant_subscriptions', 'tenant_usage', 'tenant_users',
    'subscription_plans', 'premium_packages',
    // MLM config (shared across tenants)
    'mlm_settings', 'mlm_levels', 'mlm_rank_benefits', 'mlm_rank_slabs',
    'mlm_commission_plans', 'mlm_plan_levels', 'mlm_joining_packages',
    'mlm_career_rewards', 'mlm_payouts',
    'commission_payouts', 'points_rules',
    'tier_benefits', 'badges', 'investor_levels',
    // AI config (shared)
    'ai_settings', 'ai_intent_patterns', 'ai_knowledge_base',
    'ai_chatbot_settings', 'ai_voice_models',
    // Finance config
    'gst_settings', 'tax_slabs', 'tax_types',
    'bank_accounts_master', 'bank_branches', 'bank_interest_rates',
    'chart_of_accounts',
    // Credit card processing config
    'upi_config', 'esign_config', 'digilocker_config',
    'company_credentials', 'whatsapp_config',
    'chat_widget_settings', 'crm_settings',
    'notification_settings', 'notification_templates',
    // Email/SMS config
    'email_templates', 'sms_templates',
    // Legal config
    'legal_clause_library', 'legal_ai_prompts',
    // Site config
    'site_settings', 'site_content', 'settings', 'company_settings',
    // Reports
    'report_definitions',
    // Already have tenant_id
    'agreements', 'blog_posts', 'bookings', 'booking_digital_agreements',
    'booking_emi_agreements', 'crm_assignments', 'crm_campaigns',
    'crm_interactions', 'crm_lead_forms', 'crm_lead_scores_history',
    'crm_segments', 'crm_settings', 'crm_tasks',
    'customer_referrals', 'directory_categories', 'directory_jobs',
    'directory_listings', 'directory_materials', 'directory_reviews',
    'kyc_requests', 'kyc_verification_logs', 'land_site_visits',
    'leads', 'lead_activities', 'lead_deals', 'lead_visits',
    'mlm_commission_ledger', 'mlm_import_audit', 'mlm_network_tree',
    'mlm_profiles', 'mlm_referrals', 'mlm_site_visits',
    'payments', 'payment_transactions', 'plots', 'plot_bookings',
    'properties', 'property_alert_subscriptions', 'property_visits',
    'referrals', 'saved_searches', 'search_history', 'site_visits',
    'support_tickets', 'support_ticket_replies', 'testimonials',
    'users', 'user_properties', 'visits',
];

$added = 0;
$skipped = 0;
$errors = [];

foreach ($allTables as $table) {
    if (in_array($table, $skipTables)) {
        $skipped++;
        continue;
    }

    // Check if tenant_id already exists
    $col = $db->query("SHOW COLUMNS FROM `$table` LIKE 'tenant_id'");
    if ($col->rowCount() > 0) {
        $skipped++;
        continue;
    }

    try {
        $db->exec("ALTER TABLE `$table` ADD COLUMN `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`");
        $db->exec("ALTER TABLE `$table` ADD INDEX `idx_{$table}_tenant_id` (`tenant_id`)");
        echo "[ADDED] $table\n";
        $added++;
    } catch (PDOException $e) {
        try {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1");
            $db->exec("ALTER TABLE `$table` ADD INDEX `idx_{$table}_tenant_id` (`tenant_id`)");
            echo "[ADDED] $table (no position)\n";
            $added++;
        } catch (PDOException $e2) {
            $errors[] = "$table: " . $e2->getMessage();
            echo "[ERROR] $table\n";
        }
    }
}

echo "\n--- Summary ---\n";
echo "Total: " . count($allTables) . "\n";
echo "Added: $added\n";
echo "Skipped: $skipped\n";
echo "Errors: " . count($errors) . "\n";
foreach ($errors as $e) echo "  $e\n";
