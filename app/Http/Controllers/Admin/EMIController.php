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
     * Display all EMI plans
     */
    public function index()
    {
        $sql = "SELECT e.*, u.name as customer_name, b.booking_date 
                FROM emi_plans e
                JOIN customers c ON e.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                JOIN bookings b ON e.booking_id = b.id
                ORDER BY e.created_at DESC";

        $stmt = $this->db->query($sql);
        $plans = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/emi/index', [
            'plans' => $plans,
            'page_title' => 'EMI Plans Management'
        ]);
    }

    /**
     * Show details of a specific EMI plan
     */
    public function show($id)
    {
        $sql = "SELECT e.*, u.name as customer_name, u.email, u.phone, b.total_amount as booking_total
                FROM emi_plans e
                JOIN customers c ON e.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                JOIN bookings b ON e.booking_id = b.id
                WHERE e.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $plan = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$plan) {
            $this->redirect('admin/emi');
            return;
        }

        $payments = $this->emiModel ? $this->emiModel->getSchedule($id) : [];

        $this->render('admin/emi/show', [
            'plan' => $plan,
            'payments' => $payments,
            'page_title' => 'EMI Plan Details'
        ]);
    }

    /**
     * Create a new EMI plan
     */
    public function store()
    {
        $data = $_POST;

        // Validate basic data
        if (empty($data['booking_id']) || empty($data['tenure_months'])) {
            $this->redirect('admin/emi');
            return;
        }

        $result = $this->emiModel ? $this->emiModel->save($data) : false;

        if ($result) {
            $this->redirect('admin/emi');
        } else {
            $this->redirect('admin/emi');
        }
    }

    /**
     * Record a payment for an EMI
     */
    public function pay()
    {
        $data = $_POST;

        if (empty($data['emi_plan_id']) || empty($data['amount'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            exit;
        }

        $result = $this->emiModel ? $this->emiModel->recordPayment($data) : false;

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Payment recorded successfully' : 'Failed to record payment'
        ]);
        exit;
    }
}
