<?php

/**
 * Application Configuration
 * 
 * This file contains all the configuration settings for the application.
 * For security reasons, this file should be kept outside the web root in production.
 */

// Application Settings
define('APP_NAME', 'APS Dream Home');
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/');
}
define('ROOT', __DIR__ . '/../');

// Database Configuration - Use defined() to prevent redefinition
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'apsdreamhome'); // Updated database name
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: ''));
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', DB_PASS);
}

// Email Configuration
$config['email'] = [
    'enabled' => true,
    'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'smtp_port' => getenv('SMTP_PORT') ?: 587,
    'smtp_username' => getenv('SMTP_USERNAME') ?: 'apsdreamhomes44@gmail.com',
    'smtp_password' => getenv('SMTP_PASSWORD') ?: 'Aps@1601',
    'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
    'from_email' => getenv('MAIL_FROM_ADDRESS') ?: 'apsdreamhomes44@gmail.com',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'APS Dream Home',
    'reply_to' => getenv('MAIL_REPLY_TO') ?: 'apsdreamhomes44@gmail.com',
    'bcc_admin' => true,
    'admin_email' => getenv('ADMIN_EMAIL') ?: 'apsdreamhomes44@gmail.com'
];

// WhatsApp Configuration
$config['whatsapp'] = [
    'enabled' => true,
    'phone_number' => getenv('WHATSAPP_PHONE') ?: '9277121112',
    'country_code' => getenv('WHATSAPP_COUNTRY_CODE') ?: '91',
    'api_provider' => getenv('WHATSAPP_PROVIDER') ?: 'whatsapp_business_api', // or 'twilio', 'whatsapp_web'
    'business_account_id' => getenv('WHATSAPP_BUSINESS_ID') ?: '', // For WhatsApp Business API
    'access_token' => getenv('WHATSAPP_TOKEN') ?: '', // For WhatsApp Business API
    'webhook_verify_token' => getenv('WHATSAPP_VERIFY_TOKEN') ?: 'aps_dream_home_webhook_token',
    'notification_types' => [
        'welcome_message' => true,
        'property_inquiry' => true,
        'booking_confirmation' => true,
        'payment_reminder' => true,
        'appointment_reminder' => true,
        'commission_alert' => true,
        'system_alerts' => true
    ],
    'auto_responses' => [
        'greeting_hours' => '09:00-18:00',
        'welcome_message' => 'Welcome to APS Dream Home! 🏠 How can we help you find your dream property today?',
        'away_message' => 'Thank you for your message! Our team is currently away. We\'ll respond to you within 24 hours.',
        'business_hours' => 'Mon-Fri: 9:00 AM - 6:00 PM, Sat: 9:00 AM - 2:00 PM'
    ]
];

// Include WhatsApp system if enabled
if ($config['whatsapp']['enabled']) {
    require_once __DIR__ . '/whatsapp_integration.php';
}

// AI Configuration
$config['ai'] = [
    'enabled' => true,
    'provider' => getenv('AI_PROVIDER') ?: 'openrouter',
    'api_key' => getenv('AI_API_KEY') ?: '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',
    'model' => getenv('AI_MODEL') ?: 'qwen/qwen3-coder:free',
    'features' => [
        'property_descriptions' => true,
        'property_valuation' => true,
        'chatbot' => true,
        'recommendations' => true,
        'market_analysis' => true,
        'investment_insights' => true,
        'marketing_content' => true,
        'code_analysis' => true,
        'development_assistance' => true
    ]
];

// Include AI personality system
require_once __DIR__ . '/ai_personality_system.php';

// Include AI learning system
require_once __DIR__ . '/ai_learning_system.php';
