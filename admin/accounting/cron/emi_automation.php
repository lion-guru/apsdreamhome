<?php
require_once '../../../includes/config.php';

$conn = $con;

// Set execution time limit to 5 minutes
set_time_limit(300);

function sendNotification($userId, $type, $title, $message, $link = '') {
    global $conn;
    $query = "INSERT INTO notifications (user_id, type, title, message, link, created_at)
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issss", $userId, $type, $title, $message, $link);
    $stmt->execute();
}

function updateInstallmentStatus() {
    global $conn;
    
    // Get all pending installments that are overdue
    $query = "UPDATE emi_installments 
              SET payment_status = 'overdue',
                  updated_at = NOW()
              WHERE payment_status = 'pending' 
              AND due_date < CURDATE()";
    $conn->query($query);
    
    // Get overdue installments for notification
    $query = "SELECT ei.*, ep.customer_id, c.name as customer_name, p.title as property_title
              FROM emi_installments ei
              JOIN emi_plans ep ON ei.emi_plan_id = ep.id
              JOIN customers c ON ep.customer_id = c.id
              JOIN properties p ON ep.property_id = p.id
              WHERE ei.payment_status = 'overdue'
              AND ei.last_reminder_date IS NULL 
              OR ei.last_reminder_date < DATE_SUB(NOW(), INTERVAL 3 DAY)";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        // Send notification to customer
        $title = "EMI Payment Overdue";
        $message = "Your EMI payment of ₹" . number_format($row['amount'], 2) . 
                  " for property " . $row['property_title'] . " was due on " . 
                  date('d M Y', strtotime($row['due_date']));
        $link = "customer/payments/emi.php?id=" . $row['emi_plan_id'];
        
        sendNotification($row['customer_id'], 'emi_overdue', $title, $message, $link);
        
        // Update last reminder date
        $updateQuery = "UPDATE emi_installments 
                       SET last_reminder_date = NOW()
                       WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
    }
}

function sendUpcomingPaymentReminders() {
    global $conn;
    
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
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        // Send notification
        $title = "Upcoming EMI Payment Reminder";
        $message = "Your EMI payment of ₹" . number_format($row['amount'], 2) . 
                  " for property " . $row['property_title'] . " is due on " . 
                  date('d M Y', strtotime($row['due_date']));
        $link = "customer/payments/emi.php?id=" . $row['emi_plan_id'];
        
        sendNotification($row['customer_id'], 'emi_reminder', $title, $message, $link);
        
        // Send email notification
        $to = $row['customer_email'];
        $subject = $title;
        $emailMessage = "Dear " . $row['customer_name'] . ",\n\n" . $message . "\n\n";
        $emailMessage .= "Please log in to your account to make the payment.\n\n";
        $emailMessage .= "Best regards,\nAPS Dream Home Team";
        
        mail($to, $subject, $emailMessage);
        
        // Mark reminder as sent
        $updateQuery = "UPDATE emi_installments 
                       SET reminder_sent = 1
                       WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
    }
}

