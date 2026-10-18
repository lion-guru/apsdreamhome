<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\IntentDetector;
use App\Core\Database\Database;

$db = Database::getInstance();
$detector = new IntentDetector($db);
$result = $detector->detect('Mujhe Gorakhpur mein 50 lakh ka plot chahiye');
print_r($result);