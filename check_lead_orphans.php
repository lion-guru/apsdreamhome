<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
$db->setAttribute(PDO::ATTR_TIMEOUT, 10);

// Find orphaned assigned_to values
$orphans = $db->query("SELECT DISTINCT l.assigned_to FROM leads l LEFT JOIN users u ON l.assigned_to = u.id WHERE l.assigned_to IS NOT NULL AND u.id IS NULL")->fetchAll(PDO::FETCH_COLUMN);
echo "Orphan assigned_to values: " . implode(', ', $orphans) . "\n";
echo "Count: " . count($orphans) . "\n";

// Check total leads with assigned_to
$total = $db->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NOT NULL")->fetchColumn();
echo "Total leads with assigned_to: $total\n";

// Check what users exist
$users = $db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
echo "User IDs: " . implode(', ', $users) . "\n";

// Sample leads
$leads = $db->query("SELECT id, assigned_to FROM leads LIMIT 10")->fetchAll();
foreach ($leads as $l) {
    echo "Lead {$l['id']}: assigned_to = " . ($l['assigned_to'] ?? 'NULL') . "\n";
}