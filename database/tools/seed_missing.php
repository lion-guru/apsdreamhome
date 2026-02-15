<?php
require_once 'config/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Seed locations (if empty)
    $locCount = $pdo->query("SELECT COUNT(*) FROM locations")->fetchColumn();
    if ($locCount < 10) {
        $pdo->exec("INSERT INTO locations (name, city, state, country) VALUES
            ('Connaught Place', 'Delhi', 'Delhi', 'India'),
            ('Sector 18', 'Noida', 'UP', 'India'),
            ('DLF Phase 1', 'Gurugram', 'Haryana', 'India'),
            ('Baner', 'Pune', 'Maharashtra', 'India'),
            ('Whitefield', 'Bengaluru', 'Karnataka', 'India')");
        echo "✅ Added 5 demo locations\n";
    }

    // Seed agents (if empty)
    $agentCount = $pdo->query("SELECT COUNT(*) FROM agents")->fetchColumn();
    if ($agentCount < 5) {
        $pdo->exec("INSERT INTO agents (name, email, phone, commission_rate) VALUES
            ('Rajesh Kumar', 'rajesh@aps.com', '9876543210', 2.5),
            ('Priya Sharma', 'priya@aps.com', '9123456789', 3.0),
            ('Amit Singh', 'amit@aps.com', '9988776655', 2.8)");
        echo "✅ Added 3 demo agents\n";
    }

    // Seed categories (if empty)
    $catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($catCount < 5) {
        $pdo->exec("INSERT INTO categories (name, description) VALUES
            ('Apartment', 'Residential apartment units'),
            ('Villa', 'Independent luxury villas'),
            ('Plot', 'Residential or commercial plots'),
            ('Office', 'Commercial office spaces'),
            ('Shop', 'Retail shop spaces')");
        echo "✅ Added 5 demo categories\n";
    }

    echo "🎉 Seed complete – all empty tables now have demo data!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}