<?php
namespace App\Http\Controllers\Admin;

use App\Services\Filing\EFilingService;
use App\Services\Filing\TDSFilingService;
use App\Services\Filing\GSTFilingService;
use App\Services\Filing\GSTNApiService;
use App\Services\Filing\TINApiService;

/**
 * EFilingController —" TDS/GST e-filing admin dashboard
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

    // ========== Downloads / Exports ==========

    public function downloadForm16A()
    {
        $this->requireAdmin();
        $id = (int)($this->getRouteParam('id') ?? $_GET['id'] ?? 0);
        if (!$id) { redirect('/admin/efiling/tds/certificates'); return; }

        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM tds_certificates_issued WHERE id = ?");
        $stmt->execute([$id]);
        $cert = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$cert) {
            $_SESSION['flash_error'] = 'Certificate not found';
            redirect('/admin/efiling/tds/certificates');
            return;
        }

        // Get TDS records for this deductee
        $tdsStmt = $pdo->prepare("SELECT * FROM tds_register
            WHERE deductee_pan = ? AND financial_year = ? AND quarter = ?
            ORDER BY transaction_date ASC");
        $tdsStmt->execute([$cert['deductee_pan'], $cert['financial_year'], $cert['quarter']]);
        $tdsRecords = $tdsStmt->fetchAll(\PDO::FETCH_ASSOC);

        $tan = '';
        try {
            $tanStmt = $pdo->prepare("SELECT credential_value FROM company_credentials
                WHERE credential_type = 'tan' AND status = 'active' AND is_primary = 1 LIMIT 1");
            $tanStmt->execute();
            $tanRow = $tanStmt->fetch(\PDO::FETCH_ASSOC);
            $tan = $tanRow['credential_value'] ?? '';
        } catch (\Exception $e) { /* fallback */ error_log($e->getMessage()); }

        $deductorName = htmlspecialchars($tdsRecords[0]['deductor_name'] ?? 'APS Dream Home');
        $deductorPan = htmlspecialchars($tdsRecords[0]['deductor_pan'] ?? '');
        $deducteeName = htmlspecialchars($cert['deductee_name'] ?? '');
        $deducteePan = htmlspecialchars($cert['deductee_pan'] ?? '');
        $fy = htmlspecialchars($cert['financial_year'] ?? '');
        $quarter = htmlspecialchars($cert['quarter'] ?? '');
        $certNo = htmlspecialchars($cert['certificate_number'] ?? '');
        $totalTds = number_format((float)($cert['total_tds'] ?? 0), 2);
        $issuedDate = $cert['issued_date'] ? date('d-m-Y', strtotime($cert['issued_date'])) : date('d-m-Y');
        $ayStart = (int)substr($fy, 0, 4) + 1;
        $ay = $ayStart . '-' . substr($ayStart + 1, -2);

        $rows = '';
        foreach ($tdsRecords as $i => $r) {
            $rows .= '<tr>
                <td>' . ($i + 1) . '</td>
                <td>' . htmlspecialchars($r['transaction_date'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['tds_section'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['description'] ?? $r['nature_of_payment'] ?? '-') . '</td>
                <td class="text-right">₹' . number_format((float)$r['gross_amount'], 2) . '</td>
                <td class="text-right">' . (float)$r['tds_rate'] . '%</td>
                <td class="text-right">₹' . number_format((float)$r['tds_amount'], 2) . '</td>
                <td class="text-right">₹' . number_format((float)($r['surcharge'] ?? 0), 2) . '</td>
                <td class="text-right">₹' . number_format((float)($r['cess'] ?? 0), 2) . '</td>
            </tr>';
        }

        $html = '<!DOCTYPE html>
