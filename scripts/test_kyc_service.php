<?php
define('APP_ROOT', 'C:\xampp\htdocs\apsdreamhome');

require 'C:\xampp\htdocs\apsdreamhome\config\database.php';
require 'C:\xampp\htdocs\apsdreamhome\app\Core\Autoloader.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$autoloader = \App\Core\Autoloader::getInstance();
$autoloader->addNamespace('App', 'C:\xampp\htdocs\apsdreamhome\app');
$autoloader->register();

$service = new \App\Services\KYCService($pdo);

echo "=== Testing PAN verification (mock mode) ===\n";

// Test 1: Valid format PAN with valid 4th char (P = Individual)
echo "\n1. PAN 'AAAAP1234C' (valid format, P=Individual):\n";
$result = $service->verifyPAN('AAAAP1234C', 'John Doe');
print_r($result);

// Test 2: Invalid 4th char
echo "\n2. PAN 'AAAAZ1234C' (invalid 4th char Z):\n";
$result = $service->verifyPAN('AAAAZ1234C', 'John Doe');
print_r($result);

// Test 3: Mock test PANs
echo "\n3. PAN 'BBBBB1111B' (mock DEACTIVATED):\n";
$result = $service->verifyPAN('BBBBB1111B', 'John Doe');
print_r($result);

echo "\n=== Testing Aadhaar verification (mock mode) ===\n";

// Test 4: Valid checksum Aadhaar (we need one with valid Verhoeff)
echo "\n4. Aadhaar with valid Verhoeff checksum:\n";
// Generate one with valid Verhoeff: 123456789012 has invalid checksum
// Let's try one that's known to work in mock
$result = $service->verifyAadhaar('111111111111', 'John Doe');
print_r($result);

// Test 5: Mock test Aadhaar
echo "\n5. Aadhaar '999999999999' (mock DEACTIVATED):\n";
$result = $service->verifyAadhaar('999999999999', 'John Doe');
print_r($result);

echo "\n=== Verhoeff check test ===\n";
$uidai = new \App\Services\KYC\UIDAIVerificationService();
echo "123456789012: " . ($uidai->verhoeffCheck('123456789012') ? 'valid' : 'invalid') . "\n";
echo "111111111111: " . ($uidai->verhoeffCheck('111111111111') ? 'valid' : 'invalid') . "\n";

// Find a valid one
for ($i = 111111111111; $i <= 111111111120; $i++) {
    if ($uidai->verhoeffCheck((string)$i)) {
        echo "Found valid: $i\n";
        break;
    }
}

echo "\nAll tests completed!\n";