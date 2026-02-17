<?php
require_once dirname(__DIR__, 5) . '/vendor/autoload.php';
use App\Core\Database;

// Security Check: Only allow execution from CLI or with a valid secret key
$is_cli = (PHP_SAPI === 'cli' || (isset($_SERVER['TERM']) && $_SERVER['TERM']));
$secret_key = 'your_secret_cron_key_here'; // Should be moved to config/env
$provided_key = $_GET['key'] ?? '';

if (!$is_cli && $provided_key !== $secret_key) {
    header('HTTP/1.1 403 Forbidden');
    die('Unauthorized access: This script can only be run from the command line or with a valid secret key.');
}

require_once dirname(__DIR__, 5) . '/includes/notification_manager.php';
require_once dirname(__DIR__, 5) . '/includes/email_service.php';

// Initialize Notification Manager
$db = \App\Core\App::database();
$emailService = new EmailService();
$notificationManager = new NotificationManager($db, $emailService);

// Set execution time limit to 5 minutes
set_time_limit(300);

function updateInstallmentStatus() {
    $db = \App\Core\App::database();
    global $notificationManager;
    
    // Get all pending installments that are overdue
    $query = "UPDATE emi_installments 
              SET payment_status = 'overdue',
                  updated_at = NOW()
              WHERE payment_status = 'pending' 
              AND due_date < CURDATE()";
    $db->execute($query);
    
    // Get overdue installments for notification
    $query = "SELECT ei.*, ep.customer_id, c.name as customer_name, p.title as property_title
              FROM emi_installments ei
              JOIN emi_plans ep ON ei.emi_plan_id = ep.id
              JOIN customers c ON ep.customer_id = c.id
              JOIN properties p ON ep.property_id = p.id
              WHERE ei.payment_status = 'overdue'
              AND (ei.last_reminder_date IS NULL 
              OR ei.last_reminder_date < DATE_SUB(NOW(), INTERVAL 3 DAY))";
    
    $results = $db->fetchAll($query);
    foreach ($results as $row) {
        // Send notification to customer using NotificationManager
        $notificationManager->send([
            'user_id' => $row['customer_id'],
            'template_key' => 'EMI_OVERDUE',
            'template_data' => [
                'customer_name' => $row['customer_name'],
                'amount' => number_format($row['amount'], 2),
                'property_title' => $row['property_title'],
                'due_date' => date('d M Y', strtotime($row['due_date']))
            ],
            'type' => 'emi_overdue',
            'link' => "customer/payments/emi.php?id=" . $row['emi_plan_id']
        ]);
        
        // Update last reminder date
        $updateQuery = "UPDATE emi_installments 
                       SET last_reminder_date = NOW()
                       WHERE id = ?";
        $db->execute($updateQuery, [$row['id']]);
    }
}

function sendUpcomingPaymentReminders() {
    $db = \App\Core\App::database();
    global $notificationManager;
    
    // Get installments due in next 3 days
    $query = "SELECT ei.*, ep.customer_id, c.name as customer_name, 
                     c.email as customer_email, p.title as property_title
              FROM emi_installments ei
              JOIN emi_plans ep ON ei.emi_plan_id = ep.id
              JOIN customers c ON ep.customer_id = c.id
              JOIN properties p ON ep.property_id = p.id
              WHERE ei.payment_status = 'pending'
              AND ei.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
              AND (ei.reminder_sent IS NULL OR ei.reminder_sent = 0)";
    
    $results = $db->fetchAll($query);
    foreach ($results as $row) {
        // Send notification using NotificationManager (handles both In-App and Email)
        $notificationManager->send([
            'user_id' => $row['customer_id'],
            'template_key' => 'EMI_REMINDER',
            'template_data' => [
                'customer_name' => $row['customer_name'],
                'amount' => number_format($row['amount'], 2),
                'property_title' => $row['property_title'],
                'due_date' => date('d M Y', strtotime($row['due_date']))
            ],
            'type' => 'emi_reminder',
            'link' => "customer/payments/emi.php?id=" . $row['emi_plan_id']
        ]);
        
        // Mark reminder as sent
        $updateQuery = "UPDATE emi_installments 
                       SET reminder_sent = 1
                       WHERE id = ?";
        $db->execute($updateQuery, [$row['id']]);
    }
}

function checkDefaultedPlans() {
    $db = \App\Core\App::database();
    global $notificationManager;
    
    // Get plans with 3 or more consecutive overdue installments
    $query = "SELECT ep.*, 
                     COUNT(ei.id) as overdue_count,
                     c.name as customer_name,
                     c.email as customer_email,
                     p.title as property_title
              FROM emi_plans ep
              JOIN emi_installments ei ON ep.id = ei.emi_plan_id
              JOIN customers c ON ep.customer_id = c.id
              JOIN properties p ON ep.property_id = p.id
              WHERE ep.status = 'active'
              AND ei.payment_status = 'overdue'
              GROUP BY ep.id
              HAVING overdue_count >= 3";
    
    $results = $db->fetchAll($query);
    foreach ($results as $row) {
        // Update plan status to defaulted
        $updateQuery = "UPDATE emi_plans 
                       SET status = 'defaulted',
                           updated_at = NOW()
                       WHERE id = ?";
        $db->execute($updateQuery, [$row['id']]);
        
        // Send notification to admin using NotificationManager
        // Get admin users
        $adminQuery = "SELECT id, email, name FROM admin WHERE role = 'admin'";
        $admins = $db->fetchAll($adminQuery);
        foreach ($admins as $admin) {
            $notificationManager->send([
                'user_id' => $admin['id'],
                'template_key' => 'EMI_DEFAULT_ADMIN',
                'template_data' => [
                    'admin_name' => $admin['name'],
                    'customer_name' => $row['customer_name'],
                    'property_title' => $row['property_title'],
                    'overdue_count' => $row['overdue_count']
                ],
                'type' => 'emi_default',
                'link' => "admin/accounting/view_emi_plan.php?id=" . $row['id']
            ]);
        }
        
        // Send notification to customer using NotificationManager
        $notificationManager->send([
            'user_id' => $row['customer_id'],
            'template_key' => 'EMI_DEFAULT_CUSTOMER',
            'template_data' => [
                'customer_name' => $row['customer_name'],
                'property_title' => $row['property_title'],
                'overdue_count' => $row['overdue_count']
            ],
            'type' => 'emi_default'
        ]);
    }
}

