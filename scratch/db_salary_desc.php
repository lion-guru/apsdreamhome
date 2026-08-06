<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$tables = ['employee_salary_structure', 'salary_payments', 'salary_structures', 'employee_attendance', 'employee_leaves', 'salary_tracker'];
foreach ($tables as $t) {
    echo "=== $t ===\n";
    $cols = $db->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "{$c['Field']} ({$c['Type']})\n";
    }
}
