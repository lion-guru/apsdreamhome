<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

$db = new App\Core\Database();
$pdo = $db->getPdo();

$tables = [
    'chat_sessions' => "CREATE TABLE IF NOT EXISTS chat_sessions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_token VARCHAR(64) NOT NULL UNIQUE,
        visitor_id BIGINT UNSIGNED NULL,
        user_id BIGINT UNSIGNED NULL,
        visitor_name VARCHAR(120) NULL,
        visitor_email VARCHAR(180) NULL,
        visitor_phone VARCHAR(30) NULL,
        assigned_agent_id BIGINT UNSIGNED NULL,
        agent_name VARCHAR(120) NULL,
        source VARCHAR(40) NULL DEFAULT 'website',
        page_url VARCHAR(500) NULL,
        referrer_url VARCHAR(500) NULL,
        ip_address VARCHAR(64) NULL,
        user_agent VARCHAR(500) NULL,
        country VARCHAR(80) NULL,
        status ENUM('open','assigned','active','on_hold','closed','missed') NOT NULL DEFAULT 'open',
        priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
        category VARCHAR(60) NULL,
        subject VARCHAR(200) NULL,
        message_count INT UNSIGNED NOT NULL DEFAULT 0,
        unread_admin_count INT UNSIGNED NOT NULL DEFAULT 0,
        unread_visitor_count INT UNSIGNED NOT NULL DEFAULT 0,
        last_message_at DATETIME NULL,
        last_message_by ENUM('visitor','agent','system') NULL,
        first_response_at DATETIME NULL,
        closed_at DATETIME NULL,
        closed_by BIGINT UNSIGNED NULL,
        close_reason VARCHAR(120) NULL,
        rating TINYINT UNSIGNED NULL,
        feedback_text TEXT NULL,
        tags VARCHAR(500) NULL,
        metadata JSON NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_visitor (visitor_id),
        INDEX idx_status (status),
        INDEX idx_agent (assigned_agent_id),
        INDEX idx_last_msg (last_message_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'chat_messages' => "CREATE TABLE IF NOT EXISTS chat_messages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_id BIGINT UNSIGNED NOT NULL,
        sender_type ENUM('visitor','agent','system','bot') NOT NULL,
        sender_id BIGINT UNSIGNED NULL,
        sender_name VARCHAR(120) NULL,
        message TEXT NOT NULL,
        message_type ENUM('text','image','file','emoji','quick_reply','card','system') NOT NULL DEFAULT 'text',
        attachment_url VARCHAR(500) NULL,
        attachment_name VARCHAR(255) NULL,
        attachment_size INT UNSIGNED NULL,
        is_internal_note TINYINT(1) NOT NULL DEFAULT 0,
        read_by_visitor TINYINT(1) NOT NULL DEFAULT 0,
        read_by_agent TINYINT(1) NOT NULL DEFAULT 0,
        read_at DATETIME NULL,
        edited_at DATETIME NULL,
        deleted_at DATETIME NULL,
        metadata JSON NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_sender (sender_type, sender_id),
        INDEX idx_created (created_at),
        INDEX idx_unread_visitor (session_id, read_by_visitor),
        INDEX idx_unread_agent (session_id, read_by_agent)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'chat_quick_replies' => "CREATE TABLE IF NOT EXISTS chat_quick_replies (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(120) NOT NULL,
        shortcut VARCHAR(40) NULL,
        message TEXT NOT NULL,
        category VARCHAR(60) NULL,
        sort_order INT UNSIGNED NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        usage_count INT UNSIGNED NOT NULL DEFAULT 0,
        created_by BIGINT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'chat_canned_responses' => "CREATE TABLE IF NOT EXISTS chat_canned_responses (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(120) NOT NULL,
        body TEXT NOT NULL,
        category VARCHAR(60) NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_by BIGINT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_active (is_active),
        INDEX idx_category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'chat_widget_settings' => "CREATE TABLE IF NOT EXISTS chat_widget_settings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(80) NOT NULL UNIQUE,
        setting_value TEXT NULL,
        updated_by BIGINT UNSIGNED NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'chat_agents' => "CREATE TABLE IF NOT EXISTS chat_agents (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL UNIQUE,
        display_name VARCHAR(120) NOT NULL,
        avatar_url VARCHAR(500) NULL,
        title VARCHAR(120) NULL,
        departments VARCHAR(500) NULL,
        max_concurrent_chats INT UNSIGNED NOT NULL DEFAULT 5,
        is_online TINYINT(1) NOT NULL DEFAULT 0,
        is_available TINYINT(1) NOT NULL DEFAULT 0,
        last_seen_at DATETIME NULL,
        total_chats INT UNSIGNED NOT NULL DEFAULT 0,
        avg_rating DECIMAL(3,2) NULL,
        avg_response_time_seconds INT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_online (is_online, is_available)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: $name\n";
    } catch (Exception $e) {
        echo "ERR: $name - " . $e->getMessage() . "\n";
    }
}

$replies = [
    ['Greeting', '/hello', 'Hi there! ðŸ‘‹ How can I help you today?', 'general', 1],
    ['Property Inquiry', '/prop', 'Thanks for your interest! Which property are you inquiring about?', 'sales', 2],
    ['Pricing Info', '/price', 'Pricing depends on the specific property. Could you share which one you are interested in?', 'sales', 3],
    ['Site Visit', '/visit', 'I can help schedule a site visit. Which date works best for you?', 'sales', 4],
    ['Loan Query', '/loan', 'For home loan assistance, please share your income range and preferred loan amount.', 'finance', 5],
    ['Documents Needed', '/docs', 'Typical documents: ID proof, address proof, income proof, bank statements. I can email you the full list.', 'legal', 6],
    ['Thank You', '/ty', 'Thank you for contacting APS Dream Home! We will get back to you shortly.', 'general', 7],
    ['Follow Up', '/fu', 'Just following up on our previous conversation. Do you have any other questions?', 'general', 8]
];
foreach ($replies as $r) {
    try {
        $pdo->prepare("INSERT IGNORE INTO chat_quick_replies (title, shortcut, message, category, sort_order) VALUES (?,?,?,?,?)")
            ->execute($r);
    } catch (Exception $e) { /* skip dup */ }
}

$settings = [
    'widget_enabled' => '1',
    'widget_position' => 'bottom-right',
    'widget_color' => '#007bff',
    'widget_title' => 'APS Dream Home Support',
    'widget_subtitle' => 'We typically reply in a few minutes',
    'business_hours_only' => '0',
    'business_hours_start' => '09:00',
    'business_hours_end' => '19:00',
    'auto_assign' => '1',
    'welcome_message' => 'ðŸ‘‹ Welcome to APS Dream Home! How can we help you with your property journey today?',
    'offline_message' => 'We are currently offline. Please leave a message and we will get back to you soon.'
];
foreach ($settings as $k => $v) {
    try {
        $pdo->prepare("INSERT IGNORE INTO chat_widget_settings (setting_key, setting_value) VALUES (?,?)")
            ->execute([$k, $v]);
    } catch (Exception $e) { /* skip */ }
}

echo "Phase 54: Live Chat tables created with " . count($replies) . " quick replies and " . count($settings) . " settings seeded\n";?>