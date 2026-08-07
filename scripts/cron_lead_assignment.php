<?php
/**
 * CRON: Lead Auto-Assignment
 * Runs every 5 minutes to assign new unassigned leads to active telecallers.
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    define('APP_ROOT', $root);
    require_once $root . '/app/Core/Autoloader.php';
    $autoloader = \App\Core\Autoloader::getInstance();
    $autoloader->register();

    // Default tenant or loop through all tenants
    $tenantId = 1; 
    \App\Core\Middleware\TenantContext::setById($tenantId, $pdo);
    $tenantSql = $tenantId > 1 ? " AND tenant_id = " . (int)$tenantId : "";

    $db = \App\Core\Database\Database::getInstance();
    $assignmentService = new \App\Services\CRM\LeadAssignmentService($db);

    // 1. Find all unassigned leads
    $unassignedLeads = $db->fetchAll(
        "SELECT id FROM leads WHERE assigned_to IS NULL AND status = 'new'" . $tenantSql . " ORDER BY created_at ASC LIMIT 100"
    );

    if (empty($unassignedLeads)) {
        echo "[" . date('Y-m-d H:i:s') . "] No unassigned leads found.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Found " . count($unassignedLeads) . " unassigned leads.\n";

    $assignedCount = 0;
    foreach ($unassignedLeads as $lead) {
        $result = $assignmentService->assignLead((int)$lead['id']);
        if ($result['success']) {
            echo "  [+] Lead #{$lead['id']} assigned to Employee #{$result['assigned_to']}\n";
            $assignedCount++;
        } else {
            echo "  [-] Lead #{$lead['id']} assignment failed: {$result['message']}\n";
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Successfully assigned {$assignedCount} leads.\n";

} catch (\Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] CRON ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
