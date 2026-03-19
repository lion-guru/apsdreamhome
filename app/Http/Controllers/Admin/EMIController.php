<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * EMI Controller - Custom MVC Implementation
 * Handles EMI plans management in the Admin panel
 */
class EMIController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Show EMI plans list
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT e.*, 
                           b.booking_number, 
                           c.name as customer_name, 
                           c.email as customer_email,
                           p.title as property_title,
                           COUNT(em.id) as payment_count,
                           COALESCE(SUM(em.amount), 0) as paid_amount
                    FROM emi_plans e
                    LEFT JOIN bookings b ON e.booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties p ON b.property_id = p.id
                    LEFT JOIN emi_payments em ON e.id = em.emi_plan_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (b.booking_number LIKE ? OR c.name LIKE ? OR p.title LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND e.status = ?";
                $params[] = $status;
            }

            $sql .= " GROUP BY e.id ORDER BY e.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT e.*, b.booking_number, c.name as customer_name, c.email as customer_email, p.title as property_title, COUNT(em.id) as payment_count, COALESCE(SUM(em.amount), 0) as paid_amount", "SELECT COUNT(DISTINCT e.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $emiPlans = $stmt->fetchAll();

            $data = [
                'page_title' => 'EMI Plans - APS Dream Home',
                'active_page' => 'emi',
                'emi_plans' => $emiPlans,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ]
            ];

            return $this->render('admin/emi/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("EMI Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load EMI plans');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new EMI plan
     */
    public function create()
    {
        try {
            // Get available bookings without EMI plans
            $sql = "SELECT b.id, b.booking_number, b.total_amount, 
                           c.name as customer_name, 
                           p.title as property_title
                    FROM bookings b
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties p ON b.property_id = p.id
                    WHERE b.id NOT IN (SELECT booking_id FROM emi_plans)
                    AND b.status = 'confirmed'
                    ORDER BY b.created_at DESC";
            $bookings = $this->db->fetchAll($sql);

            $data = [
                'page_title' => 'Create EMI Plan - APS Dream Home',
                'active_page' => 'emi',
                'bookings' => $bookings
            ];

            return $this->render('admin/emi/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("EMI Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load EMI form');
            return $this->redirect('admin/emi');
        }
    }

    /**
     * Store a newly created EMI plan
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['booking_id', 'total_amount', 'down_payment', 'interest_rate', 'tenure_months'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            $bookingId = (int)$data['booking_id'];
            $totalAmount = (float)$data['total_amount'];
            $downPayment = (float)$data['down_payment'];
            $interestRate = (float)$data['interest_rate'];
            $tenureMonths = (int)$data['tenure_months'];

            // Validate values
            if ($bookingId <= 0 || $totalAmount <= 0 || $downPayment < 0 || $tenureMonths <= 0) {
                return $this->jsonError('Invalid input values', 400);
            }

            if ($downPayment >= $totalAmount) {
                return $this->jsonError('Down payment must be less than total amount', 400);
            }

            // Check if booking exists and has no EMI plan
            $sql = "SELECT b.*, COUNT(e.id) as existing_emi
                    FROM bookings b
                    LEFT JOIN emi_plans e ON b.id = e.booking_id
                    WHERE b.id = ?
                    GROUP BY b.id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();

            if (!$booking) {
                return $this->jsonError('Booking not found', 404);
            }

            if ($booking['existing_emi'] > 0) {
                return $this->jsonError('EMI plan already exists for this booking', 400);
            }

            // Calculate EMI details
            $loanAmount = $totalAmount - $downPayment;
            $monthlyInterest = $interestRate / 12 / 100;
            $emiAmount = $loanAmount * $monthlyInterest * pow(1 + $monthlyInterest, $tenureMonths) /
                (pow(1 + $monthlyInterest, $tenureMonths) - 1);
            $totalPayable = $downPayment + ($emiAmount * $tenureMonths);
            $totalInterest = $totalPayable - $totalAmount;

            // Create EMI plan
            $sql = "INSERT INTO emi_plans 
                    (booking_id, total_amount, down_payment, loan_amount, interest_rate, tenure_months, 
                     emi_amount, total_interest, total_payable, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $bookingId,
                $totalAmount,
                $downPayment,
                $loanAmount,
                $interestRate,
                $tenureMonths,
                round($emiAmount, 2),
                round($totalInterest, 2),
                round($totalPayable, 2)
            ]);

            if ($result) {
                $emiPlanId = $this->db->lastInsertId();

                // Create EMI schedule
                $this->createEMISchedule($emiPlanId, $emiAmount, $tenureMonths);

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'emi_plan_created', [
                    'emi_plan_id' => $emiPlanId,
                    'booking_id' => $bookingId,
                    'total_amount' => $totalAmount
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'EMI plan created successfully',
                    'emi_plan_id' => $emiPlanId
                ]);
            }

            return $this->jsonError('Failed to create EMI plan', 500);
        } catch (Exception $e) {
            $this->loggingService->error("EMI Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create EMI plan', 500);
        }
    }

    /**
     * Display the specified EMI plan
     */
    public function show($id)
    {
        try {
            $emiPlanId = intval($id);
            if ($emiPlanId <= 0) {
                $this->setFlash('error', 'Invalid EMI plan ID');
                return $this->redirect('admin/emi');
            }

            // Get EMI plan details
            $sql = "SELECT e.*, 
                           b.booking_number, 
                           c.name as customer_name, 
                           c.email as customer_email,
                           p.title as property_title
                    FROM emi_plans e
                    LEFT JOIN bookings b ON e.booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties p ON b.property_id = p.id
                    WHERE e.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$emiPlanId]);
            $emiPlan = $stmt->fetch();

            if (!$emiPlan) {
                $this->setFlash('error', 'EMI plan not found');
                return $this->redirect('admin/emi');
            }

            // Get EMI schedule
            $sql = "SELECT * FROM emi_schedule 
                    WHERE emi_plan_id = ? 
                    ORDER BY installment_number ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$emiPlanId]);
            $schedule = $stmt->fetchAll();

            // Get payment history
            $sql = "SELECT ep.*, es.installment_number
                    FROM emi_payments ep
                    LEFT JOIN emi_schedule es ON ep.emi_schedule_id = es.id
                    WHERE ep.emi_plan_id = ?
                    ORDER BY ep.payment_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$emiPlanId]);
            $payments = $stmt->fetchAll();

            $data = [
                'page_title' => 'EMI Plan Details - APS Dream Home',
                'active_page' => 'emi',
                'emi_plan' => $emiPlan,
                'schedule' => $schedule,
                'payments' => $payments
            ];

            return $this->render('admin/emi/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("EMI Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load EMI plan details');
            return $this->redirect('admin/emi');
        }
    }

    /**
     * Process EMI payment
     */
    public function processPayment($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $emiPlanId = intval($id);
            $scheduleId = (int)($_POST['schedule_id'] ?? 0);
            $paymentMethod = $_POST['payment_method'] ?? '';
            $amount = (float)($_POST['amount'] ?? 0);
            $transactionId = $_POST['transaction_id'] ?? '';

            if ($emiPlanId <= 0 || $scheduleId <= 0 || $amount <= 0 || empty($paymentMethod)) {
                return $this->jsonError('Invalid payment details', 400);
            }

            $this->db->beginTransaction();

            try {
                // Get schedule details
                $sql = "SELECT es.*, e.emi_amount
                        FROM emi_schedule es
                        JOIN emi_plans e ON es.emi_plan_id = e.id
                        WHERE es.id = ? AND es.emi_plan_id = ? AND es.status = 'pending'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$scheduleId, $emiPlanId]);
                $schedule = $stmt->fetch();

                if (!$schedule) {
                    $this->db->rollBack();
                    return $this->jsonError('Invalid or already paid installment', 400);
                }

                // Record payment
                $sql = "INSERT INTO emi_payments 
                        (emi_plan_id, emi_schedule_id, amount, payment_method, transaction_id, status, payment_date)
                        VALUES (?, ?, ?, ?, ?, 'completed', NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$emiPlanId, $scheduleId, $amount, $paymentMethod, $transactionId]);
                $paymentId = $this->db->lastInsertId();

                // Update schedule status
                $sql = "UPDATE emi_schedule 
                        SET status = 'paid', paid_date = NOW(), paid_amount = ?
                        WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$amount, $scheduleId]);

                // Check if all installments are paid
                $sql = "SELECT COUNT(*) as total, 
                              SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid
                        FROM emi_schedule 
                        WHERE emi_plan_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$emiPlanId]);
                $result = $stmt->fetch();

                if ($result['total'] == $result['paid']) {
                    // Update EMI plan status to completed
                    $sql = "UPDATE emi_plans SET status = 'completed', completed_at = NOW() WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$emiPlanId]);
                }

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'emi_payment_processed', [
                    'emi_plan_id' => $emiPlanId,
                    'schedule_id' => $scheduleId,
                    'amount' => $amount
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'EMI payment processed successfully',
                    'payment_id' => $paymentId
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("EMI Process Payment error: " . $e->getMessage());
            return $this->jsonError('Failed to process EMI payment', 500);
        }
    }

    /**
     * Create EMI schedule
     */
    private function createEMISchedule(int $emiPlanId, float $emiAmount, int $tenureMonths): void
    {
        try {
            $sql = "INSERT INTO emi_schedule (emi_plan_id, installment_number, due_amount, due_date, status)
                    VALUES (?, ?, ?, ?, 'pending')";

            for ($i = 1; $i <= $tenureMonths; $i++) {
                $dueDate = date('Y-m-d', strtotime("+$i months"));
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$emiPlanId, $i, $emiAmount, $dueDate]);
            }
        } catch (Exception $e) {
            $this->loggingService->error("Create EMI Schedule error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get EMI statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total EMI plans
            $sql = "SELECT COUNT(*) as total FROM emi_plans";
            $result = $this->db->fetchOne($sql);
            $stats['total_plans'] = (int)($result['total'] ?? 0);

            // Active EMI plans
            $sql = "SELECT COUNT(*) as total FROM emi_plans WHERE status = 'active'";
            $result = $this->db->fetchOne($sql);
            $stats['active_plans'] = (int)($result['total'] ?? 0);

            // Completed EMI plans
            $sql = "SELECT COUNT(*) as total FROM emi_plans WHERE status = 'completed'";
            $result = $this->db->fetchOne($sql);
            $stats['completed_plans'] = (int)($result['total'] ?? 0);

            // Total EMI amount
            $sql = "SELECT COALESCE(SUM(total_payable), 0) as total FROM emi_plans";
            $result = $this->db->fetchOne($sql);
            $stats['total_amount'] = (float)($result['total'] ?? 0);

            // This month's EMI payments
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM emi_payments 
                    WHERE MONTH(payment_date) = MONTH(CURRENT_DATE) 
                    AND YEAR(payment_date) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_payments'] = (float)($result['total'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get EMI Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch EMI stats'
            ], 500);
        }
    }
}
