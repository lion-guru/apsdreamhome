<?php
/**
 * MLM Rank Recalculation Script
 * -----------------------------
 * Usage: php tools/mlm_rank_recalculate.php
 *
 * Updates mlm_profiles.current_level according to business volume thresholds.
 */

require_once __DIR__ . '/../includes/config.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

if (!$conn) {
    fwrite(STDERR, "Database connection failed.\n");
    exit(1);
}

$ranks = [
    ['label' => 'Site Manager', 'min' => 50000000, 'reward' => 'Car'],
    ['label' => 'President', 'min' => 30000000, 'reward' => 'Bullet Bike'],
    ['label' => 'Vice President', 'min' => 15000000, 'reward' => 'Pulsar Bike'],
    ['label' => 'Sr. BDM', 'min' => 7000000, 'reward' => 'Domestic/Foreign Tour'],
    ['label' => 'BDM', 'min' => 3500000, 'reward' => 'Laptop'],
    ['label' => 'Sr. Associate', 'min' => 1000000, 'reward' => 'Tablet'],
    ['label' => 'Associate', 'min' => 0, 'reward' => 'Mobile'],
];

$updated = 0;
$errors = 0;

try {
    $stmt = $conn->query('SELECT id, user_id, lifetime_sales, current_level FROM mlm_profiles');
    $profiles = $stmt->fetch_all(MYSQLI_ASSOC);

    $updateStmt = $conn->prepare(
        'UPDATE mlm_profiles SET current_level = ?, rank_updated_at = NOW() WHERE id = ?'
    );

    foreach ($profiles as $profile) {
        $newRank = determineRank((float)$profile['lifetime_sales'], $ranks);

        if ($newRank !== $profile['current_level']) {
            $updateStmt->bind_param('si', $newRank, $profile['id']);
            if ($updateStmt->execute()) {
                $updated++;
            } else {
                $errors++;
            }
        }
    }

    $updateStmt->close();
} catch (Throwable $e) {
    fwrite(STDERR, 'Rank recalculation error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo "Rank recalculation complete. Updated: {$updated}. Errors: {$errors}.\n";

function determineRank(float $businessAmount, array $ranks): string
{
    foreach ($ranks as $rank) {
        if ($businessAmount >= $rank['min']) {
            return $rank['label'];
        }
    }

    return 'Associate';
}
