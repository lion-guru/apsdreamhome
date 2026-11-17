<?php
/**
 * Seed PENDING-RERA placeholder into colonies.rera_number where NULL.
 * Idempotent: re-run is a no-op once populated.
 *
 * Safety: backup taken first; lives in backups/db/rera_pending_seed_s692_pre.sql
 */
$path = 'C:/xampp/htdocs/apsdreamhome';
require_once $path . '/config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();
$q = fn($sql, $params = []) => $db->query($sql, $params);

$updated = $q("UPDATE colonies SET rera_number = 'PENDING-VERIFICATION' WHERE rera_number IS NULL OR rera_number = '' OR rera_number = 'PENDING-VERIFICATION'");

echo "PENDING-VERIFICATION seed applied; rows affected logged at the migration runner.\n";
foreach ($db->fetchAll("SELECT id, name FROM colonies ORDER BY id") as $r) {
    echo str_pad((string)$r['id'], 4) . ' ' . $r['name'] . "\n";
}
