<?php
/**
 * Notification & Analytics Migration
 * Creates tables for Notification Center and Advanced Analytics
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database\Database;

echo "🚀 Creating Notification & Analytics Tables...\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Notifications table
    echo "🔔 Creating notifications table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate', 'agent', 'admin') NOT NULL,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        data JSON NULL,
        channels JSON NULL,
        priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
        status ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
        sent_via JSON NULL,
        read_at TIMESTAMP NULL,
        clicked_at TIMESTAMP NULL,
        action_url VARCHAR(500) NULL,
        action_text VARCHAR(100) NULL,
        image_url VARCHAR(500) NULL,
        icon VARCHAR(50) NULL,
        color VARCHAR(20) NULL,
        expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id, user_type),
        INDEX idx_type (type),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_created (created_at),
        INDEX idx_read (read_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Notification preferences
    echo "⚙️ Creating notification_preferences table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS notification_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate', 'agent', 'admin') NOT NULL,
        channel VARCHAR(20) NOT NULL,
        notification_type VARCHAR(50) NOT NULL,
        is_enabled TINYINT(1) DEFAULT 1,
        quiet_hours_start TIME NULL,
        quiet_hours_end TIME NULL,
        frequency ENUM('immediate', 'hourly_digest', 'daily_digest', 'weekly_digest') DEFAULT 'immediate',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_pref (user_id, user_type, channel, notification_type),
        INDEX idx_user (user_id, user_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Notification templates
    echo "📝 Creating notification_templates table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS notification_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        description TEXT NULL,
        title_template VARCHAR(200) NOT NULL,
        message_template TEXT NOT NULL,
        data_schema JSON NULL,
        default_channels JSON NULL,
        default_priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
        is_system TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_type (type),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Analytics events
    echo "📊 Creating analytics_events table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics_events (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        event_type VARCHAR(50) NOT NULL,
        event_name VARCHAR(100) NOT NULL,
        user_id INT NULL,
        user_type VARCHAR(20) NULL,
        session_id VARCHAR(64) NULL,
        entity_type VARCHAR(50) NULL,
        entity_id INT NULL,
        properties JSON NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        referrer VARCHAR(500) NULL,
        device_type ENUM('desktop', 'mobile', 'tablet') NULL,
        browser VARCHAR(50) NULL,
        os VARCHAR(50) NULL,
        country VARCHAR(2) NULL,
        city VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_event (event_type, event_name),
        INDEX idx_user (user_id, user_type),
        INDEX idx_entity (entity_type, entity_id),
        INDEX idx_created (created_at),
        INDEX idx_session (session_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Analytics funnels
    echo "🎯 Creating analytics_funnels table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics_funnels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        funnel_name VARCHAR(50) NOT NULL,
        stage_name VARCHAR(50) NOT NULL,
        user_id INT NULL,
        session_id VARCHAR(64) NULL,
        converted_at TIMESTAMP NULL,
        time_spent_seconds INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_funnel (funnel_name, stage_name),
        INDEX idx_user (user_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Seed notification templates
    echo "\n🌱 Seeding notification templates...\n";
    $templates = [
        ['booking_confirmed', 'Booking Confirmed', 'Your booking for {property_name} has been confirmed. Booking ID: {booking_id}', 'high'],
        ['payment_received', 'Payment Received', 'We have received your payment of ₹{amount} for {property_name}.', 'normal'],
        ['site_visit_reminder', 'Site Visit Reminder', 'Reminder for your site visit scheduled for {visit_date} at {visit_time}.', 'high'],
        ['price_drop_alert', 'Price Drop Alert', 'Price of {property_name} dropped from ₹{old_price} to ₹{new_price}!', 'normal'],
        ['new_property_match', 'New Property Match', 'New property matching your search: {property_name} in {location} for ₹{price}', 'normal'],
        ['lead_assigned', 'Lead Assigned', 'New lead assigned: {customer_name} - {customer_phone}', 'urgent'],
        ['emi_due_reminder', 'EMI Due Reminder', 'Your EMI of ₹{amount} is due on {due_date}.', 'urgent'],
        ['commission_credited', 'Commission Credited', 'Commission of ₹{amount} credited for {customer_name}\'s booking!', 'high']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO notification_templates 
        (type, name, title_template, message_template, default_priority, is_system)
        VALUES (?, ?, ?, ?, ?, 1)");
    
    foreach ($templates as $t) {
        $stmt->execute([
            $t[0],
            $t[1],
            $t[1] . ': {property_name}',
            $t[2],
            $t[3]
        ]);
    }
    
    echo "\n✅ Notification & Analytics tables created successfully!\n";
    echo "📊 Summary:\n";
    echo "   - notifications\n";
    echo "   - notification_preferences\n";
    echo "   - notification_templates (8 templates seeded)\n";
    echo "   - analytics_events\n";
    echo "   - analytics_funnels\n";
    echo "\n🎉 Total: 5 new tables!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
