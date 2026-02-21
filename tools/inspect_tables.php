<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/bootstrap.php';

$app = \App\Core\App::getInstance();
$db = $app->db();

function getTableColumns($db, $table)
{
    try {
        $stmt = $db->query("SHOW COLUMNS FROM $table");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

$tables = [
    'user',
    'users',
    'agents',
    'associates',
    'user_roles',
    'roles',
    'password_reset_tokens',
    'api_requests',
    'farmer_profiles',
    'farmer_loans',
    'farmer_support_requests',
    'farmer_transactions',
    'two_factor_tokens',
    'mlm_profiles'
];
foreach ($tables as $table) {
    echo "Table: $table\n";
    $columns = getTableColumns($db, $table);
    if (empty($columns)) {
        echo "  [Does not exist or error]\n";
    } else {
        foreach ($columns as $col) {
            echo "  {$col['Field']} ({$col['Type']})\n";
        }
    }
    echo "\n";
}
