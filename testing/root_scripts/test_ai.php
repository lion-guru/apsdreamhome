<?php

/**
 * 1-Click Test Script for AI Aggregator
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "<h2 style='font-family: sans-serif;'>🤖 Testing AI Aggregator Service...</h2>";

try {
    $service = new \App\Services\AIAggregatorService();
    $result = $service->runAggregator(2);

    echo "<div style='font-family: sans-serif; color: green;'><h3>✅ Success!</h3>";
    echo "Properties Fetched & Rewritten: <b>" . $result['success'] . "</b><br>";
    echo "Failed: <b>" . $result['failed'] . "</b></div>";

    echo "<h4 style='font-family: sans-serif;'>📋 Execution Logs:</h4><ul style='font-family: sans-serif;'>";
    foreach ($result['logs'] as $log) {
        echo "<li>$log</li>";
    }
    echo "</ul>";
    echo "<br><a href='properties' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-family: sans-serif;'>View Properties on Frontend</a>";
} catch (Exception $e) {
    echo "<div style='font-family: sans-serif; color: red;'><h3>❌ Error:</h3>" . $e->getMessage() . "</div>";
}
