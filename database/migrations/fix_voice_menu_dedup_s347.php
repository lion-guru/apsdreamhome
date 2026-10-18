<?php
/**
 * Fix: Deduplicate voice menu items + ensure credential keys exist
 */
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Deduplicate: keep lowest id per name+url
$pdo->exec("
DELETE t1 FROM admin_menu_items t1
JOIN admin_menu_items t2 
  ON t1.name = t2.name AND t1.url = t2.url AND t1.id > t2.id
WHERE t1.section = 'ai_tech' 
  AND t1.name IN ('AI Voice Calling','Voice Agents','IVR Flows','Call Logs','Voice Analytics','Telecalling Dashboard')
");
echo "Deduplicated menu items.\n";

// Ensure credential keys exist in service_configs
$keys = [
    // Exotel
    ['exotel_sid', 'voice_exotel', 'text', 'Exotel Account SID', 'ai_voice', ''],
    ['exotel_token', 'voice_exotel', 'password', 'Exotel Auth Token', 'ai_voice', ''],
    ['exotel_number', 'voice_exotel', 'text', 'Exotel Virtual Number (E.164)', 'ai_voice', ''],
    ['exotel_app_id', 'voice_exotel', 'text', 'Exotel App ID for IVR', 'ai_voice', ''],
    // Twilio
    ['twilio_sid', 'voice_twilio', 'text', 'Twilio Account SID', 'ai_voice', ''],
    ['twilio_token', 'voice_twilio', 'password', 'Twilio Auth Token', 'ai_voice', ''],
    ['twilio_number', 'voice_twilio', 'text', 'Twilio Phone Number (E.164)', 'ai_voice', ''],
    // Plivo
    ['plivo_auth_id', 'voice_plivo', 'text', 'Plivo Auth ID', 'ai_voice', ''],
    ['plivo_auth_token', 'voice_plivo', 'password', 'Plivo Auth Token', 'ai_voice', ''],
    ['plivo_number', 'voice_plivo', 'text', 'Plivo Phone Number (E.164)', 'ai_voice', ''],
    // Knowlarity
    ['knowlarity_api_key', 'voice_knowlarity', 'password', 'Knowlarity API Key', 'ai_voice', ''],
    ['knowlarity_sid', 'voice_knowlarity', 'text', 'Knowlarity SID', 'ai_voice', ''],
    ['knowlarity_number', 'voice_knowlarity', 'text', 'Knowlarity Virtual Number', 'ai_voice', ''],
];

$stmt = $pdo->prepare("
INSERT IGNORE INTO service_configs (config_key, service_name, config_type, description, group_name, config_value)
VALUES (?, ?, ?, ?, ?, ?)
");
foreach ($keys as $k) {
    $stmt->execute($k);
}
echo "Credential keys inserted/verified.\n";

// Sidebar cache bust
\App\Core\Cache::delete('admin_sidebar_all');
echo "Sidebar cache busted.\n";