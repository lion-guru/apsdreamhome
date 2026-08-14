<?php
/**
 * Create property_views table for tracking propertyæµ�è§ˆ behavior
 * and auto-generating leads from browsing patterns
 */

$host = '127.0.0.1';
$port = 3307;
$user = 'root';
$pass = '';
$db   = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("USE $db");

    // Create property_views table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS property_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            user_id INT DEFAULT NULL,
            visitor_fingerprint VARCHAR(64) DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            referrer VARCHAR(500) DEFAULT NULL,
            viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_property (property_id),
            INDEX idx_user (user_id),
            INDEX idx_ip (ip_address),
            INDEX idx_viewed_at (viewed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    echo "OK â€” property_views table created\n";

    // Verify leads table has the columns we need
    $cols = [];
    $rows = $pdo->query("DESCRIBE leads")->fetchAll();
    foreach ($rows as $row) {
        $cols[] = $row['Field'];
    }

    $needed = ['property_interest', 'budget_range', 'lead_score', 'source'];
    $missing = array_diff($needed, $cols);
    if (!empty($missing)) {
        echo "Missing columns in leads table: " . implode(', ', $missing) . "\n";
        // Add missing columns
        if (in_array('property_interest', $missing)) {
            $pdo->exec("ALTER TABLE leads ADD COLUMN property_interest VARCHAR(500) DEFAULT NULL AFTER location_preference");
            echo "  + Added property_interest\n";
        }
        if (in_array('budget_range', $missing)) {
            $pdo->exec("ALTER TABLE leads ADD COLUMN budget_range VARCHAR(100) DEFAULT NULL AFTER property_interest");
            echo "  + Added budget_range\n";
        }
        if (in_array('lead_score', $missing)) {
            $pdo->exec("ALTER TABLE leads ADD COLUMN lead_score INT DEFAULT 0 AFTER priority");
            echo "  + Added lead_score\n";
        }
        if (in_array('source', $missing)) {
            $pdo->exec("ALTER TABLE leads ADD COLUMN source VARCHAR(100) DEFAULT 'manual' AFTER lead_score");
            echo "  + Added source\n";
        }
    } else {
        echo "OK â€” leads table has all required columns\n";
    }

    echo "\nDone! Property views tracking is ready.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>