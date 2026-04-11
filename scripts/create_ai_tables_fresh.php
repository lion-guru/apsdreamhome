<?php
/**
 * Create AI Tables - Fresh Setup
 */

$host = '127.0.0.1';
$port = '3307';
$user = 'root';
$pass = '';
$dbname = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL\n\n";
    
    // Drop existing tables if exist
    $pdo->exec("DROP TABLE IF EXISTS ai_knowledge_base");
    $pdo->exec("DROP TABLE IF EXISTS ai_conversations");
    echo "✅ Dropped existing tables\n";
    
    // Table 1: ai_conversations
    $sql1 = "CREATE TABLE IF NOT EXISTS ai_conversations (
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
    
    $pdo->exec($sql1);
    echo "✅ ai_conversations table created\n";
    
    // Table 2: ai_knowledge_base
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
    
    $pdo->exec($sql2);
    echo "✅ ai_knowledge_base table created\n";
    
    // Seed initial knowledge
    $knowledge = [
        ['projects', 'suryoday heights kaha hai', 'Suryoday Heights Gorakhpur mein hai, premium colony with plots starting ₹5.5L'],
        ['projects', 'raghunath city center location', 'Raghunath City Center Gorakhpur mein hai, commercial plots available'],
        ['pricing', 'plot kitne ka hai', 'Plot prices start from ₹5.5 Lakh depending on location and size'],
        ['services', 'home loan kaise milega', 'Home loan available at 8.5% interest, apply at /financial-services'],
        ['contact', 'phone number kya hai', 'Call us at +91 92771 21112 or WhatsApp same number'],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO ai_knowledge_base (category, question_pattern, answer) VALUES (?, ?, ?)");
    foreach ($knowledge as $k) {
        try {
            $stmt->execute($k);
        } catch (PDOException $e) {
            // Ignore duplicate errors
        }
    }
    echo "✅ Initial knowledge seeded\n";
    
    echo "\n🎉 AI tables setup complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
