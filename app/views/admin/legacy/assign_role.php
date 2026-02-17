<?php
/**
 * Assign Role - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

if (!hasRole("Admin")) { header("Location: index.php?error=access_denied"); exit(); }
$csrf_token = generateCSRFToken();

$users = $db->fetchAll("SELECT id, name FROM employees WHERE status = :status ORDER BY name", ["status" => "active"]);
$roles = $db->fetchAll("SELECT id, name FROM roles ORDER BY name");

?>

