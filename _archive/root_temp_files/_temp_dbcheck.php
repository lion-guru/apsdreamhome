<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$tables = ['documents','tasks','performance_reviews','activity_logs_unified','user_activity_logs_unified','employees','departments','designations','employee_designation_roles','attendance'];
foreach ($tables as $t) {
    try {
        $r = $db->query('DESCRIBE ' . $t);
        $cols = [];
        foreach ($r as $row) $cols[] = $row['Field'];
        echo $t . ': ' . implode(', ', $cols) . PHP_EOL;
    } catch (Exception $e) {
        echo $t . ': NOT FOUND' . PHP_EOL;
    }
}
