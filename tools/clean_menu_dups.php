<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Removing duplicate menu items...\n";

// Remove All Leads from crm (duplicate of Leads)
$pdo->exec("DELETE FROM admin_menu_items WHERE section = 'crm' AND name = 'All Leads' AND url = '/admin/leads'");
echo "  Removed 'All Leads' (crm, dup)\n";

// Remove Bulk Actions (same URL as Leads, redundant)
$pdo->exec("DELETE FROM admin_menu_items WHERE section = 'crm' AND name = 'Bulk Actions'");
echo "  Removed 'Bulk Actions' (crm, redundant)\n";

// Remove All Campaigns (dup of Campaigns)
$pdo->exec("DELETE FROM admin_menu_items WHERE section = 'marketing' AND name = 'All Campaigns'");
echo "  Removed 'All Campaigns' (marketing, dup)\n";

// Move Add Plot and Plot Categories from bookings to properties
$pdo->exec("UPDATE admin_menu_items SET section = 'properties' WHERE section = 'bookings' AND url IN ('/admin/plots/create', '/admin/plots/categories')");
echo "  Moved 'Add Plot' and 'Plot Categories' → properties\n";

// Rename 'Locations' to 'States' since it's the states page
$pdo->exec("UPDATE admin_menu_items SET name = 'States' WHERE url = '/admin/locations/states' AND section = 'locations'");
echo "  Renamed 'Locations' → 'States'\n";

echo "\nDone!\n";

$items = $pdo->query("SELECT section, COUNT(*) as cnt FROM admin_menu_items GROUP BY section ORDER BY section")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== FINAL STRUCTURE ===\n";
$total = 0;
foreach ($items as $i) {
    echo "  {$i['section']}: {$i['cnt']} items\n";
    $total += $i['cnt'];
}
echo "  Total: $total items\n";
