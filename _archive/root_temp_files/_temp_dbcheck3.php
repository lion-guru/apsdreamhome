<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$r = $db->query('DESCRIBE employee_attendance');
$cols = [];
foreach ($r as $row) $cols[] = $row['Field'];
echo 'employee_attendance: ' . implode(', ', $cols) . PHP_EOL;

// Count tasks assigned to employees
$r = $db->query("SELECT COUNT(*) as cnt FROM tasks WHERE assigned_to IS NOT NULL");
echo 'tasks with assigned_to: ' . $r->fetch()['cnt'] . PHP_EOL;

// Count documents
$r = $db->query("SELECT COUNT(*) as cnt FROM documents WHERE entity_type = 'employee'");
echo 'employee documents: ' . $r->fetch()['cnt'] . PHP_EOL;

// Count performance reviews
$r = $db->query("SELECT COUNT(*) as cnt FROM performance_reviews");
echo 'performance_reviews: ' . $r->fetch()['cnt'] . PHP_EOL;

// Count activities
$r = $db->query("SELECT COUNT(*) as cnt FROM activity_logs_unified");
echo 'activity_logs_unified: ' . $r->fetch()['cnt'] . PHP_EOL;

// Count employees
$r = $db->query("SELECT COUNT(*) as cnt FROM employees WHERE status = 'active'");
echo 'active employees: ' . $r->fetch()['cnt'] . PHP_EOL;

// Employees sample
$r = $db->query("SELECT id, name, department, designation, role FROM employees LIMIT 5");
foreach ($r as $row) echo 'employee: ' . json_encode($row) . PHP_EOL;?>