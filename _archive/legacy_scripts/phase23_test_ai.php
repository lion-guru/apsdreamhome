<?php
/**
 * Test the self-learning AI system end-to-end
 */
$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

$config = require $root . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$ai = new \App\Services\AI\AIManager($pdo);

echo "=== SELF-LEARNING AI TEST ===\n\n";

// 1. Test IntentDetector
echo "1. IntentDetector tests:\n";
$tests = [
    'I want to buy a 3BHK flat in Gorakhpur' => 'buy_property',
    'à¤®à¥�à¤�à¥‡ à¤ªà¥�à¤²à¤¾à¤Ÿ à¤–à¤°à¥€à¤¦à¤¨à¤¾ à¤¹à¥ˆ' => 'buy_property',
    'Hello there!' => 'greeting',
    'à¤¨à¤®à¤¸à¥�à¤¤à¥‡' => 'greeting',
    'I need a home loan' => 'loan',
    'What is the price?' => 'price_inquiry',
    'à¤•à¤¿à¤¤à¤¨à¤¾ à¤¦à¤¾à¤® à¤¹à¥ˆ?' => 'price_inquiry',
    'Thanks for the help' => 'thanks',
    'Schedule a site visit please' => 'site_visit',
];

$passed = 0;
foreach ($tests as $msg => $expected) {
    $result = $ai->getIntentDetector()->detect($msg);
    $ok = $result['intent'] === $expected ? 'âœ“' : 'âœ—';
    if ($ok === 'âœ“') $passed++;
    echo sprintf("  %s '%s' => %s (conf: %s, lang: %s)\n",
        $ok, substr($msg, 0, 40), $result['intent'], $result['confidence'], $result['language']);
}
echo "  Result: $passed/" . count($tests) . " passed\n\n";

// 2. Test chat processing
echo "2. Chat processing:\n";
$sessionId = 'test_' . uniqid();
$response = $ai->processChat($sessionId, 1, 'I want to buy a plot');
echo "  User: I want to buy a plot\n";
echo "  Bot: " . $response['text'] . "\n";
echo "  Intent: {$response['intent']} (confidence: {$response['confidence']})\n";
echo "  Response time: {$response['response_time_ms']}ms\n\n";

// 3. Test lead scoring
echo "3. Lead Scoring:\n";
$stmt = $pdo->query("SELECT id FROM leads LIMIT 1");
$leadId = (int)$stmt->fetchColumn();
if ($leadId) {
    $score = $ai->scoreLead($leadId);
    echo "  Lead #$leadId: Score {$score['score']}/100 Grade {$score['grade']}\n";
    echo "  Breakdown: intent={$score['intent']}, engagement={$score['engagement']}, budget={$score['budget']}, timing={$score['timing']}\n";
    echo "  Predicted action: {$score['predicted_action']}\n";
} else {
    echo "  No leads to score\n";
}
echo "\n";

// 4. Test price prediction
echo "4. Price Prediction:\n";
$prediction = $ai->predictPrice('plot', null, 1000, 0, 0);
echo "  Plot in any district, 1000 sqft:\n";
echo "  Predicted: â‚¹" . number_format($prediction['predicted_price']) . "\n";
echo "  Range: â‚¹" . number_format($prediction['low_estimate']) . " - â‚¹" . number_format($prediction['high_estimate']) . "\n";
echo "  Confidence: " . ($prediction['confidence'] * 100) . "%\n";
echo "  RÂ²: {$prediction['r_squared']}, Samples: {$prediction['sample_size']}\n\n";

// 5. Test behavior tracking
echo "5. Behavior Tracking:\n";
$ai->track(1, 'view_property', '/properties/123', 'property', 123, ['duration' => 5000], 'test_session', 5000);
$ai->track(1, 'inquiry', '/contact', 'lead', null, ['source' => 'web'], 'test_session', 2000);
echo "  âœ“ Tracked 2 events\n\n";

// 6. Get recommendations
echo "6. Recommendations:\n";
try {
    $recs = $ai->getRecommendations(1, 5);
    echo "  Got " . count($recs) . " recommendations for user 1\n";
    foreach (array_slice($recs, 0, 3) as $r) {
        $name = $r['item']['title'] ?? $r['item']['name'] ?? 'Item #' . $r['item']['id'];
        echo "  - $name (score: {$r['score']})\n";
    }
} catch (Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Get stats
echo "7. AI System Stats:\n";
$stats = $ai->getStats();
foreach ($stats as $k => $v) {
    echo "  $k: $v\n";
}

echo "\n=== ALL TESTS COMPLETE ===\n";?>