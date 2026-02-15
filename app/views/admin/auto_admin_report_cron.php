<?php
/**
 * Daily Admin Report Cron Job
 * Generates and sends a system-wide summary to admins
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/notification_manager.php';
require_once __DIR__ . '/../includes/email_service.php';

// Use Database singleton
$db = \App\Core\App::database();
$nm = new NotificationManager($db, new EmailService());

// Ensure this script is run via CLI or authorized request
if (php_sapi_name() !== 'cli' && !isset($_GET['token'])) {
    $config_token = getenv('CRON_TOKEN') ?: 'aps_secret_token';
    if (($_GET['token'] ?? '') !== $config_token) {
        die("Unauthorized access.");
    }
}

try {
    // Ensure admin_reports table exists
    $db->execute("
        CREATE TABLE IF NOT EXISTS admin_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_date DATE NOT NULL,
            report_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 1. Lead Statistics
    $stats = $db->fetch("SELECT 
        COUNT(*) as total, 
        SUM(CASE WHEN status = 'New' THEN 1 ELSE 0 END) as new, 
        SUM(CASE WHEN status = 'Qualified' THEN 1 ELSE 0 END) as qualified, 
        SUM(CASE WHEN status = 'Contacted' THEN 1 ELSE 0 END) as contacted, 
        SUM(CASE WHEN status = 'Converted' THEN 1 ELSE 0 END) as converted 
        FROM leads");

    // 2. Revenue Today
    $revenue = $db->fetchOne("
        SELECT SUM(total_purchase_value) as revenue 
        FROM leads 
        WHERE status = 'Converted' 
        AND DATE(updated_at) = CURDATE()
    ")['revenue'] ?? 0;

    // 3. Upload Audit Summary
    $audit = $db->fetch("SELECT 
        COUNT(*) as total, 
        SUM(CASE WHEN slack_status = 'sent' THEN 1 ELSE 0 END) as slack_ok, 
        SUM(CASE WHEN slack_status != 'sent' THEN 1 ELSE 0 END) as slack_fail, 
        SUM(CASE WHEN telegram_status = 'sent' THEN 1 ELSE 0 END) as telegram_ok, 
        SUM(CASE WHEN telegram_status != 'sent' THEN 1 ELSE 0 END) as telegram_fail 
        FROM upload_audit_log 
        WHERE DATE(created_at) = CURDATE()");

    // 4. Top Uploader
    $top_uploader = $db->fetch("
        SELECT uploader, COUNT(*) as c 
        FROM upload_audit_log 
        WHERE DATE(created_at) = CURDATE() 
        GROUP BY uploader 
        ORDER BY c DESC 
        LIMIT 1
    ");

    // Construct the report text
    $report = "--- Leads Overview ---\n";
    $report .= "Total Leads: " . ($stats['total'] ?? 0) . "\n";
    $report .= "New: " . ($stats['new'] ?? 0) . " | Qualified: " . ($stats['qualified'] ?? 0) . " | Contacted: " . ($stats['contacted'] ?? 0) . " | Converted: " . ($stats['converted'] ?? 0) . "\n";
    $report .= "Today's Revenue: ₹" . number_format($revenue, 2) . "\n\n";

    $report .= "--- System Health (Uploads) ---\n";
    $report .= "Total Uploads: " . ($audit['total'] ?? 0) . "\n";
    $report .= "Slack Success: " . ($audit['slack_ok'] ?? 0) . " | Fail: " . ($audit['slack_fail'] ?? 0) . "\n";
    $report .= "Telegram Success: " . ($audit['telegram_ok'] ?? 0) . " | Fail: " . ($audit['telegram_fail'] ?? 0) . "\n";
    $report .= "Top Uploader: " . ($top_uploader['uploader'] ?? 'None') . " (" . ($top_uploader['c'] ?? 0) . ")\n";

    // Log report to database
    $db->execute("INSERT INTO admin_reports (report_date, report_text) VALUES (CURDATE(), ?)", [$report]);

    // Send to Admins
    // In production, fetch from user table with role='admin'
    $admins = $db->fetchAll("SELECT id, email FROM users WHERE role = 'Admin' OR role = 'super_admin'");
    
    // Fallback if no admins found in table (use default)
    if (empty($admins)) {
        $admins = [['id' => 1, 'email' => 'techguruabhay@gmail.com']];
    }

    foreach ($admins as $admin) {
        $nm->send([
            'user_id' => $admin['id'],
            'email' => $admin['email'],
            'template' => 'SYSTEM_SUMMARY',
            'data' => [
                'date' => date('Y-m-d'),
                'report' => $report
            ],
            'channels' => ['db', 'email']
        ]);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Admin report generated and sent successfully.\n";

} catch (Exception $e) {
    error_log("Admin Report Cron Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}
?>

