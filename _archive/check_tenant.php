<?php
require_once 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
foreach (['backups', 'email_queue'] as $t) {
    try {
        $stmt = $db->query('SHOW COLUMNS FROM ' . $t . ' LIKE "tenant_id"');
        if ($stmt->fetch()) {
            echo $t . ': HAS tenant_id' . PHP_EOL;
        } else {
            echo $t . ': NO tenant_id' . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo $t . ': TABLE NOT FOUND' . PHP_EOL;
    }
}?>