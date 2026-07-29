<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$stmt = $pdo->query("SELECT name, url, section FROM admin_menu_items WHERE is_active=1 AND url IN ('/admin/live-chat', '/admin/kyc', '/admin/hrm/departments', '/admin/hrm/designations', '/admin/company-loans', '/admin/backoffice/leaves', '/admin/compliance-scorecard') ORDER BY section");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['name'] . " | " . $row['url'] . " | " . $row['section'] . "\n";
}