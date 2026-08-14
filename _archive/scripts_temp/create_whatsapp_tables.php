<?php
$dsn = "mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4";
$pdo = new PDO($dsn, 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check existing schema
$cols = $pdo->query("DESCRIBE whatsapp_templates")->fetchAll(PDO::FETCH_COLUMN);
echo "Existing columns: " . implode(", ", $cols) . "\n";

// If different, recreate
if (!in_array('template_name', $cols)) {
    $pdo->exec("DROP TABLE IF EXISTS whatsapp_template_usage, whatsapp_templates");
    echo "Dropped old tables.\n";
} else {
    echo "Table exists with correct schema.\n";
}

// Now create
$sql = "
CREATE TABLE IF NOT EXISTS whatsapp_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    template_code VARCHAR(50) UNIQUE NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'system',
    template_content TEXT NOT NULL,
    description TEXT,
    variables JSON,
    status ENUM('active','inactive','pending_approval','rejected') DEFAULT 'active',
    whatsapp_template_id VARCHAR(100),
    language VARCHAR(10) DEFAULT 'hi-IN',
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_code (template_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_template_usage (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id INT UNSIGNED NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    sent_count INT UNSIGNED DEFAULT 0,
    delivered_count INT UNSIGNED DEFAULT 0,
    read_count INT UNSIGNED DEFAULT 0,
    failed_count INT UNSIGNED DEFAULT 0,
    usage_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_template (template_id),
    INDEX idx_date (usage_date),
    UNIQUE KEY uniq_template_date (template_id, usage_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO whatsapp_templates (template_name, template_code, category, template_content, description, variables, status, language) VALUES
('Welcome Message', 'welcome', 'customer-service', 'Hello {{customer_name}}! Welcome to APS Dream Home. How can we help you find your dream property today?', 'Sent to new users who register', '[\"customer_name\"]', 'active', 'hi-IN'),
('Property Inquiry Response', 'inquiry_response', 'customer-service', 'Thank you for your interest in {{property_title}}. This {{property_type}} is located in {{location}} and priced at â‚¹{{price}}. Would you like to schedule a visit?', 'Automatic response to property inquiries', '[\"property_title\",\"property_type\",\"location\",\"price\"]', 'active', 'hi-IN'),
('New Property Listing', 'new_listing', 'property', 'ðŸ�  New Listing Alert! {{property_type}} in {{location}} - {{bedrooms}}BHK, {{area}}sqft at â‚¹{{price}}. Contact us for details!', 'Notify users about new property listings', '[\"property_type\",\"location\",\"bedrooms\",\"area\",\"price\"]', 'active', 'hi-IN'),
('Price Drop Notification', 'price_drop', 'property', 'ðŸŽ‰ Price Drop Alert! {{property_title}} is now available at â‚¹{{new_price}} (was â‚¹{{old_price}}). Limited time offer!', 'Notify users about price reductions', '[\"property_title\",\"new_price\",\"old_price\"]', 'active', 'hi-IN'),
('Booking Confirmation', 'booking_confirmation', 'booking', 'âœ… Booking Confirmed! Property visit scheduled on {{date}} at {{time}}. Address: {{property_address}}. See you there!', 'Confirm property visit bookings', '[\"date\",\"time\",\"property_address\"]', 'active', 'hi-IN'),
('Payment Confirmation', 'payment_confirmation', 'payment', 'ðŸ’³ Payment Received! â‚¹{{amount}} for {{property_title}} (Booking ID: {{booking_id}}). Thank you for choosing APS Dream Home!', 'Confirm successful payments', '[\"amount\",\"property_title\",\"booking_id\"]', 'active', 'hi-IN'),
('EMI Reminder', 'emi_reminder', 'payment', 'ðŸ“… EMI Reminder: Your payment of â‚¹{{amount}} is due on {{due_date}} for {{property_title}}. Pay now to avoid late fees.', 'Remind customers about upcoming EMI payments', '[\"amount\",\"due_date\",\"property_title\"]', 'active', 'hi-IN'),
('Commission Earned', 'commission_earned', 'commission', 'ðŸ’° Commission Earned! You have earned â‚¹{{amount}} from {{source}}. Check your wallet for details.', 'Notify associates about commission earnings', '[\"amount\",\"source\"]', 'active', 'hi-IN');

SELECT COUNT(*) as count FROM whatsapp_templates;
";

$pdo->exec($sql);
$count = $pdo->query("SELECT COUNT(*) c FROM whatsapp_templates")->fetchColumn();
echo "Tables created. Template count: $count\n";?>