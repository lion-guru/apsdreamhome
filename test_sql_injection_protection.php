<?php
/**
 * APS Dream Home - SQL Injection Protection Testing Script
 */

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Security/SQLInjectionDetector.php';

echo '🗄️ APS DREAM HOME - SQL INJECTION PROTECTION TESTING\n';
echo '==========================================\n\n';

$detector = App\Security\SQLInjectionDetector::getInstance();

// Test cases
$testCases = [
    'normal_input' => ['name' => 'John Doe', 'email' => 'john@example.com'],
    'union_attack' => ['id' => "1 UNION SELECT username, password FROM users--"],
    'comment_attack' => ['query' => "SELECT * FROM users WHERE id = 1; DROP TABLE users--"],
    'boolean_attack' => ['login' => "admin' OR '1'='1"],
    'time_attack' => ['search' => "test'; SELECT SLEEP(5)--"],
    'hex_attack' => ['id' => "0x414243"],
    'xss_sql_mix' => ['input' => "<script>alert('xss')</script> UNION SELECT * FROM users--"]
];

echo '🔍 Testing SQL Injection Detection:\n';
foreach ($testCases as $testName => $data) {
    echo "Testing $testName...\n";
    
    $isDetected = $detector->detect($data);
    $threatLevel = $detector->getThreatLevel(json_encode($data));
    
    $status = $isDetected ? '🚨 DETECTED' : '✅ SAFE';
    echo "  Detection: $status\n";
    echo "  Threat Level: $threatLevel\n";
    
    if ($isDetected) {
        $detector->logAttempt(json_encode($data), $threatLevel);
    }
    echo "\n";
}

echo '🎉 SQL injection protection testing completed!\n';
