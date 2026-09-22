<?php
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
$db->query("DELETE FROM login_attempts");
echo "Cleared all login attempts.\n";
