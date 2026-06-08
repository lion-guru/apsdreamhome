<?php
namespace App\Http\Controllers\Admin;

use App\Services\Filing\EFilingService;
use App\Services\Filing\TDSFilingService;
use App\Services\Filing\GSTFilingService;

/**
 * EFilingController — TDS/GST e-filing admin dashboard
 */
class EFilingController extends AdminController
{
    private $efiling;
    private $tdsFiling;
    private $gstFiling;

    public function __construct()
    {
        parent::__construct();
        $this->efiling = new EFilingService();
        $this->tdsFiling = new TDSFilingService();
        $this->gstFiling = new GSTFilingService();
    }

    // ========== Dashboard ==========

    public function index()
    {
        $this->requireAdmin();
        $data = $this->efiling->getDashboardData();
        $data['page_title'] = 'E-Filing Dashboard';
        $data['fy'] = $this->efiling->getCurrentFinancialYear();
        $data['quarter'] = $this->efiling->getCurrentQuarter();
        $this->render('admin/efiling/dashboard', $data);
    }

    // ========== TDS Filing ==========

    public function tdsRegister()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->efiling->getCurrentFinancialYear();
        $quarter = $_GET['quarter'] ?? $this->efiling->getCurrentQuarter();
        $summary = $this->tdsFiling->getTDSSummary($fy, $quarter);
        $challans = $this->tdsFiling->listChallans(['financial_year' => $fy, 'quarter' => $quarter]);
        $submissions = $this->efiling->listSubmissions([
            'submission_type' => 'tds_return',
            'financial_year' => $fy,
            'quarter' => $quarter,
            'limit' => 20,
        ]);

