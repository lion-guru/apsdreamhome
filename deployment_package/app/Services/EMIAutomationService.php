<?php

namespace App\Services;

use App\Core\Database;
use App\Services\NotificationService;
use Exception;
use TCPDF;

class EMIAutomationService
{
    protected $db;
    protected $notificationService;
    protected $rootPath;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->notificationService = new NotificationService();
        $this->rootPath = dirname(__DIR__, 2);
    }

    /**
     * Run all automation tasks
     */
    public function runAll()
    {
        $results = [
            'status_update' => false,
            'reminders' => false,
            'defaults' => false,
            'report' => false
        ];

        try {
            $this->db->beginTransaction();

            $results['status_update'] = $this->updateInstallmentStatus();
            $results['reminders'] = $this->sendUpcomingPaymentReminders();
            $results['defaults'] = $this->checkDefaultedPlans();

            // Generate monthly report on first day of month
            if (date('j') === '1') {
                $results['report'] = $this->generateMonthlyReport();
            }

            $this->db->commit();
            return $results;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("EMI Automation Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update status of overdue installments
     */
    public function updateInstallmentStatus()
    {
        // Get all pending installments that are overdue
        $query = "UPDATE emi_installments 
                  SET status = 'overdue',
                      updated_at = NOW()
                  WHERE status = 'pending' 
                  AND due_date < CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        // Get overdue installments for notification
        $query = "SELECT ei.*, ep.customer_id, c.name as customer_name, c.email as customer_email, p.title as property_title
                  FROM emi_installments ei
                  JOIN emi_plans ep ON ei.emi_plan_id = ep.id
                  JOIN customers c ON ep.customer_id = c.id
                  JOIN properties p ON ep.property_id = p.id
                  WHERE ei.status = 'overdue'
                  AND (ei.last_reminder_date IS NULL 
                  OR ei.last_reminder_date < DATE_SUB(NOW(), INTERVAL 3 DAY))";

        $stmt = $this->db->query($query);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $subject = "Overdue EMI Payment Alert - " . $row['property_title'];
            $body = $this->renderTemplate('EMI_OVERDUE', [
                'customer_name' => $row['customer_name'],
                'amount' => number_format($row['amount'], 2),
                'property_title' => $row['property_title'],
                'due_date' => date('d M Y', strtotime($row['due_date']))
            ]);

            // Send email
            if (!empty($row['customer_email'])) {
                $this->notificationService->sendEmail(
                    $row['customer_email'],
                    $subject,
                    $body,
                    'emi_overdue',
                    $row['customer_id'],
                    ['emi_plan_id' => $row['emi_plan_id']]
                );
            }

            // Update last reminder date
            // Note: Ensure last_reminder_date column exists in emi_installments
            try {
                $updateQuery = "UPDATE emi_installments 
                               SET last_reminder_date = NOW()
                               WHERE id = ?";
                $this->db->prepare($updateQuery)->execute([$row['id']]);
            } catch (\Exception $e) {
                // Column might not exist, ignore
            }
        }

        return true;
    }

    /**
     * Send reminders for upcoming payments
     */
    public function sendUpcomingPaymentReminders()
    {
        // Get installments due in next 3 days
        $query = "SELECT ei.*, ep.customer_id, c.name as customer_name, 
                         c.email as customer_email, p.title as property_title
                  FROM emi_installments ei
                  JOIN emi_plans ep ON ei.emi_plan_id = ep.id
                  JOIN customers c ON ep.customer_id = c.id
                  JOIN properties p ON ep.property_id = p.id
                  WHERE ei.status = 'pending'
                  AND ei.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                  AND (ei.reminder_sent IS NULL OR ei.reminder_sent = 0)";

        $stmt = $this->db->query($query);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $subject = "Upcoming EMI Payment Reminder - " . $row['property_title'];
            $body = $this->renderTemplate('EMI_REMINDER', [
                'customer_name' => $row['customer_name'],
                'amount' => number_format($row['amount'], 2),
                'property_title' => $row['property_title'],
                'due_date' => date('d M Y', strtotime($row['due_date']))
            ]);

            // Send email
            if (!empty($row['customer_email'])) {
                $this->notificationService->sendEmail(
                    $row['customer_email'],
                    $subject,
                    $body,
                    'emi_reminder',
                    $row['customer_id'],
                    ['emi_plan_id' => $row['emi_plan_id']]
                );
            }

            // Mark reminder as sent
            // Note: Ensure reminder_sent column exists
            try {
                $updateQuery = "UPDATE emi_installments 
                               SET reminder_sent = 1
                               WHERE id = ?";
                $this->db->prepare($updateQuery)->execute([$row['id']]);
            } catch (\Exception $e) {
                // Column might not exist
            }
        }

        return true;
    }

    /**
     * Check for defaulted plans (3+ consecutive overdue)
     */
    public function checkDefaultedPlans()
    {
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
                  AND ei.status = 'overdue'
                  GROUP BY ep.id
                  HAVING overdue_count >= 3";

        $stmt = $this->db->query($query);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            // Update plan status to defaulted
            $updateQuery = "UPDATE emi_plans 
                           SET status = 'defaulted',
                               updated_at = NOW()
                           WHERE id = ?";
            $this->db->prepare($updateQuery)->execute([$row['id']]);

            // Notify Admins
            $adminQuery = "SELECT id, email, name FROM admin WHERE role = 'admin'";
            $admins = $this->db->query($adminQuery)->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($admins as $admin) {
                if (!empty($admin['email'])) {
                    $subject = "EMI Default Alert: " . $row['customer_name'];
                    $body = $this->renderTemplate('EMI_DEFAULT_ADMIN', [
                        'admin_name' => $admin['name'],
                        'customer_name' => $row['customer_name'],
                        'property_title' => $row['property_title'],
                        'overdue_count' => $row['overdue_count']
                    ]);

                    $this->notificationService->sendEmail(
                        $admin['email'],
                        $subject,
                        $body,
                        'emi_default_admin',
                        $admin['id'],
                        ['plan_id' => $row['id']]
                    );
                }
            }

            // Notify Customer
            if (!empty($row['customer_email'])) {
                $subject = "Important: EMI Plan Defaulted - " . $row['property_title'];
                $body = $this->renderTemplate('EMI_DEFAULT_CUSTOMER', [
                    'customer_name' => $row['customer_name'],
                    'property_title' => $row['property_title'],
                    'overdue_count' => $row['overdue_count']
                ]);

                $this->notificationService->sendEmail(
                    $row['customer_email'],
                    $subject,
                    $body,
                    'emi_default',
                    $row['customer_id'],
                    ['plan_id' => $row['id']]
                );
            }
        }

        return true;
    }

    /**
     * Generate monthly collection report
     */
    public function generateMonthlyReport()
    {
        $month = date('m');
        $year = date('Y');

        // Get monthly collection stats
        $query = "SELECT 
                    COUNT(DISTINCT ep.id) as total_active_plans,
                    COUNT(DISTINCT CASE WHEN ei.status = 'paid' THEN ei.id END) as paid_installments,
                    COUNT(DISTINCT CASE WHEN ei.status = 'overdue' THEN ei.id END) as overdue_installments,
                    SUM(CASE WHEN ei.status = 'paid' THEN ei.amount ELSE 0 END) as total_collected,
                    SUM(CASE WHEN ei.status = 'overdue' THEN ei.amount ELSE 0 END) as total_overdue
                  FROM emi_plans ep
                  LEFT JOIN emi_installments ei ON ep.id = ei.emi_plan_id
                  WHERE MONTH(ei.due_date) = ? AND YEAR(ei.due_date) = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$month, $year]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Generate PDF
        $pdfPath = $this->generateReportPDF($stats, $month, $year);

        if (!$pdfPath) {
            return false;
        }

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

        $this->db->prepare($query)->execute([$reportTitle, $reportContent, $pdfPath, $month, $year]);
        $reportId = $this->db->lastInsertId();

        // Send report to admins
        $adminQuery = "SELECT id, email, name FROM admin WHERE role = 'admin'";
        $admins = $this->db->query($adminQuery)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($admins as $admin) {
            if (!empty($admin['email'])) {
                $subject = "Monthly EMI Collection Report - " . date('F Y');
                $body = $this->renderTemplate('EMI_MONTHLY_REPORT', [
                    'admin_name' => $admin['name'],
                    'report_month' => date('F Y')
                ]);

                // Note: NotificationService might not support attachments yet, 
                // but we pass the path in payload for logging/future use
                $this->notificationService->sendEmail(
                    $admin['email'],
                    $subject,
                    $body . "\n\nReport available at: " . $pdfPath, // Fallback if no attachment support
                    'emi_report',
                    $admin['id'],
                    ['report_id' => $reportId, 'file_path' => $pdfPath]
                );
            }
        }

        return true;
    }

    private function generateReportPDF($stats, $month, $year)
    {
        // Try to load TCPDF
        if (!class_exists('TCPDF')) {
            $tcpdfPath = $this->rootPath . '/vendor/tecnickcom/tcpdf/tcpdf.php';
            if (!file_exists($tcpdfPath)) {
                // Fallback to library folder
                $tcpdfPath = $this->rootPath . '/app/Core/TCPDF/tcpdf.php';
            }

            if (file_exists($tcpdfPath)) {
                require_once $tcpdfPath;
            } else {
                error_log("TCPDF not found. Cannot generate PDF report.");
                return false;
            }
        }

        // Define PDF constants if not already defined
        if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION', 'P');
        if (!defined('PDF_UNIT')) define('PDF_UNIT', 'mm');
        if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT', 'A4');

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('APS Dream Home');
        $pdf->SetAuthor('APS Dream Home');
        $pdf->SetTitle('EMI Collection Report - ' . date('F Y'));

        $pdf->AddPage();

        // Add company logo if exists
        $logoPath = $this->rootPath . '/assets/img/logo.png';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 15, 50);
        }

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
        $pdf->Cell(0, 8, 'INR ' . number_format($stats['total_collected'] ?? 0, 2), 0, 1);
        $pdf->Cell(100, 8, 'Total Amount Overdue:', 0);
        $pdf->Cell(0, 8, 'INR ' . number_format($stats['total_overdue'] ?? 0, 2), 0, 1);

        // Save PDF
        $reportsDir = $this->rootPath . '/reports';
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0755, true);
        }

        $fileName = 'emi_report_' . date('Y_m') . '.pdf';
        $pdfPath = $reportsDir . '/' . $fileName;
        $pdf->Output($pdfPath, 'F');

        return 'reports/' . $fileName; // Return relative path
    }

    private function renderTemplate($key, $data)
    {
        $templates = [
            'EMI_OVERDUE' => "Dear {customer_name},\n\nYour EMI of INR {amount} for {property_title} was due on {due_date}. Please pay immediately to avoid penalties.\n\nRegards,\nAPS Dream Home",
            'EMI_REMINDER' => "Dear {customer_name},\n\nThis is a reminder that your EMI of INR {amount} for {property_title} is due on {due_date}.\n\nRegards,\nAPS Dream Home",
            'EMI_DEFAULT_ADMIN' => "Admin {admin_name},\n\nCustomer {customer_name} has defaulted on their EMI plan for {property_title}. Overdue installments: {overdue_count}.\n\nPlease review the account.",
            'EMI_DEFAULT_CUSTOMER' => "Dear {customer_name},\n\nYour EMI plan for {property_title} has been marked as defaulted due to {overdue_count} missed payments.\n\nPlease contact support immediately.",
            'EMI_MONTHLY_REPORT' => "Hello {admin_name},\n\nThe EMI Collection Report for {report_month} has been generated.\n\nRegards,\nAPS Dream Home"
        ];

        $template = $templates[$key] ?? '';

        foreach ($data as $k => $v) {
            $template = str_replace('{' . $k . '}', $v, $template);
        }

        return $template;
    }
}
