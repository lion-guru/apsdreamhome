<?php
/**
 * Migration: Create service_configs table + seed default rows.
 *
 * Run: php scripts/create_service_configs_table.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// â”€â”€ Create table â”€â”€
$pdo->exec("CREATE TABLE IF NOT EXISTS `service_configs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `service_name` varchar(100) NOT NULL COMMENT 'e.g. leegality, gstn, tin, razorpay, exchange_rate',
    `config_key` varchar(100) NOT NULL COMMENT 'e.g. api_key, secret, test_mode, username',
    `config_value` text DEFAULT NULL COMMENT 'Encrypted value for secrets',
    `config_type` enum('text','password','boolean','json','number') DEFAULT 'text',
    `description` varchar(255) DEFAULT NULL,
    `is_secret` tinyint(1) DEFAULT 0 COMMENT '1 = mask in UI, store encrypted',
    `group_name` varchar(50) DEFAULT 'general' COMMENT 'UI grouping',
    `sort_order` int(11) DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_service_key` (`service_name`,`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

echo "âœ“ Table service_configs created\n";

// â”€â”€ Seed defaults â”€â”€
$rows = [
    // Leegality
    ['leegality', 'api_key',    '',    'password', 'Leegality API key',                        1, 'integrations', 10],
    ['leegality', 'test_mode',  '1',   'boolean',  'Use Leegality sandbox environment',        0, 'integrations', 20],

    // GSTN
    ['gstn', 'gstin',     '',    'text',     'GST Identification Number',                   0, 'tax', 10],
    ['gstn', 'username',  '',    'text',     'GSTN portal username',                        0, 'tax', 20],
    ['gstn', 'password',  '',    'password', 'GSTN portal password',                        1, 'tax', 30],
    ['gstn', 'api_key',   '',    'password', 'GSTN API key',                                1, 'tax', 40],
    ['gstn', 'test_mode', '1',   'boolean',  'Use GSTN sandbox environment',                0, 'tax', 50],

    // TIN
    ['tin', 'username',  '',    'text',     'TIN portal username',                          0, 'tax', 10],
    ['tin', 'password',  '',    'password', 'TIN portal password',                          1, 'tax', 20],
    ['tin', 'api_key',   '',    'password', 'TIN API key',                                  1, 'tax', 30],
    ['tin', 'test_mode', '1',   'boolean',  'Use TIN sandbox environment',                  0, 'tax', 40],

    // Razorpay
    ['razorpay', 'key_id',        '',    'password', 'Razorpay Key ID',                         1, 'payments', 10],
    ['razorpay', 'key_secret',    '',    'password', 'Razorpay Key Secret',                     1, 'payments', 20],
    ['razorpay', 'webhook_secret','',    'password', 'Razorpay Webhook Signing Secret',         1, 'payments', 30],
    ['razorpay', 'test_mode',     '1',   'boolean',  'Use Razorpay test mode',                  0, 'payments', 40],

    // Twilio
    ['twilio', 'account_sid',     '',    'password', 'Twilio Account SID',                      1, 'communications', 10],
    ['twilio', 'auth_token',      '',    'password', 'Twilio Auth Token',                       1, 'communications', 20],
    ['twilio', 'from_number',     '',    'text',     'Twilio phone number (E.164)',             0, 'communications', 30],
    ['twilio', 'whatsapp_number', '',    'text',     'Twilio WhatsApp number (E.164)',          0, 'communications', 40],
    ['twilio', 'test_mode',       '1',   'boolean',  'Use Twilio sandbox environment',          0, 'communications', 50],

    // AWS S3
    ['aws_s3', 'access_key',     '',    'password', 'AWS Access Key ID',                        1, 'storage', 10],
    ['aws_s3', 'secret_key',     '',    'password', 'AWS Secret Access Key',                    1, 'storage', 20],
    ['aws_s3', 'region',         'ap-south-1', 'text', 'AWS region',                            0, 'storage', 30],
    ['aws_s3', 'bucket',         '',    'text',     'S3 bucket name',                           0, 'storage', 40],
    ['aws_s3', 'use_path_style', '0',   'boolean',  'Use path-style URLs (for MinIO/Spaces)',  0, 'storage', 50],

    // Exchange Rate
    ['exchange_rate', 'primary_api', 'https://open.er-api.com/v6/latest/INR', 'text', 'Primary exchange rate API URL', 0, 'integrations', 10],
    ['exchange_rate', 'fallback_api','',    'text',     'Fallback exchange rate API URL',         0, 'integrations', 20],
    ['exchange_rate', 'cache_ttl',   '3600','number',   'Cache TTL in seconds',                   0, 'integrations', 30],
    ['exchange_rate', 'test_mode',   '1',   'boolean',  'Use test mode',                          0, 'integrations', 40],

    // SMTP
    ['smtp', 'host',        '',    'text',     'SMTP server hostname',                        0, 'communications', 10],
    ['smtp', 'port',        '587', 'number',   'SMTP server port',                            0, 'communications', 20],
    ['smtp', 'username',    '',    'text',     'SMTP username',                               0, 'communications', 30],
    ['smtp', 'password',    '',    'password', 'SMTP password',                               1, 'communications', 40],
    ['smtp', 'from_email',  '',    'text',     'Default from email address',                  0, 'communications', 50],
    ['smtp', 'from_name',   '',    'text',     'Default from name',                           0, 'communications', 60],
    ['smtp', 'encryption',  'tls', 'text',     'Encryption: tls, ssl, or none',               0, 'communications', 70],

    // General
    ['general', 'app_name',       'APS Dream Home', 'text',    'Application name',                    0, 'general', 10],
    ['general', 'app_url',        '',                'text',    'Application base URL',                0, 'general', 20],
    ['general', 'support_email',  '',                'text',    'Support email address',               0, 'general', 30],
    ['general', 'support_phone',  '',                'text',    'Support phone number',                0, 'general', 40],
    ['general', 'company_name',   '',                'text',    'Registered company name',             0, 'general', 50],
    ['general', 'company_cin',    '',                'text',    'Corporate Identity Number',           0, 'general', 60],
    ['general', 'company_gstin',  '',                'text',    'Company GSTIN',                       0, 'general', 70],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO `service_configs`
    (`service_name`,`config_key`,`config_value`,`config_type`,`description`,`is_secret`,`group_name`,`sort_order`)
    VALUES (?,?,?,?,?,?,?,?)");

$inserted = 0;
foreach ($rows as $r) {
    $stmt->execute($r);
    $inserted += $stmt->rowCount();
}

echo "âœ“ Seeded {$inserted} new config rows (total attempted: " . count($rows) . ")\n";
echo "Done.\n";?>