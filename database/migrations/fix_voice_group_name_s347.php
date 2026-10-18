<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Fix group_name for voice credential keys
$keys = [
    'exotel_sid','exotel_token','exotel_number','exotel_app_id',
    'twilio_sid','twilio_token','twilio_number',
    'plivo_auth_id','plivo_auth_token','plivo_number',
    'knowlarity_api_key','knowlarity_sid','knowlarity_number'
];

$stmt = $pdo->prepare("UPDATE service_configs SET group_name = 'ai_voice' WHERE config_key = ?");
foreach ($keys as $k) {
    $stmt->execute([$k]);
    echo "Updated: $k\n";
}

// Also fix the provider_type rows
$stmt2 = $pdo->prepare("UPDATE service_configs SET group_name = 'ai_voice' WHERE service_name IN ('voice_exotel','voice_twilio','voice_plivo','voice_knowlarity') AND config_key = 'provider_type'");
$stmt2->execute();
echo "Updated provider_type rows: " . $stmt2->rowCount() . "\n";

echo "DONE\n";