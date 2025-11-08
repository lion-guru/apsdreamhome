<?php
/**
 * AI Workflow Patterns API
 * Provides access to detected workflow patterns
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

    // Get workflow patterns ordered by frequency
    $stmt = $pdo->prepare("
        SELECT pattern_name, pattern_category, frequency_count, automation_potential,
               last_used, average_completion_time
        FROM ai_workflow_patterns
        ORDER BY frequency_count DESC, last_used DESC
        LIMIT 10
    ");
    $stmt->execute();
    $patterns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'patterns' => array_map(function($pattern) {
            return [
                'name' => $pattern['pattern_name'],
                'category' => $pattern['pattern_category'],
                'usage_count' => $pattern['frequency_count'],
                'automation' => $pattern['automation_potential'],
                'last_used' => $pattern['last_used'],
                'avg_completion_time' => $pattern['average_completion_time'] . ' minutes',
                'description' => 'Frequently used workflow pattern with ' . $pattern['automation_potential'] . ' automation potential'
            ];
        }, $patterns),
        'total_patterns' => count($patterns),
        'generated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Workflow patterns error: ' . $e->getMessage()]);
}
