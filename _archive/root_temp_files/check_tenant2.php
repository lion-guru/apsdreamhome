<?php
require_once 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = ['backups', 'email_queue', 'user_properties', 'api_keys', 'blog_posts', 'colonies', 'plots', 'crm_lead_forms', 'crm_segments', 'deals', 'expenses', 'invoices', 'invoice_items', 'team_members', 'land_acquisitions', 'land_records', 'messages', 'pages', 'site_content', 'training_courses', 'training_modules', 'telecaller_daily_tasks', 'telecaller_performance', 'khatabook_sales', 'gst_filings', 'payroll', 'salary'];
foreach ($tables as $t) {
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
}?>