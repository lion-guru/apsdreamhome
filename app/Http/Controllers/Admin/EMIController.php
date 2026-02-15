<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

/**
 * EMI Controller
 * Handles EMI plans management in the Admin panel
 */
class EMIController extends BaseController
{
    protected $emiModel;

    public function __construct()
    {
        parent::__construct();

        // Check authentication
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->emiModel = $this->model('EMI');
    }

    /**
     * Get EMI statistics for dashboard
     */
    public function stats()
    {
        header('Content-Type: application/json');

        try {
            $stats = $this->emiModel->getStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Pay an EMI installment
     */
    public function pay()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $data = $_POST;

            // Basic validation
            if (empty($data['installment_id']) || empty($data['payment_date']) || empty($data['payment_method'])) {
                throw new \Exception('Missing required fields');
            }

            $paymentId = $this->emiModel->recordInstallmentPayment($data);

            if ($paymentId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'payment_id' => $paymentId
                ]);
            } else {
                throw new \Exception('Failed to record payment');
            }
        } catch (\Exception $e) {
            echo json_encode([
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
        header('Content-Type: application/json');

        $params = [
            'draw' => (int)($_POST['draw'] ?? 1),
            'start' => (int)($_POST['start'] ?? 0),
            'length' => (int)($_POST['length'] ?? 10),
            'search' => $_POST['search']['value'] ?? '',
            'orderColumn' => (int)($_POST['order'][0]['column'] ?? 0),
            'orderDir' => $_POST['order'][0]['dir'] ?? 'DESC'
        ];

        // Column mapping for DataTables
        $columns = ['c.name', 'p.title', 'ep.total_amount', 'ep.emi_amount', 'ep.tenure_months', 'ep.start_date', 'ep.status'];
        $params['orderBy'] = $columns[$params['orderColumn']] ?? 'ep.id';

        try {
            $result = $this->emiModel->getFilteredPlans($params);

            echo json_encode([
                'draw' => $params['draw'],
                'recordsTotal' => $result['totalRecords'],
                'recordsFiltered' => $result['filteredRecords'],
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display all EMI plans
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
            'page_title' => 'EMI Plans Management'
        ]);
    }

    /**
     * Show details of a specific EMI plan
     */
    public function show($id)
    {
        $plan = $this->emiModel->getPlanDetails($id);

        if (!$plan) {
            $this->setFlash('error', 'EMI Plan not found');
            $this->redirect('admin/emi');
            return;
        }

        $installments = $this->emiModel->getInstallments($id);

        $this->render('admin/emi/show', [
            'plan' => $plan,
            'installments' => $installments,
            'page_title' => 'EMI Plan Details'
        ]);
    }

    /**
     * Create a new EMI plan
     */
    public function store()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $data = $_POST;

            // Validate required fields
            $requiredFields = [
                'customer_id',
                'property_id',
                'total_amount',
                'interest_rate',
                'tenure_months',
                'down_payment',
                'start_date'
            ];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new \Exception("$field is required");
                }
            }

            $result = $this->emiModel->createPlan($data);

            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'EMI plan created successfully',
                    'data' => [
                        'emi_plan_id' => $result['emi_plan_id'],
                        'emi_amount' => $result['emi_amount']
                    ]
                ]);
            } else {
                throw new \Exception('Failed to create EMI plan');
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create EMI plan: ' . $e->getMessage()
            ]);
        }
    }
}
