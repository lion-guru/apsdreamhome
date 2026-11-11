<?php
/**
 * Migration: Create voice provider configurations
 * Standalone migration script with direct DB connection
 */

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$providers = [
    [
        'group_name' => 'voice',
        'service_name' => 'exotel',
        'config_key' => 'exotel',
        'config_value' => json_encode([
            'account_sid' => '',
            'api_key' => '',
            'api_token' => '',
            'caller_id' => '',
            'fallback_number' => '',
            'signature_secret' => '',
            'priority' => 1,
        ]),
        'config_type' => 'json',
        'description' => 'Exotel voice provider configuration (India)',
        'is_secret' => 1,
        'sort_order' => 1,
    ],
    [
        'group_name' => 'voice',
        'service_name' => 'twilio',
        'config_key' => 'twilio',
        'config_value' => json_encode([
            'account_sid' => '',
            'auth_token' => '',
            'caller_id' => '',
            'voice_webhook_url' => '',
            'fallback_number' => '',
            'priority' => 2,
        ]),
        'config_type' => 'json',
        'description' => 'Twilio voice provider configuration (Global)',
        'is_secret' => 1,
        'sort_order' => 2,
    ],
    [
        'group_name' => 'voice',
        'service_name' => 'plivo',
        'config_key' => 'plivo',
        'config_value' => json_encode([
            'auth_id' => '',
            'auth_token' => '',
            'caller_id' => '',
            'answer_url' => '',
            'priority' => 3,
        ]),
        'config_type' => 'json',
        'description' => 'Plivo voice provider configuration (Global)',
        'is_secret' => 1,
        'sort_order' => 3,
    ],
    [
        'group_name' => 'voice',
        'service_name' => 'knowlarity',
        'config_key' => 'knowlarity',
        'config_value' => json_encode([
            'api_key' => '',
            'secret_key' => '',
            'caller_id' => '',
            'webhook_secret' => '',
            'priority' => 4,
        ]),
        'config_type' => 'json',
        'description' => 'Knowlarity voice provider configuration (India)',
        'is_secret' => 1,
        'sort_order' => 4,
    ],
    [
        'group_name' => 'voice',
        'service_name' => 'kaleyra',
        'config_key' => 'kaleyra',
        'config_value' => json_encode([
            'api_key' => '',
            'secret_key' => '',
            'sender_id' => '',
            'priority' => 5,
        ]),
        'config_type' => 'json',
        'description' => 'Kaleyra voice provider configuration (Global)',
        'is_secret' => 1,
        'sort_order' => 5,
    ],
];

echo "Seeding voice provider configurations...\n";

foreach ($providers as $provider) {
    $stmt = $pdo->prepare("SELECT id FROM service_configs WHERE group_name = 'voice' AND service_name = ?");
    $stmt->execute([$provider['service_name']]);
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $stmt = $pdo->prepare("
            INSERT INTO service_configs 
            (group_name, service_name, config_key, config_value, config_type, description, is_secret, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $provider['group_name'],
            $provider['service_name'],
            $provider['config_key'],
            $provider['config_value'],
            $provider['config_type'],
            $provider['description'],
            $provider['is_secret'],
            $provider['sort_order'],
        ]);
        echo "Created: {$provider['service_name']}\n";
    } else {
        echo "Exists: {$provider['service_name']}\n";
    }
}

echo "Voice provider configurations seeded successfully.\n";