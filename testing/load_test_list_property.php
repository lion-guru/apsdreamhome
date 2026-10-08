<?php
/**
 * Load test for /list-property/submit endpoint
 * 
 * Run: php testing/load_test_list_property.php [concurrent_users] [requests_per_user]
 * Example: php testing/load_test_list_property.php 10 5
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$concurrentUsers = $argv[1] ?? 5;
$requestsPerUser = $argv[2] ?? 3;

echo "Load Test: /list-property/submit\n";
echo "Concurrent Users: $concurrentUsers\n";
echo "Requests Per User: $requestsPerUser\n";
echo "Total Requests: " . ($concurrentUsers * $requestsPerUser) . "\n\n";

// Get CSRF token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/list-property');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
$html = curl_exec($ch);
curl_close($ch);

if (preg_match('/name="csrf_token" value="([^"]+)"/', $html, $matches)) {
    $csrfToken = $matches[1];
    echo "CSRF Token obtained: " . substr($csrfToken, 0, 12) . "...\n";
} else {
    echo "ERROR: Could not extract CSRF token\n";
    exit(1);
}

// Test data
$testData = [
    'csrf_token' => $csrfToken,
    'listing_type' => 'sell',
    'property_type' => 'plot',
    'state_id' => '17',
    'location' => 'Gorakhpur',
    'city' => 'Gorakhpur',
    'pincode' => '273001',
    'price' => '1500000',
    'area' => '1200',
    'description' => 'Load test listing',
    'name' => 'Load Test',
    'phone' => '9876543210',
    'email' => 'loadtest@example.com',
];

$startTime = microtime(true);
$successCount = 0;
$errorCount = 0;
$totalTime = 0;
$locks = [];

// Use curl multi for concurrent requests
$mh = curl_multi_init();
$handles = [];

for ($user = 0; $user < $concurrentUsers; $user++) {
    for ($req = 0; $req < $requestsPerUser; $req++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/list-property/submit');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies_' . $user . '.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_' . $user . '.txt');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $start = microtime(true);
        curl_multi_add_handle($mh, $ch);
        $handles[(int)$ch] = ['start' => $start, 'user' => $user];
    }
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
    
    while ($info = curl_multi_info_read($mh)) {
        $ch = $info['handle'];
        $info = curl_getinfo($ch);
        $endTime = microtime(true);
        $duration = $endTime - $handles[(int)$ch]['start'];
        $totalTime += $duration;
        
        $response = curl_multi_getcontent($ch);
        $httpCode = $info['http_code'];
        
        if ($httpCode >= 200 && $httpCode < 400) {
            $successCount++;
        } else {
            $errorCount++;
        }
        
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
} while ($running > 0);

curl_multi_close($mh);

$endTime = microtime(true);
$totalDuration = $endTime - $startTime;

echo "\n=== Results ===\n";
echo "Total Requests: " . ($concurrentUsers * $requestsPerUser) . "\n";
echo "Successful: $successCount\n";
echo "Failed: $errorCount\n";
echo "Total Duration: " . round($totalDuration, 2) . "s\n";
echo "Avg Response Time: " . round(($totalTime / ($concurrentUsers * $requestsPerUser)) * 1000, 2) . "ms\n";
echo "Requests/sec: " . round(($concurrentUsers * $requestsPerUser) / $totalDuration, 2) . "\n";

// Clean up cookie files
for ($i = 0; $i < $concurrentUsers; $i++) {
    @unlink("cookies_$i.txt");
}
@unlink('cookies.txt');

echo "\nDone.\n";