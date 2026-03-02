<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use TCPDF;

/**
 * EMI Controller
 * Handles EMI plans management in the Admin panel
 */
class EMIController extends AdminController
{
    protected $emiModel;

    public function __construct()
    {
        parent::__construct();
        $this->emiModel = $this->model('EMI');
    }

    /**
     * Show EMI plans list
     */
    public function index()
    {
        $params = [
            'start' => 0,
            'length' => 100, // Show more on main page
            'orderBy' => 'ep.created_at',
            'orderDir' => 'DESC'
        ];

        $result = $this->emiModel->getFilteredPlans($params);

        $this->render('admin/emi/index', [
            'plans' => $result['data'],
            'page_title' => $this->mlSupport->translate('EMI Plans')
        ]);
    }

    /**
     * Get EMI statistics for dashboard
     */
    public function stats()
    {
        try {
            $stats = $this->emiModel->getStats();
            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get EMI plans for DataTables AJAX
     */
    public function list()
    {
        $request = $this->request;
        $search = $request->post('search');
        $order = $request->post('order');

        $params = [
            'draw' => (int)$request->post('draw', 1),
            'start' => (int)$request->post('start', 0),
            'length' => (int)$request->post('length', 10),
            'search' => $search['value'] ?? '',
            'orderColumn' => (int)($order[0]['column'] ?? 0),
            'orderDir' => $order[0]['dir'] ?? 'DESC'
        ];

        // Column mapping for DataTables
        $columns = ['c.name', 'p.title', 'ep.total_amount', 'ep.emi_amount', 'ep.tenure_months', 'ep.start_date', 'ep.status'];
        if (isset($columns[$params['orderColumn']])) {
            $params['orderBy'] = $columns[$params['orderColumn']];
        } else {
            $params['orderBy'] = 'ep.id';
        }
        $params['orderDir'] = $params['orderDir']; // Use variable directly

        try {
            $result = $this->emiModel->getFilteredPlans($params);

            return $this->jsonResponse([
                'draw' => $params['draw'],
                'recordsTotal' => $result['totalRecords'],
                'recordsFiltered' => $result['filteredRecords'],
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Show create plan form
     */
    public function create()
    {
        // Get customers for dropdown
        $customers = $this->db->fetchAll("SELECT id, name, phone FROM customers ORDER BY name ASC");

        // Get properties for dropdown
        $properties = $this->db->fetchAll("SELECT id, title FROM properties ORDER BY title ASC");

        $this->render('admin/emi/create', [
            'page_title' => $this->mlSupport->translate('Create EMI Plan'),
            'customers' => $customers,
            'properties' => $properties
        ]);
    }

    /**
     * Create a new EMI plan
     */
    public function store()
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonError($this->mlSupport->translate('Invalid request method.'));
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonError($this->mlSupport->translate('Security validation failed.'));
        }

        try {
            $data = $this->request->post();

            // Basic validation
            if (empty($data['customer_id']) || empty($data['total_amount']) || empty($data['tenure_months'])) {
                throw new \Exception($this->mlSupport->translate('Please fill all required fields.'));
            }

            $result = $this->emiModel->createPlan($data);

            $this->setFlash('success', $this->mlSupport->translate('EMI Plan created successfully.'));
            return $this->jsonResponse(['success' => true, 'redirect' => 'admin/emi']);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Show plan details
     */
    public function show($id)
    {
        $plan = $this->emiModel->getPlanDetails($id);

        if (!$plan) {
            $this->setFlash('error', $this->mlSupport->translate('Plan not found.'));
            $this->redirect('admin/emi');
            return;
        }

        $installments = $this->emiModel->getInstallments($id);

        $this->render('admin/emi/show', [
            'page_title' => $this->mlSupport->translate('EMI Plan Details'),
            'plan' => $plan,
            'installments' => $installments
        ]);
    }

    /**
     * Generate Receipt for an installment
     */
    public function generateReceipt($id)
    {
        $installmentId = (int)$id;
        $data = $this->emiModel->getInstallmentReceiptDetails($installmentId);

        if (!$data || $data['status'] !== 'paid') {
            // If not found or not paid, redirect with error
            $this->setFlash('error', $this->mlSupport->translate('Invalid installment or payment not found'));
            $this->redirect('admin/emi');
            return;
        }

        // Include TCPDF library if not autoloaded
        if (!class_exists('TCPDF')) {
            $tcpdfPath = BASE_PATH . '/vendor/tecnickcom/tcpdf/tcpdf.php';
            if (file_exists($tcpdfPath)) {
                require_once $tcpdfPath;
            } else {
                // Fallback or error
                $this->setFlash('error', 'TCPDF library not found.');
                $this->redirect('admin/emi');
                return;
            }
        }

        // Initialize TCPDF
        // Use global namespace for TCPDF as it's not namespaced in the library file
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('APS Dream Home');
        $pdf->SetAuthor('APS Dream Home');
        $pdf->SetTitle('EMI Payment Receipt');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Company Logo and Details
        $logoPath = BASE_PATH . '/assets/img/logo.png';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 15, 50);
        }

        $pdf->Cell(0, 5, 'APS Dream Home', 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $this->mlSupport->translate('Address: Your Company Address Here'), 0, 1, 'R'); // TODO: Fetch from settings
        $pdf->Cell(0, 5, $this->mlSupport->translate('Phone: Your Company Phone'), 0, 1, 'R');
        $pdf->Cell(0, 5, $this->mlSupport->translate('Email: your@email.com'), 0, 1, 'R');

        // Receipt Title
        $pdf->Ln(20);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $this->mlSupport->translate('EMI Payment Receipt'), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $this->mlSupport->translate('Receipt No') . ': ' . ($data['transaction_id'] ?? 'N/A'), 0, 1, 'C');

        // Customer Details
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, $this->mlSupport->translate('Customer Details'), 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Name') . ':', 0);
        $pdf->Cell(0, 5, $data['customer_name'], 0, 1);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Phone') . ':', 0);
        $pdf->Cell(0, 5, $data['customer_phone'], 0, 1);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Email') . ':', 0);
        $pdf->Cell(0, 5, $data['customer_email'], 0, 1);

        // Property Details
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, $this->mlSupport->translate('Property Details'), 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Property') . ':', 0);
        $pdf->Cell(0, 5, $data['property_title'], 0, 1);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Address') . ':', 0);
        $pdf->Cell(0, 5, $data['property_address'], 0, 1);

        // Payment Details
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, $this->mlSupport->translate('Payment Details'), 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        // Create a table for payment details
        $pdf->Cell(60, 7, $this->mlSupport->translate('Description'), 1);
        $pdf->Cell(30, 7, $this->mlSupport->translate('Due Date'), 1);
        $pdf->Cell(30, 7, $this->mlSupport->translate('Paid Date'), 1);
        $pdf->Cell(30, 7, $this->mlSupport->translate('Amount'), 1);
        $pdf->Cell(30, 7, $this->mlSupport->translate('Late Fee'), 1, 1);

        $pdf->Cell(60, 7, $this->mlSupport->translate('EMI Installment #') . $data['installment_number'], 1);
        $pdf->Cell(30, 7, date('d/m/Y', strtotime($data['due_date'])), 1);
        $pdf->Cell(30, 7, date('d/m/Y', strtotime($data['payment_date'])), 1);
        $pdf->Cell(30, 7, 'Rs. ' . number_format($data['amount'], 2), 1);
        $pdf->Cell(30, 7, 'Rs. ' . number_format($data['late_fee'] ?? 0, 2), 1, 1);

        // Total
        $totalAmount = $data['amount'] + ($data['late_fee'] ?? 0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(150, 7, $this->mlSupport->translate('Total Amount Paid') . ':', 1);
        $pdf->Cell(30, 7, 'Rs. ' . number_format($totalAmount, 2), 1, 1);

        // Payment Method
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Payment Method') . ':', 0);
        $pdf->Cell(0, 5, $this->mlSupport->translate(ucfirst($data['payment_method'] ?? 'Unknown')), 0, 1);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Transaction ID') . ':', 0);
        $pdf->Cell(0, 5, $data['transaction_id'] ?? 'N/A', 0, 1);

        // EMI Plan Status
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, $this->mlSupport->translate('EMI Plan Status'), 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Total Amount') . ':', 0);
        $pdf->Cell(0, 5, 'Rs. ' . number_format($data['total_amount'], 2), 0, 1);
        $pdf->Cell(50, 5, $this->mlSupport->translate('EMI Amount') . ':', 0);
        $pdf->Cell(0, 5, 'Rs. ' . number_format($data['emi_amount'], 2), 0, 1);
        $pdf->Cell(50, 5, $this->mlSupport->translate('Tenure') . ':', 0);
        $pdf->Cell(0, 5, $data['tenure_months'] . ' ' . $this->mlSupport->translate('months'), 0, 1);

        // Footer text
        $pdf->Ln(20);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, $this->mlSupport->translate('This is a computer generated receipt and does not require a signature.'), 0, 1, 'C');
        $pdf->Cell(0, 5, $this->mlSupport->translate('For any queries, please contact our support team.'), 0, 1, 'C');

        // Output the PDF
        $pdf->Output('EMI_Receipt_' . ($data['transaction_id'] ?? 'N/A') . '.pdf', 'I');
        exit;
    }

    /**
     * Pay an EMI installment
     */
    public function pay()
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonError($this->mlSupport->translate('Invalid request method'));
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonError($this->mlSupport->translate('Security validation failed.'));
        }

        try {
            $data = $this->request->post();

            // Basic validation
            if (empty($data['installment_id']) || empty($data['payment_date']) || empty($data['payment_method'])) {
                throw new \Exception($this->mlSupport->translate('Missing required fields'));
            }

            $paymentId = $this->emiModel->recordInstallmentPayment($data);

            if ($paymentId) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => $this->mlSupport->translate('Payment recorded successfully'),
                    'payment_id' => $paymentId
                ]);
            } else {
                throw new \Exception($this->mlSupport->translate('Failed to record payment'));
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Get foreclosure amount for a plan
     */
    public function getForeclosureAmount($id)
    {
        try {
            $amount = $this->emiModel->calculateForeclosureAmount($id);
            return $this->jsonResponse([
                'success' => true,
                'amount' => $amount,
                'formatted_amount' => '₹' . number_format($amount, 2)
            ]);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Foreclose an EMI plan
     */
    public function foreclose()
    {
        if (!$this->request->isPost()) {
            return $this->jsonError($this->mlSupport->translate('Invalid request method'));
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonError($this->mlSupport->translate('Security validation failed.'));
        }

        try {
            $data = $this->request->post();

            // Basic validation
            if (empty($data['emi_plan_id']) || empty($data['amount'])) {
                throw new \Exception($this->mlSupport->translate('Missing required fields'));
            }

            $success = $this->emiModel->foreclosePlan($data);

            if ($success) {
                $this->setFlash('success', $this->mlSupport->translate('Plan foreclosed successfully.'));
                return $this->jsonResponse([
                    'success' => true,
                    'message' => $this->mlSupport->translate('Plan foreclosed successfully')
                ]);
            } else {
                throw new \Exception($this->mlSupport->translate('Failed to foreclose plan'));
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Show Foreclosure Reports page
     */
    public function foreclosureReport()
    {
        $this->render('admin/emi/foreclosure_report', [
            'page_title' => $this->mlSupport->translate('EMI Foreclosure Reports')
        ]);
    }

    /**
     * Get Foreclosure Statistics (AJAX)
     */
    public function getForeclosureStats()
    {
        try {
            $stats = $this->emiModel->getForeclosureStats();
            return $this->jsonResponse(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Foreclosure Trend (AJAX)
     */
    public function getForeclosureTrend()
    {
        try {
            $months = $this->request->get('months', 12);
            $trend = $this->emiModel->getForeclosureTrend($months);
            return $this->jsonResponse($trend);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Foreclosure Report Data (AJAX)
     */
    public function getForeclosureReportData()
    {
        try {
            $filters = [
                'start_date' => $this->request->get('start_date'),
                'end_date' => $this->request->get('end_date'),
                'customer_id' => $this->request->get('customer_id')
            ];

            $data = $this->emiModel->getForeclosureReportData($filters);
            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Trigger EMI Automation manually
     */
    public function runAutomation()
    {
        if (!$this->request->isPost()) {
            return $this->jsonError('Invalid request method');
        }

        try {
            // Include service manually if not autoloaded (though it should be)
            if (!class_exists('\App\Services\EMIAutomationService')) {
                require_once BASE_PATH . '/app/Services/EMIAutomationService.php';
            }

            $service = new \App\Services\EMIAutomationService();
            $results = $service->runAll();
            return $this->jsonResponse(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }
}
