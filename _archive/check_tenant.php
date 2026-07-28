<?php
require_once 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = ['user_properties', 'email_templates', 'colonies', 'plots', 'email_queue', 'sms_queue', 'campaigns', 'crm_lead_forms', 'crm_segments', 'deals', 'expenses', 'invoices', 'invoice_items', 'team_members', 'land_acquisitions', 'land_records', 'messages', 'pages', 'site_content', 'training_courses', 'training_modules', 'telecaller_daily_tasks', 'telecaller_performance', 'khatabook_sales', 'gst_filings', 'payroll', 'salary', 'backups'];
foreach ($tables as $t) {
    try {
        $stmt = $db->query('SHOW COLUMNS FROM ' . $t . ' LIKE "tenant_id"');
        if ($stmt->fetch()) {
            echo $t . ': HAS tenant_id' . PHP_EOL;
        } else {
            echo $t . ': NO tenant_id' . PHP_EOL;
        }
    } catch (Exception $e) {
        echo $t . ': ERROR - ' . $e->getMessage() . PHP_EOL;
    }
}