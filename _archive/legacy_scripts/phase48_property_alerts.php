<?php
/**
 * Phase 48: Property Alert Subscriptions
 * Customers subscribe to property alerts based on criteria
 * Auto-notified when new matching properties are listed
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$db->exec("DROP TABLE IF EXISTS property_alert_subscriptions");
$db->exec("
    CREATE TABLE property_alert_subscriptions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NULL,
        email VARCHAR(150) NULL,
        phone VARCHAR(20) NULL,
        name VARCHAR(150) NULL,
        property_type VARCHAR(50) NULL,
        listing_type VARCHAR(20) NULL,
        city VARCHAR(100) NULL,
        state VARCHAR(100) NULL,
        min_price DECIMAL(12,2) NULL,
        max_price DECIMAL(12,2) NULL,
        min_area_sqft INT(11) NULL,
        max_area_sqft INT(11) NULL,
        bedrooms INT(11) NULL,
        notify_email TINYINT(1) NOT NULL DEFAULT 1,
        notify_sms TINYINT(1) NOT NULL DEFAULT 0,
        notify_whatsapp TINYINT(1) NOT NULL DEFAULT 0,
        frequency ENUM('instant','daily','weekly') NOT NULL DEFAULT 'daily',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        last_notified_at TIMESTAMP NULL,
        total_notifications INT(11) NOT NULL DEFAULT 0,
        unsubscribe_token VARCHAR(64) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_email (email),
        INDEX idx_active (is_active, frequency),
        INDEX idx_type (property_type, listing_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK property_alert_subscriptions table created\n";

$db->exec("DROP TABLE IF EXISTS property_alert_log");
$db->exec("
    CREATE TABLE property_alert_log (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        subscription_id INT(11) NOT NULL,
        property_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NULL,
        channel VARCHAR(20) NOT NULL,
        status ENUM('sent','failed','queued') NOT NULL DEFAULT 'queued',
        message TEXT NULL,
        sent_at TIMESTAMP NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_subscription (subscription_id),
        INDEX idx_property (property_id),
        INDEX idx_user (user_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK property_alert_log table created\n";?>