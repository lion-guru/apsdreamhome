<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$pairs = [
    'user' => 'users',
    'agent' => 'agents',
    'associate' => 'associates',
    'customer' => 'customers',
    'employee' => 'employees',
    'project' => 'projects',
    'property' => 'properties',
    'lead' => 'leads',
    'setting' => 'settings',
];

echo "Checking for singular/plural table duplication...\n";
foreach ($pairs as $singular => $plural) {
    $hasSingular = false;
    $hasPlural = false;
    $singularCount = 0;
    $pluralCount = 0;

    try {
        $stmt = $conn->query("SHOW TABLES LIKE '$singular'");
        if ($stmt->fetch()) {
            $hasSingular = true;
            $singularCount = $conn->query("SELECT COUNT(*) FROM `$singular`")->fetchColumn();
        }

        $stmt = $conn->query("SHOW TABLES LIKE '$plural'");
        if ($stmt->fetch()) {
            $hasPlural = true;
            $pluralCount = $conn->query("SELECT COUNT(*) FROM `$plural`")->fetchColumn();
        }

        if ($hasSingular && $hasPlural) {
            echo "⚠️  DUPLICATE FOUND: '$singular' ($singularCount rows) vs '$plural' ($pluralCount rows)\n";
        } elseif ($hasSingular) {
            echo "ℹ️  Only Singular: '$singular' ($singularCount rows)\n";
        } elseif ($hasPlural) {
            echo "✅ Only Plural: '$plural' ($pluralCount rows)\n";
        } else {
            echo "❌ Missing both: '$singular' / '$plural'\n";
        }

    } catch (PDOException $e) {
        echo "Error checking $singular/$plural: " . $e->getMessage() . "\n";
    }
}