        $this->render('admin/efiling/tds', [
            'page_title' => 'TDS E-Filing',
            'fy' => $fy,
            'quarter' => $quarter,
            'fy_list' => $this->getFyList(),
            'summary' => $summary,
            'challans' => $challans,
            'submissions' => $submissions,
            'rates' => $this->tdsFiling->getTDSRates(),
            'current_fy' => $this->efiling->getCurrentFinancialYear(),
            'current_quarter' => $this->efiling->getCurrentQuarter(),
        ]);
    }

    public function generateTdsReturn()
    {
        $this->requireAdmin();
        $fy = $_POST['fy'] ?? $this->efiling->getCurrentFinancialYear();
        $quarter = $_POST['quarter'] ?? $this->efiling->getCurrentQuarter();

        $result = $this->tdsFiling->generateForm26Q($fy, $quarter);
        if ($result['success']) {
            $_SESSION['flash_success'] = "Form 26Q generated: {$result['summary']['total_records']} records, TDS ₹" .
                number_format($result['summary']['total_tds'], 2) . ". File: {$result['filename']}";
        } else {
            $_SESSION['flash_error'] = "Error: " . ($result['error'] ?? 'Unknown error');
        }
        redirect('/admin/efiling/tds?fy=' . $fy . '&quarter=' . $quarter);
    }

    public function tdsChallans()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->efiling->getCurrentFinancialYear();
        $challans = $this->tdsFiling->listChallans(['financial_year' => $fy]);

        $this->render('admin/efiling/tds-challans', [
            'page_title' => 'TDS Challan 281',
            'fy' => $fy,
            'fy_list' => $this->getFyList(),
            'challans' => $challans,
        ]);
    }

    public function createChallan()
    {
        $this->requireAdmin();
        $this->render('admin/efiling/challan-form', [
            'page_title' => 'Create Challan 281',
            'fy' => $this->efiling->getCurrentFinancialYear(),
            'quarter' => $this->efiling->getCurrentQuarter(),
            'fy_list' => $this->getFyList(),
            'sections' => $this->tdsFiling->getTDSRates(),
        ]);
    }

    public function storeChallan()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail($_POST['csrf_token'] ?? '');

        $challanId = $this->tdsFiling->generateChallan281([
            'tan' => $_POST['tan'] ?? '',
            'assessment_year' => $this->tdsFiling->getAssessmentYear($_POST['financial_year'] ?? ''),
            'financial_year' => $_POST['financial_year'] ?? '',
            'quarter' => $_POST['quarter'] ?? '',
            'deposit_date' => $_POST['deposit_date'] ?? date('Y-m-d'),
            'tds_section' => $_POST['tds_section'] ?? null,
            'total_amount' => (float)($_POST['total_amount'] ?? 0),
            'interest_amount' => (float)($_POST['interest_amount'] ?? 0),
            'penalty_amount' => (float)($_POST['penalty_amount'] ?? 0),
            'deposited_via' => $_POST['deposited_via'] ?? 'net_banking',
            'bank_name' => $_POST['bank_name'] ?? null,
            'remarks' => $_POST['remarks'] ?? null,
        ]);

        $_SESSION['flash_success'] = "Challan 281 created (ID: {$challanId})";
        redirect('/admin/efiling/tds-challans');
    }

    public function challanDetail()
    {
        $this->requireAdmin();
        $id = $this->getRouteParam('id') ?? $_GET['id'] ?? null;
        if (!$id) { redirect('/admin/efiling/tds-challans'); return; }
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM tds_challans WHERE id = ?");
        $stmt->execute([$id]);
        $challan = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$challan) { redirect('/admin/efiling/tds-challans'); return; }

        $this->render('admin/efiling/challan-detail', [
            'page_title' => 'Challan #' . $challan['id'],
            'challan' => $challan,
        ]);
    }

    public function tdsCertificates()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->efiling->getCurrentFinancialYear();
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM tds_certificates_issued WHERE financial_year = ? ORDER BY quarter, deductee_name");
        $stmt->execute([$fy]);
        $certs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/efiling/tds-certificates', [
            'page_title' => 'Form 16A Certificates',
            'fy' => $fy,
            'fy_list' => $this->getFyList(),
            'certificates' => $certs,
        ]);
    }

    // ========== GST Filing ==========

    public function gstFiling()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->efiling->getCurrentFinancialYear();
        $month = $_GET['month'] ?? (int)date('m');
        $year = $_GET['year'] ?? (int)date('Y');
        $summary = $this->gstFiling->getGSTSummary($fy);
        $submissions = $this->efiling->listSubmissions([
            'submission_type' => ['gstr1', 'gstr3b', 'gstr9'],
            'financial_year' => $fy,
            'limit' => 20,
        ]);

        $this->render('admin/efiling/gst', [
            'page_title' => 'GST E-Filing',
            'fy' => $fy,
            'month' => $month,
            'year' => $year,
            'fy_list' => $this->getFyList(),
            'months' => $this->getMonthList(),
            'summary' => $summary,
            'submissions' => $submissions,
        ]);
    }

    public function generateGstr1()
    {
        $this->requireAdmin();
        $month = (int)($_POST['month'] ?? date('m'));
        $year = (int)($_POST['year'] ?? date('Y'));
        $fy = $_POST['fy'] ?? $this->efiling->getCurrentFinancialYear();

        $result = $this->gstFiling->generateGSTR1($month, $year, $fy);
        if ($result['success']) {
            $_SESSION['flash_success'] = "GSTR-1 generated: {$result['summary']['b2b_invoices']} B2B + {$result['summary']['b2c_count']} B2C invoices. File: {$result['filename']}";
        } else {
            $_SESSION['flash_error'] = "Error: " . ($result['error'] ?? 'Unknown error');
        }
        redirect("/admin/efiling/gst?fy={$fy}&month={$month}&year={$year}");
    }

    public function generateGstr3b()
    {
        $this->requireAdmin();
        $month = (int)($_POST['month'] ?? date('m'));
        $year = (int)($_POST['year'] ?? date('Y'));
        $fy = $_POST['fy'] ?? $this->efiling->getCurrentFinancialYear();

        $result = $this->gstFiling->generateGSTR3B($month, $year, $fy);
        if ($result['success']) {
            $_SESSION['flash_success'] = "GSTR-3B generated: Output ₹" .
                number_format($result['summary']['output_tax'], 2) .
                ", ITC ₹" . number_format($result['summary']['input_tax'], 2) .
                ", Net ₹" . number_format($result['summary']['net_payable'], 2);
        } else {
            $_SESSION['flash_error'] = "Error: " . ($result['error'] ?? 'Unknown error');
        }
        redirect("/admin/efiling/gst?fy={$fy}&month={$month}&year={$year}");
    }

    // ========== Calendar ==========

    public function calendar()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->efiling->getCurrentFinancialYear();
        $type = $_GET['type'] ?? null;
        $deadlines = $this->efiling->getAllDeadlines($fy, $type);
        $overdue = $this->efiling->getOverdueDeadlines();

        $this->render('admin/efiling/calendar', [
            'page_title' => 'Filing Calendar',
            'fy' => $fy,
            'type' => $type,
            'fy_list' => $this->getFyList(),
            'deadlines' => $deadlines,
            'overdue' => $overdue,
        ]);
    }

    // ========== Submissions List ==========

    public function submissions()
    {
        $this->requireAdmin();
        $type = $_GET['type'] ?? null;
        $status = $_GET['status'] ?? null;
        $fy = $_GET['fy'] ?? null;
        $submissions = $this->efiling->listSubmissions([
            'submission_type' => $type,
            'status' => $status,
            'financial_year' => $fy,
            'limit' => 100,
        ]);

        $this->render('admin/efiling/submissions', [
            'page_title' => 'Filing Submissions',
            'type' => $type,
            'status' => $status,
            'fy' => $fy,
            'submissions' => $submissions,
            'fy_list' => $this->getFyList(),
        ]);
    }

    public function showSubmission(int $id)
    {
        $this->requireAdmin();
        $submission = $this->efiling->getSubmission($id);
        if (!$submission) {
            $_SESSION['flash_error'] = 'Submission not found';
            redirect('/admin/efiling/submissions');
            return;
        }

        $jsonContent = null;
        if ($submission['json_file_path'] && file_exists($submission['json_file_path'])) {
            $jsonContent = json_decode(file_get_contents($submission['json_file_path']), true);
        }

        $this->render('admin/efiling/submission-detail', [
            'page_title' => 'Submission Detail',
            'submission' => $submission,
            'json_content' => $jsonContent,
        ]);
    }

    public function updateSubmissionStatus()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['submission_id'] ?? 0);
        $status = $_POST['status'] ?? 'draft';

        $extra = [];
        if (!empty($_POST['portal_reference'])) $extra['portal_reference'] = $_POST['portal_reference'];
        if (!empty($_POST['remarks'])) $extra['error_message'] = $_POST['remarks'];

        $this->efiling->updateSubmissionStatus($id, $status, $extra);
        $_SESSION['flash_success'] = "Submission status updated to: {$status}";
        redirect("/admin/efiling/submissions/{$id}");
    }

    // ========== Helpers ==========

    private function getPdo(): \PDO
    {
        $config = require 'C:/xampp/htdocs/apsdreamhome/config/database.php';
        return new \PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
            $config['username'], $config['password'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }

    private function getFyList(): array
    {
        $currentYear = (int)date('Y');
        $list = [];
        for ($y = $currentYear; $y >= $currentYear - 2; $y--) {
            $fy = $y . '-' . substr($y + 1, -2);
            $list[$fy] = "FY {$fy} (AY " . ($y + 1) . '-' . substr($y + 2, -2) . ")";
        }
        return $list;
    }

    private function getMonthList(): array
    {
        return [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
    }
}
