<?php
require __DIR__ . '/../vendor/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "=== Check Uttar Pradesh State ===\n";
$upState = $db->fetch('SELECT id, name FROM states WHERE name LIKE ? LIMIT 1', ['%Uttar Pradesh%']);
var_dump($upState);

echo "\n=== Districts for Uttar Pradesh ===\n";
if ($upState) {
    $upDistricts = $db->fetchAll('SELECT id, name, state_id FROM districts WHERE state_id = ? LIMIT 20', [$upState['id']]);
    var_dump($upDistricts);
}

echo "\n=== Check Kerala State ===\n";
$klState = $db->fetch('SELECT id, name FROM states WHERE name LIKE ? LIMIT 1', ['%Kerala%']);
var_dump($klState);

echo "\n=== Districts for Kerala ===\n";
if ($klState) {
    $klDistricts = $db->fetchAll('SELECT id, name, state_id FROM districts WHERE state_id = ? LIMIT 20', [$klState['id']]);
    var_dump($klDistricts);
}

echo "\n=== Check for Kochi/Thiruvananthapuram ===\n";
$specialDistricts = $db->fetchAll('SELECT id, name, state_id FROM districts WHERE name IN (?, ?)', ['Kochi', 'Thiruvananthapuram']);
var_dump($specialDistricts);
