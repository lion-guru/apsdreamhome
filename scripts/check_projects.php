<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
echo "📊 Projects in Database:\n";
echo str_repeat("=", 60) . "\n";

// Get projects table structure
$columns = $pdo->query("SHOW COLUMNS FROM projects")->fetchAll(PDO::FETCH_COLUMN);
echo "Table columns: " . implode(', ', $columns) . "\n\n";

// Get projects
$stmt = $pdo->query("SELECT * FROM projects LIMIT 5");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($projects) > 0) {
    echo "🏗️ Projects in Database:\n";
    foreach ($projects as $p) {
        $name = $p['name'] ?? $p['title'] ?? 'Unknown';
        $location = $p['location'] ?? $p['address'] ?? $p['city'] ?? 'N/A';
        $city = $p['city'] ?? $p['district'] ?? 'N/A';
        $status = $p['status'] ?? 'unknown';
        echo "  • {$name} - {$location}, {$city} ({$status})\n";
    }
} else {
    echo "⚠️ No projects found in database!\n";
}
