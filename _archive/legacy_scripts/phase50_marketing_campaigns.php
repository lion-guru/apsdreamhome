<?php
/**
 * Phase 50: Marketing Campaign Manager
 * Create, schedule, and track marketing campaigns across channels
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec("DROP TABLE IF EXISTS marketing_campaigns");
$db->exec("
    CREATE TABLE marketing_campaigns (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        description TEXT NULL,
        type ENUM('email','sms','whatsapp','push','multi') NOT NULL DEFAULT 'email',
        status ENUM('draft','scheduled','sending','sent','paused','cancelled') NOT NULL DEFAULT 'draft',
        target_audience VARCHAR(100) NULL,
        target_filters JSON NULL,
        subject VARCHAR(255) NULL,
        content TEXT NOT NULL,
        template_id INT(11) NULL,
        scheduled_at TIMESTAMP NULL,
        sent_at TIMESTAMP NULL,
        completed_at TIMESTAMP NULL,
        total_recipients INT(11) NOT NULL DEFAULT 0,
        sent_count INT(11) NOT NULL DEFAULT 0,
        delivered_count INT(11) NOT NULL DEFAULT 0,
        opened_count INT(11) NOT NULL DEFAULT 0,
        clicked_count INT(11) NOT NULL DEFAULT 0,
        failed_count INT(11) NOT NULL DEFAULT 0,
        unsubscribed_count INT(11) NOT NULL DEFAULT 0,
        created_by BIGINT(20) UNSIGNED NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status, scheduled_at),
        INDEX idx_type (type, status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK marketing_campaigns table created\n";

$db->exec("DROP TABLE IF EXISTS marketing_campaign_recipients");
$db->exec("
    CREATE TABLE marketing_campaign_recipients (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT(11) NOT NULL,
        user_id BIGINT(20) UNSIGNED NULL,
        email VARCHAR(150) NULL,
        phone VARCHAR(20) NULL,
        name VARCHAR(150) NULL,
        channel VARCHAR(20) NOT NULL,
        status ENUM('pending','sent','delivered','opened','clicked','failed','bounced','unsubscribed') NOT NULL DEFAULT 'pending',
        sent_at TIMESTAMP NULL,
        delivered_at TIMESTAMP NULL,
        opened_at TIMESTAMP NULL,
        clicked_at TIMESTAMP NULL,
        failed_at TIMESTAMP NULL,
        error_message TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_campaign (campaign_id, status),
        INDEX idx_user (user_id, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK marketing_campaign_recipients table created\n";

$db->exec("DROP TABLE IF EXISTS marketing_campaign_templates");
$db->exec("
    CREATE TABLE marketing_campaign_templates (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        type ENUM('email','sms','whatsapp','push') NOT NULL,
        subject VARCHAR(255) NULL,
        body TEXT NOT NULL,
        variables JSON NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        usage_count INT(11) NOT NULL DEFAULT 0,
        created_by BIGINT(20) UNSIGNED NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_type (type, is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK marketing_campaign_templates table created\n";

$db->exec("DROP TABLE IF EXISTS marketing_unsubscribes");
$db->exec("
    CREATE TABLE marketing_unsubscribes (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NULL,
        email VARCHAR(150) NULL,
        phone VARCHAR(20) NULL,
        channel VARCHAR(20) NOT NULL,
        reason VARCHAR(255) NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_email (email),
        INDEX idx_phone (phone)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK marketing_unsubscribes table created\n";

// Seed default templates
$db->exec("INSERT INTO marketing_campaign_templates (name, type, subject, body, variables, created_by) VALUES
('New Property Launch', 'email', 'New {{property_type}} Available in {{city}}!',
'Hi {{name}},\n\nWe have a new {{property_type}} available in {{city}} that matches your preferences.\n\nPrice: â‚¹{{price}}\nLocation: {{location}}\nArea: {{area}} sqft\n\nView details: {{link}}\n\nBest regards,\nAPS Dream Home Team',
JSON_ARRAY('name','property_type','city','price','location','area','link'), NULL)");

$db->exec("INSERT INTO marketing_campaign_templates (name, type, subject, body, variables) VALUES
('Price Drop Alert', 'email', 'Price Reduced on {{property_type}} in {{city}}!',
'Hi {{name}},\n\nGreat news! The price has been reduced on a {{property_type}} in {{city}}.\n\nNew Price: â‚¹{{new_price}}\nOld Price: â‚¹{{old_price}}\nSavings: â‚¹{{savings}}\n\nView: {{link}}\n\nDon''t miss this opportunity!\n\nAPS Dream Home', JSON_ARRAY('name','property_type','city','new_price','old_price','savings','link'))");

$db->exec("INSERT INTO marketing_campaign_templates (name, type, subject, body, variables) VALUES
('Welcome Email', 'email', 'Welcome to APS Dream Home!',
'Hi {{name}},\n\nWelcome to APS Dream Home - your trusted real estate partner!\n\nWith us you can:\n- Browse verified properties\n- Get instant property alerts\n- Connect with trusted agents\n- Schedule site visits\n\nStart exploring: {{link}}\n\nHappy home hunting!\nAPS Dream Home Team', JSON_ARRAY('name','link'))");

$db->exec("INSERT INTO marketing_campaign_templates (name, type, subject, body, variables) VALUES
('Site Visit Reminder SMS', 'sms', NULL,
'Hi {{name}}, reminder: site visit to {{property}} in {{city}} tomorrow at {{time}}. Reply STOP to unsubscribe.',
JSON_ARRAY('name','property','city','time'))");

$db->exec("INSERT INTO marketing_campaign_templates (name, type, subject, body, variables) VALUES
('Property Update WhatsApp', 'whatsapp', NULL,
'ðŸ�  *New Property Match!*\n\nHi {{name}},\n\nWe found a {{property_type}} in {{city}} that matches your needs.\n\nðŸ’° Price: â‚¹{{price}}\nðŸ“� Location: {{location}}\nðŸ“� Area: {{area}} sqft\n\nView: {{link}}\n\nReply STOP to unsubscribe.',
JSON_ARRAY('name','property_type','city','price','location','area','link'))");

$db->exec("INSERT INTO marketing_campaign_templates (name, type, subject, body, variables) VALUES
('Festival Offer', 'email', 'Special Festival Offers on Properties!',
'Hi {{name}},\n\nCelebrate this festival with your dream home! Special offers up to {{discount}}% on select properties.\n\nView offers: {{link}}\n\nLimited time only!\n\nAPS Dream Home', JSON_ARRAY('name','discount','link'))");

echo "OK 6 default templates seeded\n";
echo "DONE\n";?>