<?php
require 'C:/xampp/htdocs/apsdreamhome/app/Core/autoload.php';
$db = App\Core\Database::getInstance()->getConnection();

// Check existing columns
$cols = array_column($db->query("SHOW COLUMNS FROM ai_settings")->fetchAll(PDO::FETCH_ASSOC), 'Field');
echo "Existing columns: " . implode(', ', $cols) . PHP_EOL;

// Add missing columns
$addCols = [
    'groq_api_key' => "VARCHAR(255) DEFAULT NULL",
    'openrouter_api_key' => "VARCHAR(255) DEFAULT NULL",
    'ollama_url' => "VARCHAR(255) DEFAULT 'http://localhost:11434'",
    'ollama_model' => "VARCHAR(100) DEFAULT 'llama3.1:8b'",
];

foreach ($addCols as $col => $def) {
    if (!in_array($col, $cols)) {
        $db->exec("ALTER TABLE ai_settings ADD COLUMN $col $def");
        echo "Added: $col" . PHP_EOL;
    } else {
        echo "Exists: $col" . PHP_EOL;
    }
}

// Update existing row with empty keys if needed
$row = $db->query("SELECT id FROM ai_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $db->exec("UPDATE ai_settings SET groq_api_key = COALESCE(groq_api_key, ''), openrouter_api_key = COALESCE(openrouter_api_key, '') WHERE id = {$row['id']}");
    echo "\nUpdated row {$row['id']} with default values" . PHP_EOL;
} else {
    $db->exec("INSERT INTO ai_settings (is_active, groq_api_key, openrouter_api_key, ollama_url, ollama_model) VALUES (1, '', '', 'http://localhost:11434', 'llama3.1:8b')");
    echo "\nCreated new settings row" . PHP_EOL;
}

echo "\nDone!" . PHP_EOL;?>