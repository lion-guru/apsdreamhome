<?php
/**
 * Support Tickets - Updated with Session Management
 */

require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// List support tickets
$tickets = $db->fetchAll("SELECT * FROM support_tickets ORDER BY created_at DESC");

?>

