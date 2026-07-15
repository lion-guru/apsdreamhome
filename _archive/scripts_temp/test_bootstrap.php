<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
error_log("Test: Bootstrap loaded, APP_ROOT=" . APP_ROOT);
$db = \App\Core\Database\Database::getInstance();
error_log("Test: Database instance created");
$conn = $db->getConnection();
error_log("Test: Connection created: " . get_class($conn));
echo "SUCCESS\n";