<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\IntentDetector;
use App\Core\Database\Database;

$db = Database::getInstance();
$detector = new IntentDetector($db);

$tests = [
    'Mujhe Gorakhpur mein 50 lakh ka plot chahiye',
    'I want to buy a 3BHK flat in Lucknow under 80 lakhs',
    'Site visit book karna hai kal',
    'EMI calculator chahiye',
    'Property details chahiye plot number A-101',
];

foreach ($tests as $test) {
    $result = $detector->detect($test);
    echo "Query: $test\n";
    echo "Intent: {$result['intent']} (confidence: {$result['confidence']})\n\n";
}