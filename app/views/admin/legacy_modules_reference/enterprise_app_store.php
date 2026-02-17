<?php
/**
 * Enterprise App Store - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}
?>
$db = \App\Core\App::database();
$apps = $db->fetchAll("SELECT * FROM app_store ORDER BY created_at DESC LIMIT 30");
?>
