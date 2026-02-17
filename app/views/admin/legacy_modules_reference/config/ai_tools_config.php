<?php

// Include the main admin initialization
require_once __DIR__ . '/../core/init.php';

// Establish database connection
$db = \App\Core\App::database();

// AI Tools Configuration File
define('CHATBOT_TABLE', 'ai_chatbot_interactions');
define('PROPERTY_DESC_TABLE', 'ai_property_descriptions');
define('AI_SETTINGS_TABLE', 'ai_tools_settings');
define('AI_LOGS_TABLE', 'ai_usage_logs');

// Database table creation queries
$ai_tables_sql = [
    CHATBOT_TABLE => "
        CREATE TABLE IF NOT EXISTS " . CHATBOT_TABLE . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            query TEXT,
            response TEXT,
            satisfaction_score DECIMAL(2,1),
            response_time DECIMAL(5,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(uid)
        )",
    
    PROPERTY_DESC_TABLE => "
        CREATE TABLE IF NOT EXISTS " . PROPERTY_DESC_TABLE . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT,
            original_description TEXT,
            generated_description TEXT,
            word_count INT,
            focus_points JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id)
        )",
    
    AI_SETTINGS_TABLE => "
        CREATE TABLE IF NOT EXISTS " . AI_SETTINGS_TABLE . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tool_name VARCHAR(50),
            settings JSON,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
    
    AI_LOGS_TABLE => "
        CREATE TABLE IF NOT EXISTS " . AI_LOGS_TABLE . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tool_name VARCHAR(50),
            user_id INT,
            action VARCHAR(100),
            details JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(uid)
        )"
];

// Create tables if they don't exist
try {
    foreach ($ai_tables_sql as $table_name => $sql) {
        $db->query($sql);
    }
} catch (\Exception $e) {
    error_log("Error creating AI tools tables: " . $e->getMessage());
}

// Default settings for AI tools
$default_settings = [
    'chatbot' => [
        'language' => 'hi',
        'response_style' => 'formal',
        'max_response_time' => 5.0
    ],
    'property_description' => [
        'default_length' => 'medium',
        'focus_points' => [
            'location_highlights' => true,
            'amenities' => true,
            'investment_potential' => true
        ]
    ]
];

// Insert default settings if not exists
try {
    $count = $db->fetch("SELECT COUNT(*) as count FROM " . AI_SETTINGS_TABLE)['count'];
    if ($count == 0) {
        foreach ($default_settings as $tool_name => $settings) {
            $db->insert(AI_SETTINGS_TABLE, [
                'tool_name' => $tool_name,
                'settings' => json_encode($settings)
            ]);
        }
    }
} catch (\Exception $e) {
    error_log("Error inserting default AI settings: " . $e->getMessage());
}

