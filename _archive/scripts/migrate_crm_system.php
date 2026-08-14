<?php
/**
 * CRM System Migration â€” Lead Follow-up + Pipeline + Interaction Tracking
 * Creates interaction tracking, assignment history, pipeline stages, lead forms
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

$tables = [

    // 1. crm_interactions â€” every touchpoint with a lead (call, sms, email, visit, whatsapp, meeting, note)
    "CREATE TABLE IF NOT EXISTS `crm_interactions` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` BIGINT(20) UNSIGNED NOT NULL,
        `user_id` BIGINT(20) UNSIGNED NOT NULL,
        `interaction_type` ENUM('call','sms','email','whatsapp','visit','meeting','note','system') NOT NULL DEFAULT 'note',
        `direction` ENUM('inbound','outbound') DEFAULT 'outbound',
        `subject` VARCHAR(255) DEFAULT NULL,
        `body` TEXT DEFAULT NULL,
        `duration_seconds` INT(11) DEFAULT NULL,
        `outcome` ENUM('connected','not_reached','busy','no_answer','interested','not_interested','callback_requested','site_visit_booked','proposal_sent','deal_closed') DEFAULT NULL,
        `next_action` VARCHAR(255) DEFAULT NULL,
        `next_action_date` DATE DEFAULT NULL,
        `attachments` JSON DEFAULT NULL,
        `metadata` JSON DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_interactions_lead` (`lead_id`),
        KEY `idx_crm_interactions_user` (`user_id`),
        KEY `idx_crm_interactions_type` (`interaction_type`),
        KEY `idx_crm_interactions_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 2. crm_assignments â€” lead assignment history (who â†’ whom â†’ when)
    "CREATE TABLE IF NOT EXISTS `crm_assignments` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` BIGINT(20) UNSIGNED NOT NULL,
        `assigned_from` BIGINT(20) UNSIGNED DEFAULT NULL,
        `assigned_to` BIGINT(20) UNSIGNED NOT NULL,
        `assigned_by` BIGINT(20) UNSIGNED NOT NULL,
        `reason` VARCHAR(255) DEFAULT NULL,
        `notes` TEXT DEFAULT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_assignments_lead` (`lead_id`),
        KEY `idx_crm_assignments_to` (`assigned_to`),
        KEY `idx_crm_assignments_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 3. crm_pipeline_stages â€” configurable pipeline stages per role
    "CREATE TABLE IF NOT EXISTS `crm_pipeline_stages` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(50) NOT NULL,
        `color` VARCHAR(7) DEFAULT '#6366f1',
        `icon` VARCHAR(50) DEFAULT 'circle',
        `order_index` INT(11) NOT NULL DEFAULT 0,
        `role` ENUM('admin','agent','associate','employee','all') DEFAULT 'all',
        `is_won` TINYINT(1) DEFAULT 0,
        `is_lost` TINYINT(1) DEFAULT 0,
        `auto_actions` JSON DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_stage_slug_role` (`slug`, `role`),
        KEY `idx_crm_stages_role` (`role`, `order_index`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 4. crm_lead_forms â€” web/app capture forms (embeddable)
    "CREATE TABLE IF NOT EXISTS `crm_lead_forms` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `form_code` VARCHAR(50) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `fields_config` JSON NOT NULL DEFAULT (JSON_ARRAY()),
        `source_tag` VARCHAR(50) DEFAULT 'website',
        `default_assign_to` BIGINT(20) UNSIGNED DEFAULT NULL,
        `auto_score` TINYINT(1) DEFAULT 1,
        `auto_enroll_drip` TINYINT(1) DEFAULT 0,
        `drip_campaign_id` INT(11) DEFAULT NULL,
        `thank_you_message` VARCHAR(500) DEFAULT 'Thank you! We will contact you soon.',
        `redirect_url` VARCHAR(500) DEFAULT NULL,
        `webhook_url` VARCHAR(500) DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `submission_count` INT(11) DEFAULT 0,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_form_code` (`form_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 5. crm_form_submissions â€” every form fill
    "CREATE TABLE IF NOT EXISTS `crm_form_submissions` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `form_id` INT(11) NOT NULL,
        `lead_id` BIGINT(20) UNSIGNED DEFAULT NULL,
        `submitted_data` JSON NOT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `user_agent` TEXT DEFAULT NULL,
        `utm_source` VARCHAR(100) DEFAULT NULL,
        `utm_medium` VARCHAR(100) DEFAULT NULL,
        `utm_campaign` VARCHAR(100) DEFAULT NULL,
        `utm_term` VARCHAR(100) DEFAULT NULL,
        `utm_content` VARCHAR(100) DEFAULT NULL,
        `referrer_url` VARCHAR(500) DEFAULT NULL,
        `page_url` VARCHAR(500) DEFAULT NULL,
        `device_type` ENUM('desktop','mobile','tablet') DEFAULT NULL,
        `converted` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_form_sub_form` (`form_id`),
        KEY `idx_crm_form_sub_lead` (`lead_id`),
        KEY `idx_crm_form_sub_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 6. crm_campaigns â€” ad/marketing campaigns with lead tracking
    "CREATE TABLE IF NOT EXISTS `crm_campaigns` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(200) NOT NULL,
        `campaign_type` ENUM('google_ads','facebook_ads','instagram_ads','whatsapp_broadcast','sms_blast','email_blast','referral','organic','event','other') NOT NULL,
        `platform` VARCHAR(50) DEFAULT NULL,
        `budget` DECIMAL(12,2) DEFAULT 0,
        `spent` DECIMAL(12,2) DEFAULT 0,
        `target_audience` TEXT DEFAULT NULL,
        `target_locations` VARCHAR(500) DEFAULT NULL,
        `start_date` DATE DEFAULT NULL,
        `end_date` DATE DEFAULT NULL,
        `landing_page_url` VARCHAR(500) DEFAULT NULL,
        `tracking_code` VARCHAR(100) DEFAULT NULL,
        `total_impressions` INT(11) DEFAULT 0,
        `total_clicks` INT(11) DEFAULT 0,
        `total_leads` INT(11) DEFAULT 0,
        `total_conversions` INT(11) DEFAULT 0,
        `cost_per_lead` DECIMAL(10,2) DEFAULT 0,
        `status` ENUM('draft','active','paused','completed','archived') DEFAULT 'draft',
        `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_campaigns_type` (`campaign_type`),
        KEY `idx_crm_campaigns_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 7. crm_tasks â€” follow-up tasks for each user
    "CREATE TABLE IF NOT EXISTS `crm_tasks` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` BIGINT(20) UNSIGNED DEFAULT NULL,
        `assigned_to` BIGINT(20) UNSIGNED NOT NULL,
        `created_by` BIGINT(20) UNSIGNED NOT NULL,
        `task_type` ENUM('call','sms','email','whatsapp','visit','meeting','follow_up','other') NOT NULL DEFAULT 'follow_up',
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
        `status` ENUM('pending','in_progress','completed','skipped','overdue') DEFAULT 'pending',
        `due_date` DATE NOT NULL,
        `due_time` TIME DEFAULT NULL,
        `completed_at` DATETIME DEFAULT NULL,
        `completed_notes` TEXT DEFAULT NULL,
        `reminder_at` DATETIME DEFAULT NULL,
        `reminder_sent` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_tasks_lead` (`lead_id`),
        KEY `idx_crm_tasks_assigned` (`assigned_to`, `status`),
        KEY `idx_crm_tasks_due` (`due_date`, `status`),
        KEY `idx_crm_tasks_overdue` (`status`, `due_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 8. crm_lead_scores_history â€” scoring history trail
    "CREATE TABLE IF NOT EXISTS `crm_lead_scores_history` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` BIGINT(20) UNSIGNED NOT NULL,
        `old_score` INT(11) DEFAULT 0,
        `new_score` INT(11) DEFAULT 0,
        `score_factors` JSON DEFAULT NULL,
        `scored_by` ENUM('system','admin','ai') DEFAULT 'system',
        `reason` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_score_history_lead` (`lead_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 9. crm_whatsapp_messages â€” WhatsApp message tracking
    "CREATE TABLE IF NOT EXISTS `crm_whatsapp_messages` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` BIGINT(20) UNSIGNED NOT NULL,
        `user_id` BIGINT(20) UNSIGNED NOT NULL,
        `message` TEXT NOT NULL,
        `message_type` ENUM('text','image','document','template','location') DEFAULT 'text',
        `template_name` VARCHAR(100) DEFAULT NULL,
        `direction` ENUM('inbound','outbound') DEFAULT 'outbound',
        `status` ENUM('sent','delivered','read','failed','pending') DEFAULT 'pending',
        `whatsapp_message_id` VARCHAR(100) DEFAULT NULL,
        `error_message` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_wa_lead` (`lead_id`),
        KEY `idx_crm_wa_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 10. crm_lead_sources_extended â€” detailed source tracking per lead
    "CREATE TABLE IF NOT EXISTS `crm_lead_sources_extended` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` BIGINT(20) UNSIGNED NOT NULL,
        `campaign_id` INT(11) DEFAULT NULL,
        `form_id` INT(11) DEFAULT NULL,
        `source_type` ENUM('website','social_media','google_ads','facebook_ads','referral','walk_in','call_in','event','whatsapp','other') DEFAULT 'website',
        `source_detail` VARCHAR(200) DEFAULT NULL,
        `medium` VARCHAR(100) DEFAULT NULL,
        `utm_source` VARCHAR(100) DEFAULT NULL,
        `utm_medium` VARCHAR(100) DEFAULT NULL,
        `utm_campaign` VARCHAR(100) DEFAULT NULL,
        `utm_term` VARCHAR(100) DEFAULT NULL,
        `utm_content` VARCHAR(100) DEFAULT NULL,
        `gclid` VARCHAR(200) DEFAULT NULL,
        `fbclid` VARCHAR(200) DEFAULT NULL,
        `landing_page` VARCHAR(500) DEFAULT NULL,
        `referrer` VARCHAR(500) DEFAULT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `city` VARCHAR(100) DEFAULT NULL,
        `state` VARCHAR(100) DEFAULT NULL,
        `country` VARCHAR(50) DEFAULT NULL,
        `device` VARCHAR(100) DEFAULT NULL,
        `browser` VARCHAR(100) DEFAULT NULL,
        `os` VARCHAR(100) DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_crm_source_lead` (`lead_id`),
        KEY `idx_crm_source_type` (`source_type`),
        KEY `idx_crm_source_campaign` (`campaign_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
];

echo "\n=== Creating CRM Tables ===\n";
$created = 0;
foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        preg_match('/`(\w+)`/', $sql, $m);
        echo "  âœ“ {$m[1]}\n";
        $created++;
    } catch (PDOException $e) {
        echo "  âœ— Error: " . $e->getMessage() . "\n";
    }
}
echo "Created: {$created}/" . count($tables) . " tables\n";

// Seed default pipeline stages
echo "\n=== Seeding Pipeline Stages ===\n";
$stages = [
    ['New Lead',      'new',       '#10b981', 'fiber_new',          1, 'all', 0, 0],
    ['Contacted',     'contacted', '#3b82f6', 'phone_in_talk',      2, 'all', 0, 0],
    ['Qualified',     'qualified', '#8b5cf6', 'verified',           3, 'all', 0, 0],
    ['Site Visit',    'site_visit','#f59e0b', 'location_on',        4, 'all', 0, 0],
    ['Proposal',      'proposal',  '#ec4899', 'description',        5, 'all', 0, 0],
    ['Negotiation',   'negotiation','#ef4444','handshake',          6, 'all', 0, 0],
    ['Booking',       'booking',   '#06b6d4', 'event_available',    7, 'all', 0, 0],
    ['Closed Won',    'won',       '#22c55e', 'check_circle',       8, 'all', 1, 0],
    ['Closed Lost',   'lost',      '#64748b', 'cancel',             9, 'all', 0, 1],
    ['Nurture',       'nurture',   '#f97316', 'favorite',          10, 'all', 0, 0],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO crm_pipeline_stages (name, slug, color, icon, order_index, role, is_won, is_lost) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$seeded = 0;
foreach ($stages as $s) {
    $stmt->execute($s);
    if ($stmt->rowCount() > 0) $seeded++;
}
echo "  âœ“ {$seeded} pipeline stages seeded\n";

// Seed default lead capture form
echo "\n=== Seeding Lead Capture Form ===\n";
$formFields = json_encode([
    ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
    ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true],
    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => false],
    ['name' => 'budget', 'label' => 'Budget (â‚¹)', 'type' => 'number', 'required' => false],
    ['name' => 'location', 'label' => 'Preferred Location', 'type' => 'text', 'required' => false],
    ['name' => 'property_type', 'label' => 'Property Type', 'type' => 'select', 'options' => ['Plot', 'House', 'Flat', 'Shop'], 'required' => false],
    ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => false],
]);
$formSql = "INSERT IGNORE INTO crm_lead_forms (name, form_code, description, fields_config, source_tag) VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($formSql);
$stmt->execute(['Website Enquiry', 'WEB_ENQ', 'Main website enquiry form', $formFields, 'website']);
$stmt->execute(['WhatsApp Bot', 'WA_BOT', 'WhatsApp chatbot capture', $formFields, 'whatsapp']);
$stmt->execute(['Walk-in', 'WALKIN', 'Office walk-in capture', $formFields, 'walk_in']);
$stmt->execute(['Facebook Lead', 'FB_LEAD', 'Facebook ad campaign form', $formFields, 'facebook_ads']);
echo "  âœ“ 4 lead capture forms seeded\n";

// Seed campaigns
echo "\n=== Seeding Campaigns ===\n";
$campaigns = [
    ['Facebook Campaign - Gorakhpur', 'facebook_ads', 'Facebook', 50000, 'Residential plots in Gorakhpur', 'Gorakhpur, Lucknow', '2026-01-01', '2026-06-30', null, null, 'active'],
    ['Google Ads - Plots India', 'google_ads', 'Google', 100000, 'All locations', 'Pan India', '2026-01-01', '2026-12-31', null, null, 'active'],
    ['Instagram Reels', 'instagram_ads', 'Instagram', 30000, 'Young buyers 25-40', 'Gorakhpur, Lucknow', '2026-03-01', '2026-09-30', null, null, 'active'],
    ['WhatsApp Broadcast', 'whatsapp_broadcast', 'WhatsApp', 10000, 'Existing leads re-engagement', 'All', '2026-01-01', '2026-12-31', null, null, 'active'],
    ['Referral Program', 'referral', 'Internal', 0, 'Associate referral program', 'All', '2026-01-01', '2026-12-31', null, null, 'active'],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO crm_campaigns (name, campaign_type, platform, budget, target_audience, target_locations, start_date, end_date, landing_page_url, tracking_code, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($campaigns as $c) {
    $stmt->execute($c);
}
echo "  âœ“ 5 campaigns seeded\n";

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "\nâœ… CRM Migration Complete! {$created} tables created.\n";?>