function checkDefaultedPlans() {
    global $conn;
    
    // Get plans with 3 or more consecutive overdue installments
    $query = "SELECT ep.*, 
                     COUNT(ei.id) as overdue_count,
                     c.name as customer_name,
                     p.title as property_title
              FROM emi_plans ep
              JOIN emi_installments ei ON ep.id = ei.emi_plan_id
              JOIN customers c ON ep.customer_id = c.id
              JOIN properties p ON ep.property_id = p.id
              WHERE ep.status = 'active'
              AND ei.payment_status = 'overdue'
              GROUP BY ep.id
              HAVING overdue_count >= 3";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        // Update plan status to defaulted
        $updateQuery = "UPDATE emi_plans 
                       SET status = 'defaulted',
                           updated_at = NOW()
                       WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        
        // Send notification to admin
        $adminTitle = "EMI Plan Defaulted";
        $adminMessage = "EMI plan for property " . $row['property_title'] . 
                       " by customer " . $row['customer_name'] . 
                       " has been marked as defaulted due to " . 
                       $row['overdue_count'] . " overdue payments.";
        $adminLink = "admin/accounting/view_emi_plan.php?id=" . $row['id'];
        
        // Get admin users
        $adminQuery = "SELECT id FROM users WHERE role = 'admin'";
        $adminResult = $conn->query($adminQuery);
        while ($admin = $adminResult->fetch_assoc()) {
            sendNotification($admin['id'], 'emi_default', $adminTitle, $adminMessage, $adminLink);
        }
        
        // Send email to customer
        $customerTitle = "EMI Plan Default Notice";
        $customerMessage = "Dear " . $row['customer_name'] . ",\n\n" .
                         "Your EMI plan for property " . $row['property_title'] . 
                         " has been marked as defaulted due to " . $row['overdue_count'] . 
                         " consecutive overdue payments.\n\n" .
                         "Please contact our office immediately to resolve this issue.\n\n" .
                         "Best regards,\nAPS Dream Home Team";
        
        mail($row['customer_email'], $customerTitle, $customerMessage);
    }
}

function generateMonthlyReport() {
    global $conn;
    
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
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    
    // Generate PDF report
    require_once '../../../vendor/autoload.php';
    require_once '../../../vendor/tecnickcom/tcpdf/tcpdf.php';
    
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
    $pdf->Image('../../../assets/img/logo.png', 15, 15, 50);
    
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
    $pdf->Cell(0, 8, '₹' . number_format($stats['total_collected'], 2), 0, 1);
    $pdf->Cell(100, 8, 'Total Amount Overdue:', 0);
    $pdf->Cell(0, 8, '₹' . number_format($stats['total_overdue'], 2), 0, 1);
    
    // Save PDF
    $pdfPath = '../../../reports/emi_report_' . date('Y_m') . '.pdf';
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
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssii", $reportTitle, $reportContent, $pdfPath, $month, $year);
    $stmt->execute();
    $reportId = $stmt->insert_id;
    
    // Send report to admin
    $adminQuery = "SELECT id, email FROM users WHERE role = 'admin'";
    $adminResult = $conn->query($adminQuery);
    while ($admin = $adminResult->fetch_assoc()) {
        // Send in-app notification
        sendNotification(
            $admin['id'],
            'emi_report',
            $reportTitle,
            "Monthly EMI collection report is now available.",
            "admin/reports/view.php?id=" . $reportId
        );
        
        // Send email with PDF attachment
        $to = $admin['email'];
        $subject = $reportTitle;
        $message = "Dear Admin,\n\n" .
                  "The monthly EMI collection report for " . date('F Y') . " is attached.\n\n" .
                  "Best regards,\nAPS Dream Home System";
        
        $headers = "From: system@apsdreamhome.com";
        
        // Email with attachment
        $attachment = chunk_split(base64_encode(file_get_contents($pdfPath)));
        $boundary = md5(time());
        
        $headers .= "\r\nMIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";
        
        $body = "--" . $boundary . "\r\n";
        $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($message));
        
        $body .= "--" . $boundary . "\r\n";
        $body .= "Content-Type: application/pdf; name=\"EMI_Report_" . date('F_Y') . ".pdf\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"EMI_Report_" . date('F_Y') . ".pdf\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= $attachment;
        $body .= "--" . $boundary . "--";
        
        mail($to, $subject, $body, $headers);
    }
}

try {
    $conn = $con;
    
    // Start transaction
    $conn->begin_transaction();
    
    // Run automation tasks
    updateInstallmentStatus();
    sendUpcomingPaymentReminders();
    checkDefaultedPlans();
    
    // Generate monthly report on first day of month
    if (date('j') === '1') {
        generateMonthlyReport();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "EMI automation tasks completed successfully.\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo "Error running EMI automation tasks: " . $e->getMessage() . "\n";
    
    // Log error
    error_log("EMI Automation Error: " . $e->getMessage());
}
?>