<html><head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="UTF-8">
<title>Form 16A - Certificate for Tax Deducted at Source</title>
<style>
body { font-family: Arial, sans-serif; font-size: 13px; margin: 30px; color: #333; }
h1 { text-align: center; font-size: 18px; margin-bottom: 5px; }
h2 { text-align: center; font-size: 14px; font-weight: normal; margin-top: 0; color: #555; }
.header-box { border: 2px solid #333; padding: 12px 20px; margin-bottom: 20px; }
.header-box h1 { margin: 0; }
.header-box h2 { margin: 5px 0 0 0; }
.meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 12px; }
.meta div { flex: 1; }
.meta strong { display: inline-block; width: 140px; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; font-size: 12px; }
th { background: #f0f0f0; font-weight: bold; }
.text-right { text-align: right; }
.total-row td { font-weight: bold; background: #f5f5f5; }
.footer { margin-top: 30px; font-size: 11px; }
.footer .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
.footer .sig-block { text-align: center; width: 200px; }
.footer .sig-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; }
.stamp { text-align: right; margin-top: 20px; font-size: 11px; color: #666; }
</style></head><body>

<div class="header-box">
    <h1>FORM 16A</h1>
    <h2>Certificate for Tax Deducted at Source under Section 203 of the Income Tax Act, 1961</h2>
</div>

<div class="meta">
    <div><strong>Certificate No:</strong> ' . $certNo . '</div>
    <div><strong>Issue Date:</strong> ' . $issuedDate . '</div>
</div>

<div class="meta">
    <div><strong>Financial Year:</strong> ' . $fy . '</div>
    <div><strong>Assessment Year:</strong> ' . $ay . '</div>
    <div><strong>Quarter:</strong> ' . $quarter . '</div>
</div>

<hr class="style-18469">

<div class="meta">
    <div>
        <strong>Deductor (TDS Payer):</strong><br>
        Name: ' . $deductorName . '<br>
        PAN: ' . $deductorPan . '<br>
        TAN: ' . htmlspecialchars($tan) . '
    </div>
    <div>
        <strong>Deductee (TDS Receiver):</strong><br>
        Name: ' . $deducteeName . '<br>
        PAN: ' . $deducteePan . '
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th><th>Date</th><th>Section</th><th>Nature of Payment</th>
            <th class="text-right">Gross Amount</th><th class="text-right">TDS Rate</th>
            <th class="text-right">TDS Amount</th><th class="text-right">Surcharge</th>
            <th class="text-right">Health & Edu Cess</th>
        </tr>
    </thead>
    <tbody>
        ' . ($rows ?: '<tr><td colspan="9" class="style-58107">No TDS records found</td></tr>') . '
        <tr class="total-row">
            <td colspan="4">Total</td>
            <td class="text-right">₹' . number_format(array_sum(array_column($tdsRecords, 'gross_amount')), 2) . '</td>
            <td></td>
            <td class="text-right">₹' . $totalTds . '</td>
            <td class="text-right">₹' . number_format(array_sum(array_column($tdsRecords, 'surcharge')), 2) . '</td>
            <td class="text-right">₹' . number_format(array_sum(array_column($tdsRecords, 'cess')), 2) . '</td>
        </tr>
    </tbody>
</table>

<div class="stamp">
    I/We hereby certify that the tax has been deducted at source and deposited to the credit of the Central Government.
</div>

<div class="footer">
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-line">Deductor\'s Signature & Seal</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Place & Date</div>
        </div>
    </div>
</div>

</body></html>';

        $filename = "Form16A_{$cert['deductee_pan']}_{$fy}_{$quarter}.html";
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        echo $html;
        exit;
    }

    public function exportGstr1()
    {
        $this->requireAdmin();
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));
        $fy = $_GET['fy'] ?? $this->efiling->getCurrentFinancialYear();

        $result = $this->gstFiling->generateGSTR1($month, $year, $fy);
        if (!$result['success']) {
            $_SESSION['flash_error'] = "Export failed: " . ($result['error'] ?? 'No data found');
            redirect('/admin/efiling/gst?fy=' . urlencode($fy));
            return;
        }

        $period = $this->gstFiling->getReturnPeriod($month, $year);
        $filename = "GSTR1_{$period}_" . date('Ymd') . '.json';
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($result['gstr1'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function exportGstr3b()
    {
        $this->requireAdmin();
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));
        $fy = $_GET['fy'] ?? $this->efiling->getCurrentFinancialYear();

        $result = $this->gstFiling->generateGSTR3B($month, $year, $fy);
        if (!$result['success']) {
            $_SESSION['flash_error'] = "Export failed: " . ($result['error'] ?? 'No data found');
            redirect('/admin/efiling/gst?fy=' . urlencode($fy));
            return;
        }

        $period = $this->gstFiling->getReturnPeriod($month, $year);
        $filename = "GSTR3B_{$period}_" . date('Ymd') . '.json';
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($result['gstr3b'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ========== GSTN Portal ==========

    public function gstnPortal()
    {
        $this->requireAdmin();
        $gstn = new GSTNApiService();
        $tin = new TINApiService();
        $fy = $this->efiling->getCurrentFinancialYear();
        $submissions = $this->efiling->listSubmissions([
            'submission_type' => ['gstr1', 'gstr3b'],
            'financial_year' => $fy,
            'limit' => 20,
        ]);

        $this->render('admin/efiling/gstn-portal', [
            'page_title' => 'GSTN Portal',
            'gstn_status' => $gstn->getConnectionStatus(),
            'tin_status' => $tin->getConnectionStatus(),
            'fy' => $fy,
            'fy_list' => $this->getFyList(),
            'months' => $this->getMonthList(),
            'submissions' => $submissions,
        ]);
    }

    public function submitGstn()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail($_POST['csrf_token'] ?? '');

        $returnType = $_POST['return_type'] ?? 'gstr1';
        $month = (int)($_POST['month'] ?? date('m'));
        $year = (int)($_POST['year'] ?? date('Y'));
        $fy = $_POST['fy'] ?? $this->efiling->getCurrentFinancialYear();
        $period = sprintf('%02d%04d', $month, $year);

        $gstn = new GSTNApiService();

        // Generate JSON data first
        if ($returnType === 'gstr1') {
            $genResult = $this->gstFiling->generateGSTR1($month, $year, $fy);
            $data = $genResult['gstr1'] ?? [];
        } else {
            $genResult = $this->gstFiling->generateGSTR3B($month, $year, $fy);
            $data = $genResult['gstr3b'] ?? [];
        }

        if (!isset($data['gstin'])) {
            $data['fp'] = $period;
        }

        // Submit via API
        $result = $returnType === 'gstr1'
            ? $gstn->submitGstr1($data)
            : $gstn->submitGstr3b($data);

        if (!empty($result['success'])) {
            $mode = $gstn->isTestMode() ? ' [TEST MODE]' : '';
            $_SESSION['flash_success'] = ucfirst($returnType) . " submitted{$mode}: " .
                ($result['reference_number'] ?? $result['acknowledgment_number'] ?? 'OK');

            // Update submission status
            if (!empty($genResult['submission_id'])) {
                $this->efiling->updateSubmissionStatus($genResult['submission_id'], 'submitted', [
                    'portal_reference' => $result['reference_number'] ?? $result['acknowledgment_number'] ?? null,
                    'portal_response_json' => json_encode($result),
                ]);
            }
        } else {
            $_SESSION['flash_error'] = "Submission failed: " . ($result['error'] ?? 'Unknown error');
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function gstnStatus()
    {
        $this->requireAdmin();
        $gstin = $this->getRouteParam('gstin') ?? $_GET['gstin'] ?? '';
        $period = $_GET['period'] ?? sprintf('%02d%04d', (int)date('m'), (int)date('Y'));

        $gstn = new GSTNApiService();
        $result = $gstn->getStatus($gstin, $period);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // ========== TIN Portal ==========

    public function tinPortal()
    {
        $this->requireAdmin();
        $tin = new TINApiService();
        $fy = $this->efiling->getCurrentFinancialYear();
        $quarter = $this->efiling->getCurrentQuarter();
        $submissions = $this->efiling->listSubmissions([
            'submission_type' => 'tds_return',
            'financial_year' => $fy,
            'limit' => 20,
        ]);

        $this->render('admin/efiling/tin-portal', [
            'page_title' => 'TIN Portal',
            'tin_status' => $tin->getConnectionStatus(),
            'fy' => $fy,
            'quarter' => $quarter,
            'fy_list' => $this->getFyList(),
            'submissions' => $submissions,
        ]);
    }

    public function submitTin()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail($_POST['csrf_token'] ?? '');

        $formType = $_POST['form_type'] ?? '26Q';
        $fy = $_POST['fy'] ?? $this->efiling->getCurrentFinancialYear();
        $quarter = $_POST['quarter'] ?? $this->efiling->getCurrentQuarter();

        $tin = new TINApiService();

        // Generate Form 26Q data first
        $genResult = $this->tdsFiling->generateForm26Q($fy, $quarter);
        if (!$genResult['success']) {
            $_SESSION['flash_error'] = "Error: " . ($genResult['error'] ?? 'No TDS records found');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $genResult['error'] ?? 'No data']);
            exit;
        }

        $formData = $genResult['form_26q'] ?? [];

        // Submit via API
        $result = $formType === '27Q'
            ? $tin->submitForm27Q($formData)
            : $tin->submitForm26Q($formData);

        if (!empty($result['success'])) {
            $mode = $tin->isTestMode() ? ' [TEST MODE]' : '';
            $_SESSION['flash_success'] = "Form {$formType} submitted{$mode}: " .
                ($result['data']['acknowledgment_number'] ?? $result['data']['token_number'] ?? 'OK');

            if (!empty($genResult['submission_id'])) {
                $this->efiling->updateSubmissionStatus($genResult['submission_id'], 'submitted', [
                    'portal_reference' => $result['data']['token_number'] ?? $result['data']['acknowledgment_number'] ?? null,
                    'portal_response_json' => json_encode($result),
                ]);
            }
        } else {
            $_SESSION['flash_error'] = "Submission failed: " . ($result['error'] ?? 'Unknown error');
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function tinStatus()
    {
        $this->requireAdmin();
        $tokenNumber = $this->getRouteParam('token') ?? $_GET['token'] ?? '';

        $tin = new TINApiService();
        $result = $tin->getStatus($tokenNumber);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // ========== Helpers ==========

    private function getPdo(): \PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
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
