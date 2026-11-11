<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
foreach ($db->query("SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME != 'PRIMARY' AND CONSTRAINT_NAME LIKE 'fk_%'") as $r) {
    echo $r['TABLE_NAME'] . ': ' . $r['CONSTRAINT_NAME'] . "\n";
}