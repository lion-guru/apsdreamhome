<?php

/**
 * Migration: Create AI Conversations Table
 * Stores chat history for learning
 */

require_once __DIR__ . '/../../app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();

echo "Creating ai_conversations table...\n";

$sql = "CREATE TABLE IF NOT EXISTS ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    user_id INT DEFAULT NULL,
    user_role VARCHAR(20) DEFAULT 'guest',
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    intent VARCHAR(50) DEFAULT NULL,
    sentiment VARCHAR(20) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_role (user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->exec($sql);
    echo "✅ ai_conversations table created successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Create ai_knowledge_base table for storing learned information
echo "Creating ai_knowledge_base table...\n";

$sql2 = "CREATE TABLE IF NOT EXISTS ai_knowledge_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    question_pattern VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    usage_count INT DEFAULT 0,
    effectiveness_score DECIMAL(3,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_pattern (question_pattern),
    INDEX idx_usage (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->exec($sql2);
    echo "✅ ai_knowledge_base table created successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Seed initial knowledge
echo "Seeding initial knowledge base...\n";

$initialKnowledge = [
    ['projects', 'suryoday heights kaha hai', 'Suryoday Heights Gorakhpur mein hai, premium colony with plots starting ₹5.5L'],
    ['projects', 'raghunath city center location', 'Raghunath City Center Gorakhpur mein hai, commercial plots available'],
    ['pricing', 'plot kitne ka hai', 'Plot prices start from ₹5.5 Lakh depending on location and size'],
    ['services', 'home loan kaise milega', 'Home loan available at 8.5% interest, apply at /financial-services'],
    ['contact', 'phone number kya hai', 'Call us at +91 92771 21112 or WhatsApp same number'],
];

foreach ($initialKnowledge as $knowledge) {
    try {
        $db->query(
            "INSERT IGNORE INTO ai_knowledge_base (category, question_pattern, answer) VALUES (?, ?, ?)",
            $knowledge
        );
    } catch (Exception $e) {
        // Ignore duplicates
    }
}

echo "✅ AI tables setup complete!\n";