function generateMonthlyReport() {
    $db = \App\Core\App::database();
    global $notificationManager;
    
    $month = date('m');
    $year = date('Y');
    
    // Get monthly collection stats
    $query = "SELECT 
                COUNT(DISTINCT ep.id) as total_active_plans,
                COUNT(DISTINCT CASE WHEN ei.payment_status = 'paid' THEN ei.id END) as paid_installments,
                COUNT(DISTINCT CASE WHEN ei.payment_status = 'overdue' THEN ei.id END) as overdue_installments,
                SUM(CASE WHEN ei.payment_status = 'paid' THEN ei.amount ELSE 0 END) as total_collected,
                SUM(CASE WHEN ei.payment_status = 'overdue' THEN ei.amount ELSE 0 END) as total_overdue
              FROM emi_plans ep
              LEFT JOIN emi_installments ei ON ep.id = ei.emi_plan_id
              WHERE MONTH(ei.due_date) = ? AND YEAR(ei.due_date) = ?";
    
    $stats = $db->fetch($query, [$month, $year]);
    
    // Generate PDF report
    require_once dirname(__DIR__, 5) . '/vendor/autoload.php';
    require_once dirname(__DIR__, 5) . '/vendor/tecnickcom/tcpdf/tcpdf.php';
    
    // Define PDF constants if not already defined
    if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION', 'P');
    if (!defined('PDF_UNIT')) define('PDF_UNIT', 'mm');
    if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT', 'A4');
    
    /** @var TCPDF $pdf */
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('APS Dream Home');
    $pdf->SetAuthor('APS Dream Home');
    $pdf->SetTitle('EMI Collection Report - ' . date('F Y'));
    
    $pdf->AddPage();
    
    // Add company logo
    $pdf->Image(dirname(__DIR__, 5) . '/assets/img/logo.png', 15, 15, 50);
    
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 20, 'EMI Collection Report - ' . date('F Y'), 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 12);
    
    // Add statistics
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Collection Statistics:', 0, 1);
    $pdf->Cell(100, 8, 'Active EMI Plans:', 0);
    $pdf->Cell(0, 8, $stats['total_active_plans'], 0, 1);
    $pdf->Cell(100, 8, 'Paid Installments:', 0);
    $pdf->Cell(0, 8, $stats['paid_installments'], 0, 1);
    $pdf->Cell(100, 8, 'Overdue Installments:', 0);
    $pdf->Cell(0, 8, $stats['overdue_installments'], 0, 1);
    $pdf->Cell(100, 8, 'Total Amount Collected:', 0);
    $pdf->Cell(0, 8, '₹' . number_format($stats['total_collected'] ?? 0, 2), 0, 1);
    $pdf->Cell(100, 8, 'Total Amount Overdue:', 0);
    $pdf->Cell(0, 8, '₹' . number_format($stats['total_overdue'] ?? 0, 2), 0, 1);
    
    // Save PDF
    $pdfPath = dirname(__DIR__, 5) . '/reports/emi_report_' . date('Y_m') . '.pdf';
    $pdf->Output($pdfPath, 'F');
    
    // Save report reference in database
    $query = "INSERT INTO reports (
                title,
                type,
                content,
                file_path,
                generated_for_month,
                generated_for_year,
                created_at
              ) VALUES (?, 'emi', ?, ?, ?, ?, NOW())";
    
    $reportTitle = "EMI Collection Report - " . date('F Y');
    $reportContent = json_encode($stats);
    
    $db->execute($query, [$reportTitle, $reportContent, $pdfPath, $month, $year]);
    $reportId = $db->lastInsertId();
    
    // Send report to admin
    $adminQuery = "SELECT id, email, name FROM admin WHERE role = 'admin'";
    $admins = $db->fetchAll($adminQuery);
    foreach ($admins as $admin) {
        $notificationManager->send([
            'user_id' => $admin['id'],
            'template_key' => 'EMI_MONTHLY_REPORT',
            'template_data' => [
                'admin_name' => $admin['name'],
                'report_month' => date('F Y')
            ],
            'type' => 'emi_report',
            'link' => "admin/reports/view.php?id=" . $reportId,
            'attachments' => [$pdfPath]
        ]);
    }
}

try {
    // Start transaction
    $db->beginTransaction();
    
    // Run automation tasks
    updateInstallmentStatus();
    sendUpcomingPaymentReminders();
    checkDefaultedPlans();
    
    // Generate monthly report on first day of month
    if (date('j') === '1') {
        generateMonthlyReport();
    }
    
    // Commit transaction
    $db->commit();
    
    echo "EMI automation tasks completed successfully.\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db) && $db->isInTransaction()) {
        $db->rollBack();
    }
    
    echo "Error running EMI automation tasks: " . $e->getMessage() . "\n";
    
    // Log error
    error_log("EMI Automation Error: " . $e->getMessage());
}
?>
