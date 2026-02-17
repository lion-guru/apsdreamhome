<?php
/**
 * Tasks Management - Updated with Session Management
 */

require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// List tasks
$tasks = $db->fetchAll("SELECT t.*, e.name as assignee FROM tasks t LEFT JOIN employees e ON t.assigned_to = e.id ORDER BY t.due_date ASC, t.status DESC");

?>

