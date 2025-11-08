<?php
/**
 * AI Knowledge Base API
 * Provides access to AI knowledge base entries
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    global $config;
    $host = $config['database']['host'] ?? 'localhost';
    $dbname = $config['database']['database'] ?? 'apsdreamhome';
    $username = $config['database']['username'] ?? 'root';
    $password = $config['database']['password'] ?? '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get recent knowledge entries
    $stmt = $pdo->prepare("
        SELECT id, topic, category, title, content, difficulty_level, usage_count, created_at
        FROM ai_knowledge_base
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'entries' => array_map(function($entry) {
            return [
                'id' => $entry['id'],
                'title' => $entry['title'],
                'topic' => $entry['topic'],
                'category' => $entry['category'],
                'content' => substr($entry['content'], 0, 150) . '...',
                'difficulty' => $entry['difficulty_level'],
                'usage_count' => $entry['usage_count'],
                'created_at' => $entry['created_at']
            ];
        }, $entries),
        'total_entries' => count($entries),
        'generated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Knowledge base error: ' . $e->getMessage()]);
}
