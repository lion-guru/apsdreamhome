<?php
require_once 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
foreach (['api_keys'] as $t) {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM $t LIKE 'tenant_id'");
        if ($stmt->fetch()) {
            echo "$t: HAS tenant_id\n";
        } else {
            echo "$t: NO tenant_id\n";
        }
    } catch (\Exception $e) {
        echo "$t: TABLE NOT FOUND\n";
    }
}