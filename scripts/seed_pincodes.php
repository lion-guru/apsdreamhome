<?php
/**
 * Seed pincodes from India Post API
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "Setting up pincodes table...\n\n";

$createTable = "CREATE TABLE IF NOT EXISTS pincodes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pincode VARCHAR(10) NOT NULL,
    area_name VARCHAR(255),
    city_id INT UNSIGNED,
    district_id INT UNSIGNED,
    state_id INT UNSIGNED,
    country_id INT UNSIGNED DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pincode (pincode),
    INDEX idx_city (city_id),
    INDEX idx_state (state_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $db->execute($createTable);
    echo "✅ Table created/verified: pincodes\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

$count = $db->fetch("SELECT COUNT(*) as cnt FROM pincodes");
echo "Current pincodes: " . $count['cnt'] . "\n";

$force = isset($argv[1]) && $argv[1] === '--force';

if ($count['cnt'] > 0 && !$force) {
    echo "⏭️  Skipping - table already has data. Use --force to re-seed.\n";
    exit(0);
}

echo "\nFetching pincodes from India Post API...\n";
$apiKey = "579b464db66ec23bdd000001cdc3b564546246a772a26393094f5645";
$url = "https://api.data.gov.in/resource/5c2f62fe-5afa-4119-a499-fec9d604d5bd?api-key=" . $apiKey . "&format=json&limit=10000";

echo "API URL: " . substr($url, 0, 80) . "...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ API Error: HTTP $httpCode\n";
    exit(1);
}

$data = json_decode($response, true);
if (!isset($data['records'])) {
    echo "❌ Invalid API response\n";
    exit(1);
}

echo "Found " . count($data['records']) . " records in API\n";

echo "Seeding pincodes...\n";
$inserted = 0;
$skipped = 0;

foreach ($data['records'] as $record) {
    $pincode = trim($record['pincode'] ?? '');
    $area = trim($record['officename'] ?? $record['officetype'] ?? '');
    
    if (empty($pincode)) {
        $skipped++;
        continue;
    }
    
    try {
        $db->execute(
            "INSERT IGNORE INTO pincodes (pincode, area_name) VALUES (?, ?)",
            [$pincode, $area]
        );
        $inserted++;
    } catch (Exception $e) {
        $skipped++;
    }
    
    if ($inserted % 2000 === 0) {
        echo "  Inserted: $inserted\n";
    }
}

echo "\n✅ Done! Inserted: $inserted, Skipped: $skipped\n";

$finalCount = $db->fetch("SELECT COUNT(*) as cnt FROM pincodes");
echo "Total pincodes in DB: " . $finalCount['cnt'] . "\n";