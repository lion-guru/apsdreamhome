<?php
/**
 * Migration for Session 207:
 * 1. whatsapp_flows (dynamic chatbot flow builder)
 * 2. whatsapp_message_logs (two-way conversation stream)
 * 3. commission_advances (associate commission advance & recovery desk)
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "Running migrations for Session 207...\n";

// 1. whatsapp_flows
$pdo->exec("
    CREATE TABLE IF NOT EXISTS whatsapp_flows (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        title VARCHAR(100) NOT NULL,
        trigger_keyword VARCHAR(100) NOT NULL,
        match_type ENUM('exact', 'contains', 'regex') NOT NULL DEFAULT 'contains',
        action_hook VARCHAR(50) NULL,
        reply_message TEXT NOT NULL,
        interactive_buttons_json TEXT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        priority INT(11) NOT NULL DEFAULT 10,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_wa_flows_tenant (tenant_id),
        INDEX idx_wa_flows_active (is_active, priority)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Seed default standard flows if empty
$flowCount = (int)$pdo->query("SELECT COUNT(*) FROM whatsapp_flows")->fetchColumn();
if ($flowCount === 0) {
    echo "Seeding default WhatsApp bot flows...\n";
    $flows = [
        [
            'title' => 'Available Townships & Plots',
            'trigger_keyword' => '1,plot,township,colony,rate,price,flat,land,jameen',
            'match_type' => 'contains',
            'action_hook' => 'fetch_plots',
            'reply_message' => "*APS Dream Home — New Townships*\nExplore our RERA approved gated townships with wide roads, parks, and 24x7 security.\n\nTap to explore vacant plots:\n{{base_url}}/properties\n\nReply 2 to book a FREE Sunday Cab Site Visit.",
            'priority' => 10,
        ],
        [
            'title' => 'Sunday Site Visit Cab Booking',
            'trigger_keyword' => '2,visit,cab,site,sunday,tour,dekhna,pickup',
            'match_type' => 'contains',
            'action_hook' => 'book_visit',
            'reply_message' => "*FREE Sunday Site Visit Request Noted!*\nPickup Hubs: Railway Station, Golghar, Medical College Road.\n\nOur fleet coordinator will call you to confirm your cab timing.\nMeanwhile, explore live layout: {{base_url}}/properties",
            'priority' => 20,
        ],
        [
            'title' => 'Booking & EMI Passbook Self-Service',
            'trigger_keyword' => '3,emi,passbook,receipt,due,kist,payment',
            'match_type' => 'contains',
            'action_hook' => 'fetch_passbook',
            'reply_message' => "*Booking & EMI Passbook*\nCheck your outstanding balance, download official receipts, or pay online:\n{{base_url}}/login\n\nFor assistance, call support at +91 9876543210.",
            'priority' => 30,
        ],
        [
            'title' => 'Associate Partner Onboarding',
            'trigger_keyword' => '4,join,associate,agent,earn,job,kamana,commission',
            'match_type' => 'contains',
            'action_hook' => 'create_lead',
            'reply_message' => "*Become an APS Associate & Earn Rs. 50,000+/Month!*\nJoin Eastern UP's fastest growing developer network with weekly payouts and full marketing support.\n\nRegister free: {{base_url}}/register\nOur associate manager will contact you with details.",
            'priority' => 40,
        ],
    ];

    $st = $pdo->prepare("
        INSERT INTO whatsapp_flows (tenant_id, title, trigger_keyword, match_type, action_hook, reply_message, priority, is_active)
        VALUES (1, ?, ?, ?, ?, ?, ?, 1)
    ");
    foreach ($flows as $f) {
        $st->execute([$f['title'], $f['trigger_keyword'], $f['match_type'], $f['action_hook'], $f['reply_message'], $f['priority']]);
    }
}

// 2. whatsapp_message_logs
$pdo->exec("
    CREATE TABLE IF NOT EXISTS whatsapp_message_logs (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        phone_number VARCHAR(20) NOT NULL,
        direction ENUM('inbound', 'outbound') NOT NULL,
        message_body TEXT NOT NULL,
        flow_matched VARCHAR(100) NULL,
        provider_message_id VARCHAR(100) NULL,
        status VARCHAR(50) DEFAULT 'delivered',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_wa_logs_phone (phone_number),
        INDEX idx_wa_logs_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 3. commission_advances
$pdo->exec("
    CREATE TABLE IF NOT EXISTS commission_advances (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        associate_id INT(11) NOT NULL,
        requested_amount DECIMAL(15,2) NOT NULL,
        approved_amount DECIMAL(15,2) DEFAULT 0.00,
        tenure_months INT(11) NOT NULL DEFAULT 3,
        deduction_pct DECIMAL(5,2) NOT NULL DEFAULT 25.00,
        recovered_amount DECIMAL(15,2) DEFAULT 0.00,
        status ENUM('pending', 'approved', 'disbursed', 'rejected', 'repaid') NOT NULL DEFAULT 'pending',
        purpose VARCHAR(255) NOT NULL,
        disbursement_ref VARCHAR(100) NULL,
        admin_remarks TEXT NULL,
        approved_by INT(11) NULL,
        disbursed_at DATETIME NULL,
        repaid_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_comm_advances_assoc (associate_id),
        INDEX idx_comm_advances_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "Migration for Session 207 completed successfully!\n";
