
<?php
/**
 * Enterprise Features Migration - Final
 * Creates all tables for Payment, Chat, Cache, Queue, Map, i18n
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database\Database;

echo "🚀 Starting Enterprise Features Migration...\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 1. Payment Transactions
    echo "💳 Creating payment_transactions table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_transactions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        transaction_id VARCHAR(100) NOT NULL UNIQUE,
        gateway VARCHAR(20) NOT NULL,
        order_id VARCHAR(100) NULL,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate', 'agent', 'admin') NOT NULL,
        entity_type ENUM('booking', 'emi', 'plot', 'registration', 'misc') NOT NULL,
        entity_id INT NULL,
        amount DECIMAL(15,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'INR',
        status ENUM('pending', 'captured', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
        payment_method VARCHAR(50) NULL,
        payment_mode VARCHAR(20) NULL,
        gateway_response JSON NULL,
        refund_amount DECIMAL(15,2) DEFAULT 0,
        refund_reason VARCHAR(255) NULL,
        refunded_at TIMESTAMP NULL,
        attempts INT DEFAULT 0,
        webhook_data JSON NULL,
        metadata JSON NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id, user_type),
        INDEX idx_status (status),
        INDEX idx_entity (entity_type, entity_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 2. User Payment Methods
    echo "💰 Creating user_payment_methods table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_payment_methods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate', 'agent', 'admin') NOT NULL,
        gateway VARCHAR(20) NOT NULL,
        method_type ENUM('card', 'upi', 'netbanking', 'wallet') NOT NULL,
        method_token VARCHAR(255) NOT NULL,
        last_four VARCHAR(4) NULL,
        card_brand VARCHAR(20) NULL,
        expiry_month VARCHAR(2) NULL,
        expiry_year VARCHAR(4) NULL,
        upi_id VARCHAR(100) NULL,
        bank_name VARCHAR(100) NULL,
        wallet_name VARCHAR(50) NULL,
        is_default TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_method (user_id, user_type, method_token),
        INDEX idx_user (user_id, user_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 3. Chat Conversations
    echo "💬 Creating chat_conversations table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NULL,
        lead_id INT NULL,
        customer_id INT NOT NULL,
        agent_id INT NOT NULL,
        status ENUM('active', 'closed', 'archived') DEFAULT 'active',
        source ENUM('website', 'mobile', 'whatsapp', 'property_page') DEFAULT 'website',
        last_message_at TIMESTAMP NULL,
        last_message_preview VARCHAR(255) NULL,
        customer_unread_count INT DEFAULT 0,
        agent_unread_count INT DEFAULT 0,
        metadata JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_customer (customer_id),
        INDEX idx_agent (agent_id),
        INDEX idx_status (status),
        INDEX idx_property (property_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 4. Chat Messages
    echo "📨 Creating chat_messages table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        sender_id INT NOT NULL,
        sender_type ENUM('customer', 'agent', 'system', 'bot') NOT NULL,
        message_type ENUM('text', 'image', 'file', 'location', 'property_card', 'template') DEFAULT 'text',
        message TEXT NOT NULL,
        attachments JSON NULL,
        is_read TINYINT(1) DEFAULT 0,
        read_at TIMESTAMP NULL,
        reply_to_message_id BIGINT NULL,
        metadata JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_conversation (conversation_id),
        INDEX idx_sender (sender_id, sender_type),
        INDEX idx_created (created_at),
        INDEX idx_unread (conversation_id, is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 5. Queue Jobs
    echo "⚡ Creating queue_jobs table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS queue_jobs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        queue VARCHAR(50) NOT NULL DEFAULT 'default',
        job_class VARCHAR(255) NOT NULL,
        job_data JSON NOT NULL,
        attempts INT DEFAULT 0,
        max_attempts INT DEFAULT 3,
        reserved_at TIMESTAMP NULL,
        available_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_queue_reserved (queue, reserved_at, available_at),
        INDEX idx_available (available_at),
        INDEX idx_attempts (attempts, max_attempts)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 6. Failed Jobs
    echo "❌ Creating failed_jobs table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS failed_jobs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        queue VARCHAR(50) NOT NULL,
        job_class VARCHAR(255) NOT NULL,
        job_data JSON NOT NULL,
        exception TEXT NOT NULL,
        failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_queue (queue),
        INDEX idx_failed (failed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 7. Property Coordinates
    echo "📍 Creating property_coordinates table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS property_coordinates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        latitude DECIMAL(10, 8) NOT NULL,
        longitude DECIMAL(11, 8) NOT NULL,
        address_formatted VARCHAR(255) NULL,
        accuracy VARCHAR(20) NULL,
        geocoded_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_property (property_id),
        INDEX idx_coordinates (latitude, longitude),
        INDEX idx_location (latitude, longitude, property_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 8. Nearby Places
    echo "🏪 Creating nearby_places table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS nearby_places (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        place_type ENUM('school', 'hospital', 'market', 'mall', 'restaurant', 'bank', 'atm', 'park', 'temple', 'mosque', 'church', 'transit') NOT NULL,
        place_name VARCHAR(100) NOT NULL,
        distance_meters INT NOT NULL,
        rating DECIMAL(2,1) NULL,
        place_id VARCHAR(100) NULL,
        latitude DECIMAL(10, 8) NULL,
        longitude DECIMAL(11, 8) NULL,
        walk_time_minutes INT NULL,
        drive_time_minutes INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_property (property_id),
        INDEX idx_type (place_type),
        INDEX idx_distance (property_id, distance_meters)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 9. Supported Locales
    echo "🌐 Creating supported_locales table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS supported_locales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(5) NOT NULL UNIQUE,
        name VARCHAR(50) NOT NULL,
        native_name VARCHAR(50) NOT NULL,
        is_rtl TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        is_default TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        flag_icon VARCHAR(20) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_active (is_active),
        INDEX idx_default (is_default)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 10. Translations
    echo "📝 Creating translations table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS translations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        locale VARCHAR(5) NOT NULL,
        namespace VARCHAR(50) DEFAULT 'app',
        key_name VARCHAR(100) NOT NULL,
        value TEXT NOT NULL,
        is_system TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_translation (locale, namespace, key_name),
        INDEX idx_locale (locale),
        INDEX idx_namespace (namespace)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 11. Payment Schedules
    echo "📅 Creating payment_schedules table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        entity_type ENUM('booking', 'plot', 'emi') NOT NULL,
        entity_id INT NOT NULL,
        total_amount DECIMAL(15,2) NOT NULL,
        paid_amount DECIMAL(15,2) DEFAULT 0,
        next_due_date DATE NULL,
        next_due_amount DECIMAL(15,2) NULL,
        installment_count INT DEFAULT 1,
        installments_paid INT DEFAULT 0,
        late_fee_amount DECIMAL(10,2) DEFAULT 0,
        status ENUM('active', 'completed', 'overdue', 'defaulted') DEFAULT 'active',
        auto_debit TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_status (status),
        INDEX idx_due (next_due_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 12. Payment Webhook Logs
    echo "🪝 Creating payment_webhook_logs table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_webhook_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        gateway VARCHAR(20) NOT NULL,
        event_type VARCHAR(100) NOT NULL,
        payload JSON NOT NULL,
        signature VARCHAR(255) NULL,
        is_valid TINYINT(1) DEFAULT 0,
        processed TINYINT(1) DEFAULT 0,
        processed_at TIMESTAMP NULL,
        error_message TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_gateway (gateway),
        INDEX idx_processed (processed)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 13. Chat Quick Replies
    echo "⚡ Creating chat_quick_replies table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_quick_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        agent_id INT NULL,
        title VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        category VARCHAR(50) NULL,
        is_global TINYINT(1) DEFAULT 0,
        shortcut VARCHAR(20) NULL,
        usage_count INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_agent (agent_id),
        INDEX idx_global (is_global)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 14. Chat Participants
    echo "👥 Creating chat_participants table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_participants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'agent', 'admin') NOT NULL,
        role ENUM('owner', 'participant', 'viewer') DEFAULT 'participant',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        left_at TIMESTAMP NULL,
        is_active TINYINT(1) DEFAULT 1,
        UNIQUE KEY unique_participant (conversation_id, user_id, user_type),
        INDEX idx_conversation (conversation_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 15. User Locale Preferences
    echo "🌍 Creating user_locale_preferences table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_locale_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate', 'agent', 'admin') NOT NULL,
        locale VARCHAR(5) NOT NULL,
        timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
        date_format VARCHAR(20) DEFAULT 'd M Y',
        time_format VARCHAR(20) DEFAULT 'h:i A',
        currency_code VARCHAR(3) DEFAULT 'INR',
        number_format VARCHAR(10) DEFAULT 'en_IN',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id, user_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 16. Map Cache
    echo "🗺️ Creating map_cache table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS map_cache (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cache_key VARCHAR(100) NOT NULL UNIQUE,
        response_data JSON NOT NULL,
        expires_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 17. Job Batches
    echo "📦 Creating job_batches table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS job_batches (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        batch_id VARCHAR(50) NOT NULL UNIQUE,
        total_jobs INT NOT NULL,
        pending_jobs INT NOT NULL,
        failed_jobs INT DEFAULT 0,
        processed_jobs INT DEFAULT 0,
        options JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        finished_at TIMESTAMP NULL,
        cancelled_at TIMESTAMP NULL,
        INDEX idx_batch (batch_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 18. Queue Workers
    echo "👷 Creating queue_workers table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS queue_workers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        worker_id VARCHAR(100) NOT NULL UNIQUE,
        queue VARCHAR(50) NOT NULL,
        process_id INT NOT NULL,
        started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        jobs_processed INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        INDEX idx_queue (queue),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Seed default locales
    echo "\n🌱 Seeding locales...\n";
    $locales = [
        ['en', 'English', 'English', 0, 1, '🇬🇧', 0],
        ['hi', 'Hindi', 'हिंदी', 0, 0, '🇮🇳', 1],
        ['bn', 'Bengali', 'বাংলা', 0, 0, '🇧🇩', 2],
        ['te', 'Telugu', 'తెలుగు', 0, 0, '🇮🇳', 3],
        ['mr', 'Marathi', 'मराठी', 0, 0, '🇮🇳', 4],
        ['ta', 'Tamil', 'தமிழ்', 0, 0, '🇮🇳', 5],
        ['ur', 'Urdu', 'اردو', 1, 0, '🇵🇰', 6],
        ['gu', 'Gujarati', 'ગુજરાતી', 0, 0, '🇮🇳', 7],
        ['kn', 'Kannada', 'ಕನ್ನಡ', 0, 0, '🇮🇳', 8],
        ['ml', 'Malayalam', 'മലയാളം', 0, 0, '🇮🇳', 9],
        ['pa', 'Punjabi', 'ਪੰਜਾਬੀ', 0, 0, '🇮🇳', 10],
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO supported_locales 
        (code, name, native_name, is_rtl, is_default, flag_icon, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($locales as $locale) {
        $stmt->execute($locale);
    }
    
    echo "\n✅ All enterprise tables created successfully!\n";
    echo "📊 Migration Summary:\n";
    echo "   - Payment Tables: 4\n";
    echo "   - Chat Tables: 4\n";
    echo "   - Queue Tables: 4\n";
    echo "   - Map Tables: 2\n";
    echo "   - i18n Tables: 3\n";
    echo "\n🎉 Total: 18 new tables created!\n";
    echo "🌍 11 Indian languages supported!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
