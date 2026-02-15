<?php
require_once 'config/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Seed locations (only if empty)
    if ($pdo->query("SELECT COUNT(*) FROM locations")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO locations (name, city, state, country) VALUES
            ('Connaught Place', 'Delhi', 'Delhi', 'India'),
            ('Sector 18', 'Noida', 'UP', 'India'),
            ('DLF Phase 1', 'Gurugram', 'Haryana', 'India')");
        echo "✅ Added 3 demo locations\n";
    }

    // Seed agents (only if empty)
    if ($pdo->query("SELECT COUNT(*) FROM agents")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO agents (name, email, phone, commission_rate) VALUES
            ('Rajesh Kumar', 'rajesh@aps.com', '9876543210', 2.5),
            ('Priya Sharma', 'priya@aps.com', '9123456789', 3.0)");
        echo "✅ Added 2 demo agents\n";
    }

    // Seed categories (only if empty)
    if ($pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO categories (name, description) VALUES
            ('Apartment', 'Residential apartment units'),
            ('Villa', 'Independent luxury villas')");
        echo "✅ Added 2 demo categories\n";
    }

    echo "🎉 Empty tables seeded – ready for testing!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}