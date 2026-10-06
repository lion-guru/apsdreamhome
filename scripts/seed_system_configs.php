<?php
/**
 * Seed Default System Configurations
 */

$dbHost = '127.0.0.1';
$dbPort = 3306;
$dbName = 'apsdreamhome';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$tid = 1;

$defaultConfigs = [
    // General
    ['app_name', 'APS Dream Home', 'string', 'general', 'Application name', 1, 1, 0],
    ['app_tagline', 'Premium Real Estate in Uttar Pradesh', 'string', 'general', 'Application tagline', 1, 1, 1],
    ['app_logo', '/assets/images/logo.png', 'string', 'general', 'Application logo path', 1, 1, 2],
    ['app_favicon', '/assets/images/favicon.ico', 'string', 'general', 'Favicon path', 1, 1, 3],
    ['default_language', 'en', 'string', 'general', 'Default language', 1, 1, 4],
    ['supported_languages', '["en","hi"]', 'json', 'general', 'Supported languages', 1, 1, 5],
    ['timezone', 'Asia/Kolkata', 'string', 'general', 'Application timezone', 1, 1, 6],
    ['date_format', 'd-m-Y', 'string', 'general', 'Default date format', 1, 1, 7],
    ['currency', 'INR', 'string', 'general', 'Default currency', 1, 1, 8],
    ['currency_symbol', '₹', 'string', 'general', 'Currency symbol', 1, 1, 9],
    ['decimal_places', 2, 'integer', 'general', 'Decimal places for currency', 1, 1, 10],

    // System
    ['maintenance_mode', false, 'boolean', 'system', 'Enable maintenance mode', 1, 1, 10],
    ['maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.', 'string', 'system', 'Maintenance mode message', 1, 1, 11],
    ['maintenance_allowed_ips', '[]', 'json', 'system', 'Allowed IPs during maintenance', 1, 1, 12],
    ['debug_mode', false, 'boolean', 'system', 'Enable debug mode', 1, 0, 13],
    ['log_level', 'error', 'string', 'system', 'Log level (debug, info, warning, error)', 1, 1, 14],
    ['cache_enabled', true, 'boolean', 'system', 'Enable caching', 1, 1, 15],
    ['cache_ttl', 300, 'integer', 'system', 'Default cache TTL in seconds', 1, 1, 16],
    ['session_lifetime', 7200, 'integer', 'system', 'Session lifetime in seconds', 1, 1, 17],
    ['csrf_token_lifetime', 3600, 'integer', 'system', 'CSRF token lifetime in seconds', 1, 1, 18],

    // Security
    ['password_min_length', 8, 'integer', 'security', 'Minimum password length', 1, 1, 20],
    ['password_require_uppercase', true, 'boolean', 'security', 'Require uppercase in password', 1, 1, 21],
    ['password_require_lowercase', true, 'boolean', 'security', 'Require lowercase in password', 1, 1, 22],
    ['password_require_numbers', true, 'boolean', 'security', 'Require numbers in password', 1, 1, 23],
    ['password_require_symbols', true, 'boolean', 'security', 'Require symbols in password', 1, 1, 24],
    ['password_max_consecutive', 3, 'integer', 'security', 'Max consecutive identical characters', 1, 1, 25],
    ['password_block_common', true, 'boolean', 'security', 'Block common passwords', 1, 1, 26],
    ['password_block_user_info', true, 'boolean', 'security', 'Block user info in password', 1, 1, 27],
    ['password_history_count', 5, 'integer', 'security', 'Remember previous passwords', 1, 1, 28],
    ['password_expiry_days', 90, 'integer', 'security', 'Password expiry in days (0=never)', 1, 1, 29],
    ['lockout_threshold', 5, 'integer', 'security', 'Failed attempts before lockout', 1, 1, 30],
    ['lockout_duration_minutes', 15, 'integer', 'security', 'Lockout duration in minutes', 1, 1, 31],
    ['require_2fa', false, 'boolean', 'security', 'Require 2FA for all users', 1, 1, 32],
    ['remember_me_days', 30, 'integer', 'security', 'Remember me token lifetime', 1, 1, 33],
    ['session_concurrent_limit', 5, 'integer', 'security', 'Max concurrent sessions per user', 1, 1, 34],
    ['ip_whitelist', '[]', 'json', 'security', 'Whitelisted IP addresses', 1, 1, 35],
    ['ip_blacklist', '[]', 'json', 'security', 'Blacklisted IP addresses', 1, 1, 36],

    // Email
    ['mail_driver', 'smtp', 'string', 'email', 'Mail driver (smtp, sendmail, mailgun, ses)', 1, 1, 40],
    ['mail_host', 'smtp.gmail.com', 'string', 'email', 'SMTP host', 1, 1, 41],
    ['mail_port', 587, 'integer', 'email', 'SMTP port', 1, 1, 42],
    ['mail_username', '', 'string', 'email', 'SMTP username', 1, 1, 43],
    ['mail_password', '', 'string', 'email', 'SMTP password', 1, 1, 43],
    ['mail_encryption', 'tls', 'string', 'email', 'SMTP encryption (tls, ssl)', 1, 1, 44],
    ['mail_from_address', 'noreply@apsdreamhome.com', 'string', 'email', 'From address', 1, 1, 45],
    ['mail_from_name', 'APS Dream Home', 'string', 'email', 'From name', 1, 1, 46],
    ['mail_queue_enabled', true, 'boolean', 'email', 'Enable mail queue', 1, 1, 47],
    ['mail_queue_batch_size', 50, 'integer', 'email', 'Mail queue batch size', 1, 1, 48],

    // SMS
    ['sms_driver', 'msg91', 'string', 'sms', 'SMS driver (msg91, twilio, nexmo)', 1, 1, 50],
    ['msg91_auth_key', '', 'string', 'sms', 'MSG91 Auth Key', 1, 1, 51],
    ['msg91_sender_id', 'APSDHM', 'string', 'sms', 'MSG91 Sender ID', 1, 1, 52],
    ['msg91_template_id', '', 'string', 'sms', 'MSG91 Template ID', 1, 1, 53],
    ['twilio_sid', '', 'string', 'sms', 'Twilio Account SID', 1, 1, 54],
    ['twilio_token', '', 'string', 'sms', 'Twilio Auth Token', 1, 1, 55],
    ['twilio_from', '', 'string', 'sms', 'Twilio From Number', 1, 1, 55],
    ['sms_rate_limit', 10, 'integer', 'sms', 'SMS per minute per user', 1, 1, 56],

    // WhatsApp
    ['whatsapp_enabled', true, 'boolean', 'whatsapp', 'Enable WhatsApp notifications', 1, 1, 60],
    ['whatsapp_provider', 'meta', 'string', 'whatsapp', 'WhatsApp provider (meta, twilio, gupshup)', 1, 1, 61],
    ['whatsapp_token', '', 'string', 'whatsapp', 'WhatsApp API token', 1, 1, 62],
    ['whatsapp_phone_id', '', 'string', 'whatsapp', 'WhatsApp Phone Number ID', 1, 1, 63],
    ['whatsapp_template_namespace', '', 'string', 'whatsapp', 'Template namespace', 1, 1, 64],

    // Push Notifications
    ['push_enabled', true, 'boolean', 'push', 'Enable push notifications', 1, 1, 70],
    ['fcm_server_key', '', 'string', 'push', 'FCM Server Key', 1, 1, 71],
    ['fcm_sender_id', '', 'string', 'push', 'FCM Sender ID', 1, 1, 72],
    ['push_rate_limit', 100, 'integer', 'push', 'Push notifications per minute', 1, 1, 72],

    // Payment
    ['razorpay_key_id', '', 'string', 'payment', 'Razorpay Key ID', 1, 1, 80],
    ['razorpay_key_secret', '', 'string', 'payment', 'Razorpay Key Secret', 1, 1, 81],
    ['razorpay_webhook_secret', '', 'string', 'payment', 'Razorpay Webhook Secret', 1, 1, 82],
    ['phonepe_merchant_id', '', 'string', 'payment', 'PhonePe Merchant ID', 1, 1, 83],
    ['phonepe_salt_key', '', 'string', 'payment', 'PhonePe Salt Key', 1, 1, 84],
    ['phonepe_salt_index', 1, 'integer', 'payment', 'PhonePe Salt Index', 1, 1, 85],
    ['payment_gateway_default', 'razorpay', 'string', 'payment', 'Default payment gateway', 1, 1, 86],
    ['payment_test_mode', true, 'boolean', 'payment', 'Enable test mode', 1, 1, 87],
    ['payment_currency', 'INR', 'string', 'payment', 'Payment currency', 1, 1, 88],

    // MLM
    ['mlm_enabled', true, 'boolean', 'mlm', 'Enable MLM features', 1, 1, 90],
    ['mlm_max_depth', 7, 'integer', 'mlm', 'Maximum MLM depth', 1, 1, 91],
    ['mlm_commission_cap', 20, 'float', 'mlm', 'Commission cap percentage', 1, 1, 92],
    ['mlm_royalty_pool_percent', 2, 'float', 'mlm', 'Royalty pool percentage', 1, 1, 93],
    ['mlm_registration_fee', 0, 'float', 'mlm', 'Registration fee', 1, 1, 93],
    ['mlm_min_payout', 500, 'float', 'mlm', 'Minimum payout amount', 1, 1, 94],
    ['mlm_payout_frequency', 'monthly', 'string', 'mlm', 'Payout frequency (weekly, monthly)', 1, 1, 95],

    // Referral
    ['referral_enabled', true, 'boolean', 'referral', 'Enable referral program', 1, 1, 100],
    ['referral_signup_bonus', 100, 'float', 'referral', 'Signup bonus amount', 1, 1, 101],
    ['referral_booking_bonus', 500, 'float', 'referral', 'Booking bonus amount', 1, 1, 102],
    ['referral_levels', 4, 'integer', 'referral', 'Number of referral levels', 1, 1, 102],
    ['referral_level_percentages', '{"1":10,"2":5,"3":2.5,"4":1}', 'json', 'referral', 'Level percentages', 1, 1, 103],

    // Wallet
    ['wallet_enabled', true, 'boolean', 'wallet', 'Enable wallet system', 1, 1, 110],
    ['wallet_min_balance', 0, 'float', 'wallet', 'Minimum wallet balance', 1, 1, 111],
    ['wallet_max_balance', 1000000, 'float', 'wallet', 'Maximum wallet balance', 1, 1, 112],
    ['wallet_withdrawal_min', 100, 'float', 'wallet', 'Minimum withdrawal amount', 1, 1, 113],
    ['wallet_withdrawal_max', 500000, 'float', 'wallet', 'Maximum withdrawal per transaction', 1, 1, 114],
    ['wallet_withdrawal_fee_percent', 0, 'float', 'wallet', 'Withdrawal fee percentage', 1, 1, 115],
    ['wallet_kyc_required', true, 'boolean', 'wallet', 'KYC required for withdrawals', 1, 1, 116],

    // Pricing
    ['pricing_default_corner_premium', 10, 'float', 'pricing', 'Corner plot premium %', 1, 1, 120],
    ['pricing_default_park_premium', 15, 'float', 'pricing', 'Park facing premium %', 1, 1, 121],
    ['pricing_default_road_premium', 8, 'float', 'pricing', 'Wide road premium %', 1, 1, 122],
    ['pricing_road_width_threshold', 40, 'integer', 'pricing', 'Road width threshold (ft)', 1, 1, 122],

    // Rate Limiting
    ['rate_limit_auth', 5, 'integer', 'rate_limiting', 'Auth attempts per window', 1, 1, 130],
    ['rate_limit_auth_window', 900, 'integer', 'rate_limiting', 'Auth window in seconds', 1, 1, 131],
    ['rate_limit_api', 120, 'integer', 'rate_limiting', 'API requests per minute', 1, 1, 132],
    ['rate_limit_search', 30, 'integer', 'rate_limiting', 'Search requests per minute', 1, 1, 132],
    ['rate_limit_admin', 300, 'integer', 'rate_limiting', 'Admin requests per minute', 1, 1, 133],

    // File Upload
    ['upload_max_size', 10, 'integer', 'uploads', 'Max upload size in MB', 1, 1, 140],
    ['upload_allowed_types', '["jpg","jpeg","png","gif","pdf","doc","docx","xls","xlsx"]', 'json', 'uploads', 'Allowed file types', 1, 1, 141],
    ['upload_path', '/uploads/', 'string', 'uploads', 'Upload directory path', 1, 1, 142],
    ['upload_url', '/uploads/', 'string', 'uploads', 'Upload URL path', 1, 1, 142],

    // API
    ['api_rate_limit', 100, 'integer', 'api', 'API requests per minute per user', 1, 1, 150],
    ['api_token_lifetime', 86400, 'integer', 'api', 'API token lifetime in seconds', 1, 1, 151],
    ['api_version', 'v2', 'string', 'api', 'Current API version', 1, 1, 152],

    // Features
    ['feature_mlm', true, 'boolean', 'features', 'Enable MLM module', 1, 1, 160],
    ['feature_referral', true, 'boolean', 'features', 'Enable referral program', 1, 1, 161],
    ['feature_wallet', true, 'boolean', 'features', 'Enable wallet', 1, 1, 162],
    ['feature_booking', true, 'boolean', 'features', 'Enable booking', 1, 1, 163],
    ['feature_emi', true, 'boolean', 'features', 'Enable EMI', 1, 1, 164],
    ['feature_wallet_activation', true, 'boolean', 'features', 'Enable wallet activation', 1, 1, 165],
    ['feature_company_loan', true, 'boolean', 'features', 'Enable company loan', 1, 1, 166],
    ['feature_legal_docs', true, 'boolean', 'features', 'Enable legal documents', 1, 1, 167],
    ['feature_ai_chat', true, 'boolean', 'features', 'Enable AI chat', 1, 1, 168],
    ['feature_voice_assistant', true, 'boolean', 'features', 'Enable voice assistant', 1, 1, 169],
    ['feature_property_verification', true, 'boolean', 'features', 'Enable property verification', 1, 1, 170],

    // SEO
    ['seo_title_suffix', ' - APS Dream Home', 'string', 'seo', 'Title suffix', 1, 1, 180],
    ['seo_default_description', 'Premium real estate plots and properties in Gorakhpur, Lucknow and Uttar Pradesh', 'string', 'seo', 'Default meta description', 1, 1, 181],
    ['seo_default_keywords', 'real estate, plots, properties, gorakhpur, lucknow, up', 'string', 'seo', 'Default meta keywords', 1, 1, 182],
    ['seo_og_image', '/assets/images/og-default.jpg', 'string', 'seo', 'Default OG image', 1, 1, 183],
    ['robots_txt', 'User-agent: *\nDisallow: /admin/\nDisallow: /api/\nSitemap: https://apsdreamhome.com/sitemap.xml', 'string', 'seo', 'Robots.txt content', 1, 1, 184],

    // Analytics
    ['ga_measurement_id', '', 'string', 'analytics', 'Google Analytics Measurement ID', 1, 1, 190],
    ['gtm_container_id', '', 'string', 'analytics', 'GTM Container ID', 1, 1, 191],
    ['fb_pixel_id', '', 'string', 'analytics', 'Facebook Pixel ID', 1, 1, 192],
];

$stmt = $pdo->prepare("
    INSERT IGNORE INTO system_configs 
    (config_key, config_value, config_type, config_group, description, is_public, is_editable, sort_order)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$sortOrder = 0;
foreach ($defaultConfigs as $config) {
    [$key, $value, $type, $group, $desc, $public, $editable, $sort] = $config;
    $stmt->execute([$key, $value, $type, $group, $desc, $public, $editable, $sort]);
    echo "Seeded: $key\n";
}

echo "Seeding completed successfully!\n";