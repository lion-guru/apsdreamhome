<?php
/**
 * Migration: Create Chatbot and Payment Tables
 * Tables: ai_conversations, payment_transactions
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database\Database;

echo "🚀 Creating Chatbot & Payment Tables...\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // AI Conversations Table
    echo "🤖 Creating ai_conversations table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL DEFAULT 0,
        user_message TEXT NOT NULL,
        bot_response TEXT NOT NULL,
        intent VARCHAR(50) DEFAULT 'general',
        language VARCHAR(10) DEFAULT 'en',
        confidence DECIMAL(3,2) DEFAULT 0.80,
        source VARCHAR(20) DEFAULT 'local',
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        INDEX idx_intent (intent)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ ai_conversations table created\n\n";
    
    // Payment Transactions Table
    echo "💳 Creating payment_transactions table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gateway VARCHAR(50) NOT NULL,
        order_id VARCHAR(100) NOT NULL,
        transaction_id VARCHAR(100) NULL,
        amount DECIMAL(12,2) NOT NULL,
        currency VARCHAR(10) DEFAULT 'INR',
        status VARCHAR(50) DEFAULT 'initiated',
        customer_id INT NULL,
        customer_email VARCHAR(255) NULL,
        customer_phone VARCHAR(20) NULL,
        payment_method VARCHAR(50) NULL,
        payment_instrument TEXT NULL,
        response_data JSON NULL,
        webhook_data JSON NULL,
        error_message TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        INDEX idx_order_id (order_id),
        INDEX idx_transaction_id (transaction_id),
        INDEX idx_status (status),
        INDEX idx_gateway (gateway),
        INDEX idx_customer_id (customer_id),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ payment_transactions table created\n\n";
    
    echo "🎉 All tables created successfully!\n";
    echo "📊 Tables created:\n";
    echo "   - ai_conversations (Chat history)\n";
    echo "   - payment_transactions (Payment logs)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
