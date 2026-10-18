<?php
/**
 * Migration: Voice/IVR Provider Configuration Seeds (AI Calling)
 * Idempotent: INSERT IGNORE for admin_menu_items + service_configs
 * Run AFTER Exotel/Twilio credentials are entered via /admin/service-configs
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// 1. Ensure Voice/IVR menu items exist (idempotent) - column is `order_index`
$menuItems = [
    ['AI Voice Calling', '/admin/voice-calling', 'phone', 'ai_tech', 10, 1, 'voice_calling.view'],
    ['Voice Agents', '/admin/voice-agents', 'bot', 'ai_tech', 20, 1, 'voice_agents.view'],
    ['IVR Flows', '/admin/ivr-flows', 'git-branch', 'ai_tech', 30, 1, 'ivr_flows.view'],
    ['Call Logs', '/admin/call-logs', 'phone-call', 'ai_tech', 40, 1, 'call_logs.view'],
    ['Voice Analytics', '/admin/voice-analytics', 'bar-chart-2', 'ai_tech', 50, 1, 'voice_analytics.view'],
    ['Telecalling Dashboard', '/admin/telecalling', 'users', 'ai_tech', 60, 1, 'telecalling.view'],
];

foreach ($menuItems as $m) {
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO `admin_menu_items` (`name`, `url`, `icon`, `section`, `order_index`, `is_active`, `permission_key`)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute($m);
}

// 2. Service configs for voice providers (owner fills via GUI) - columns: service_name, group_name
$providers = [
    ['voice_exotel', 'password', 'Exotel (Recommended for India)', 'ai_voice'],
    ['voice_twilio', 'password', 'Twilio (Global)', 'ai_voice'],
    ['voice_plivo', 'password', 'Plivo (Alternative)', 'ai_voice'],
    ['voice_knowlarity', 'password', 'Knowlarity (India)', 'ai_voice'],
];

foreach ($providers as $p) {
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO `service_configs` (`service_name`, `config_key`, `config_type`, `description`, `group_name`)
        VALUES (?, 'provider_type', ?, ?, ?)
    ");
    $stmt->execute($p);
}

// 3. Config keys for each provider (owner fills values)
$configKeys = [
    // Exotel
    ['exotel_sid', 'voice_exotel', 'text', 'Exotel Account SID', 1, ''],
    ['exotel_token', 'voice_exotel', 'password', 'Exotel Auth Token', 1, ''],
    ['exotel_number', 'voice_exotel', 'text', 'Exotel Virtual Number (E.164)', 1, ''],
    ['exotel_app_id', 'voice_exotel', 'text', 'Exotel App ID for IVR', 1, ''],
    // Twilio
    ['twilio_sid', 'voice_twilio', 'text', 'Twilio Account SID', 1, ''],
    ['twilio_token', 'voice_twilio', 'password', 'Twilio Auth Token', 1, ''],
    ['twilio_number', 'voice_twilio', 'text', 'Twilio Phone Number (E.164)', 1, ''],
    // Plivo
    ['plivo_auth_id', 'voice_plivo', 'text', 'Plivo Auth ID', 1, ''],
    ['plivo_auth_token', 'voice_plivo', 'password', 'Plivo Auth Token', 1, ''],
    ['plivo_number', 'voice_plivo', 'text', 'Plivo Phone Number (E.164)', 1, ''],
    // Knowlarity
    ['knowlarity_api_key', 'voice_knowlarity', 'password', 'Knowlarity API Key', 1, ''],
    ['knowlarity_sid', 'voice_knowlarity', 'text', 'Knowlarity SID', 1, ''],
    ['knowlarity_number', 'voice_knowlarity', 'text', 'Knowlarity Virtual Number', 1, ''],
];

foreach ($configKeys as $k) {
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO `service_configs` (`config_key`, `service_name`, `config_type`, `description`, `group_name`, `config_value`)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute($k);
}

echo "DONE: Voice/IVR menu items + provider configs seeded. Owner must fill credentials via /admin/service-configs\n";