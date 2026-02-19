<?php
// require_once __DIR__ . '/../config/database.php';

// Helper to get DB connection
function getDBConnection()
{
    $host = 'localhost';
    $db   = 'apsdreamhome';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

$pdo = getDBConnection();
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "Total Tables: " . count($tables) . "\n\n";

// Define groups of potentially redundant tables
$groups = [
    'Users & Auth' => ['users', 'user', 'agents', 'associates', 'agent_users', 'customers', 'employees', 'admins'],
    'Leads & CRM' => ['leads', 'crm_leads', 'inquiries', 'property_inquiries', 'contact_inquiries'],
    'Properties' => ['properties', 'property_listings', 'listings', 'plots', 'flats'],
    'Payments' => ['payments', 'transactions', 'orders', 'invoices'],
    'Settings' => ['settings', 'system_settings', 'site_settings', 'config'],
    'Roles & Permissions' => ['roles', 'user_roles', 'permissions', 'user_permissions', 'role_permissions'],
    'Media' => ['media', 'gallery', 'images', 'uploads', 'files'],
    'Locations' => ['locations', 'cities', 'states', 'districts', 'areas'],
];

foreach ($groups as $groupName => $groupTables) {
    echo "--- $groupName ---\n";
    $found = [];
    foreach ($groupTables as $table) {
        if (in_array($table, $tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);
            $found[] = [
                'name' => $table,
                'rows' => $count,
                'columns' => count($cols),
                'col_names' => implode(', ', array_slice($cols, 0, 5)) . (count($cols) > 5 ? '...' : '')
            ];
        }
    }

    if (empty($found)) {
        echo "No tables found.\n";
    } else {
        foreach ($found as $t) {
            echo "✅ {$t['name']} ({$t['rows']} rows, {$t['columns']} cols): {$t['col_names']}\n";
        }

        // Suggest consolidation if multiple tables found
        if (count($found) > 1) {
            echo "⚠️ Potential Duplication: " . implode(', ', array_column($found, 'name')) . "\n";
        }
    }
    echo "\n";
}

// Check for other similar names
echo "--- Other Potential Duplicates (Name Similarity) ---\n";
sort($tables);
$prev = '';
foreach ($tables as $table) {
    if ($prev && (strpos($table, $prev) === 0 || levenshtein($table, $prev) < 3)) {
        echo "❓ $prev vs $table\n";
    }
    $prev = $table;
}
