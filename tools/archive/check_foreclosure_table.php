<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Check if foreclosure_logs table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'foreclosure_logs'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'foreclosure_logs' exists.\n";

        // Show columns
        $stmt = $conn->query("DESCRIBE foreclosure_logs");
        echo "Columns:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "Table 'foreclosure_logs' DOES NOT exist.\n";

        // Suggest creation SQL
        echo "\nSuggestion: Create table with:\n";
        echo "CREATE TABLE foreclosure_logs (\n";
        echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        echo "    plan_id INT NOT NULL,\n";
        echo "    foreclosure_amount DECIMAL(10,2) NOT NULL,\n";
        echo "    original_amount DECIMAL(10,2) NOT NULL,\n";
        echo "    penalty_amount DECIMAL(10,2) DEFAULT 0.00,\n";
        echo "    waiver_amount DECIMAL(10,2) DEFAULT 0.00,\n";
        echo "    notes TEXT,\n";
        echo "    processed_by INT,\n"; // Admin ID
        echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
        echo ");\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
