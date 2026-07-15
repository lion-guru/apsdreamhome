<?php
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();

// Check campaign_analytics
$tables = $db->fetchAll("SHOW TABLES LIKE '%campaign_analytics%'");
echo "campaign_analytics tables: " . json_encode($tables) . "\n";

// Check customer_behavior_analysis segments count
try {
    $r = $db->fetchOne("SELECT COUNT(DISTINCT behavior_category) as segments FROM customer_behavior_analysis WHERE behavior_category IS NOT NULL");
    echo "Behavior segments: " . json_encode($r) . "\n";
} catch (Throwable $e) {
    echo "customer_behavior_analysis error: " . $e->getMessage() . "\n";
}

// Check journey duration
try {
    $r = $db->fetchOne("SELECT AVG(DATEDIFF(COALESCE(end_date, NOW()), start_date)) as avg_days FROM customer_journeys WHERE start_date IS NOT NULL");
    echo "Journey avg duration: " . json_encode($r) . "\n";
} catch (Throwable $e) {
    echo "customer_journeys error: " . $e->getMessage() . "\n";
}

// Check event types count
try {
    $r = $db->fetchOne("SELECT COUNT(DISTINCT event_type) as types FROM customer_events WHERE event_type IS NOT NULL");
    echo "Event types: " . json_encode($r) . "\n";
} catch (Throwable $e) {
    echo "customer_events error: " . $e->getMessage() . "\n";
}
