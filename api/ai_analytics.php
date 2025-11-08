<?php
/**
 * AI Analytics API
 * Provides learning analytics and performance metrics
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Use the same database connection as other files
    global $config;
    $host = $config['database']['host'] ?? 'localhost';
    $dbname = $config['database']['database'] ?? 'apsdreamhome';
    $username = $config['database']['username'] ?? 'root';
    $password = $config['database']['password'] ?? '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get interaction statistics
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_interactions,
            AVG(CASE
                WHEN success_rating IN ('excellent', 'good') THEN 1.0
                WHEN success_rating = 'average' THEN 0.7
                WHEN success_rating = 'poor' THEN 0.3
                ELSE 0.0
            END) as avg_success_rate,
            SUM(tokens_used) as total_tokens
        FROM ai_user_interactions
        WHERE interaction_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");

    $stmt->execute();
    $interaction_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get knowledge base statistics
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_entries
        FROM ai_knowledge_base
    ");
    $stmt->execute();
    $knowledge_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get workflow patterns
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_patterns
        FROM ai_workflow_patterns
    ");
    $stmt->execute();
    $workflow_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'analytics' => [
            'total_interactions' => $interaction_stats['total_interactions'] ?? 0,
            'success_rate' => round(($interaction_stats['avg_success_rate'] ?? 0) * 100, 1),
            'total_tokens' => $interaction_stats['total_tokens'] ?? 0,
            'knowledge_entries' => $knowledge_stats['total_entries'] ?? 0,
            'workflow_patterns' => $workflow_stats['total_patterns'] ?? 0
        ],
        'generated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Analytics error: ' . $e->getMessage()]);
}
