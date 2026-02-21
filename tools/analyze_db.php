<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Analyzing database tables structure...\n";

try {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $totalTables = count($tables);
    echo "Total tables found: $totalTables\n\n";

    $prefixes = [];
    foreach ($tables as $table) {
        $parts = explode('_', $table);
        $prefix = $parts[0];
        if (!isset($prefixes[$prefix])) {
            $prefixes[$prefix] = 0;
        }
        $prefixes[$prefix]++;
    }

    arsort($prefixes);

    echo "--- Table Groups by Prefix (Top 20) ---\n";
    $count = 0;
    foreach ($prefixes as $prefix => $num) {
        echo "$prefix: $num tables\n";
        $count++;
        if ($count >= 20) break;
    }

    echo "\n--- Sample Tables for Top Prefixes ---\n";
    $count = 0;
    foreach ($prefixes as $prefix => $num) {
        if ($num > 5) {
            echo "\nPrefix '$prefix' ($num tables): ";
            $samples = [];
            foreach ($tables as $t) {
                if (strpos($t, $prefix . '_') === 0) {
                    $samples[] = $t;
                    if (count($samples) >= 3) break;
                }
            }
            echo implode(', ', $samples) . "...\n";
        }
        $count++;
        if ($count >= 5) break;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
