<?php
/**
 * Audit Log - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

// List audit log
$db = \App\Core\App::database();
$audit = $db->fetchAll("SELECT * FROM audit_log ORDER BY created_at DESC LIMIT 100");
?>

