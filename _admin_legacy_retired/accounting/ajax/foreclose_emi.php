<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db_connection.php';
require_once '../../../includes/auth_check.php';
require_once '../includes/emi_foreclosure_logger.php';
require_once '../includes/emi_foreclosure_notifier.php';

header('Content-Type: application/json');

class EMIForeclosureProcessor {
    private $conn;
    private $logger;
    private $notifier;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->logger = new EMIForeclosureLogger($dbConnection);
        $this->notifier = new EMIForeclosureNotifier($dbConnection);
    }

    private function validateForeclosureEligibility(int $emiPlanId): array {
        $checkQuery = "SELECT 
            id, 
            customer_id,
            property_id,
            status, 
            tenure_months, 
            start_date, 
            total_amount,
            emi_amount,
            remaining_amount
        FROM emi_plans 
        WHERE id = ? AND status = 'active'";
        
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param('i', $emiPlanId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('EMI Plan not found or already completed/defaulted');
        }
        
        $planDetails = $result->fetch_assoc();
        
        $startDate = new DateTime($planDetails['start_date']);
        $currentDate = new DateTime();
        $monthsPassed = $startDate->diff($currentDate)->m;
        
        if ($monthsPassed < ($planDetails['tenure_months'] * 0.5)) {
            throw new Exception('Foreclosure not allowed before 50% of loan tenure');
        }
        
        return $planDetails;
    }

    public function processForeclose(int $emiPlanId): array {
        try {
            $this->conn->begin_transaction();
            
            $planDetails = $this->validateForeclosureEligibility($emiPlanId);
            
            $foreclosureAmount = $planDetails['remaining_amount'] ?? $planDetails['total_amount'];
            
            $updateQuery = "UPDATE emi_plans 
                SET 
                    status = 'completed', 
                    foreclosure_date = NOW(),
                    foreclosure_amount = ?,
                    remaining_amount = 0
                WHERE id = ?";
            
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->bind_param('di', $foreclosureAmount, $emiPlanId);
            $stmt->execute();
            
            $this->logger->logForeclosureAttempt(
                $emiPlanId, 
                'success', 
                'EMI Plan successfully foreclosed', 
                $foreclosureAmount,
                [
                    'customer_id' => $planDetails['customer_id'],
                    'property_id' => $planDetails['property_id'],
                    'tenure_months' => $planDetails['tenure_months']
                ]
            );
            
            // Send notifications for successful foreclosure
            $this->notifier->notifyStakeholders(
                $emiPlanId, 
                'success', 
                ['foreclosure_amount' => $foreclosureAmount]
            );
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'EMI Plan successfully foreclosed',
                'foreclosure_amount' => $foreclosureAmount
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            
            $this->logger->logForeclosureAttempt(
                $emiPlanId, 
                'failed', 
                $e->getMessage(),
                0.0
            );
            
            // Send notifications for failed foreclosure
            $this->notifier->notifyStakeholders(
                $emiPlanId, 
                'failed', 
                ['error_message' => $e->getMessage()]
            );
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

// Main request handling
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    global $con;
    $conn = $con;
    
    // Validate required fields
    $requiredFields = ['emi_plan_id'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $emiPlanId = intval($_POST['emi_plan_id']);
    
    // Start transaction
    $conn->begin_transaction();
    
    // Validate admin permissions
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        throw new Exception('Unauthorized access');
    }
    
    // Get EMI plan details with additional validation
    $query = "SELECT ep.*, 
                     c.name as customer_name,
                     c.email as customer_email,
                     p.title as property_title,
                     (SELECT COUNT(*) FROM emi_installments 
                      WHERE emi_plan_id = ep.id AND payment_status = 'paid') as paid_installments,
                     (SELECT COUNT(*) FROM emi_installments 
                      WHERE emi_plan_id = ep.id AND payment_status = 'pending') as pending_installments
              FROM emi_plans ep
              JOIN customers c ON ep.customer_id = c.id
              JOIN properties p ON ep.property_id = p.id
              WHERE ep.id = ? AND ep.status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $emiPlanId);
    $stmt->execute();
    $emiPlan = $stmt->get_result()->fetch_assoc();
    
    // Validate EMI plan
    if (!$emiPlan) {
        throw new Exception('EMI plan not found or not active');
    }
    
    // Check minimum tenure for foreclosure
    $minTenureMonths = 6; // Minimum 6 months of EMI payments
    if ($emiPlan['paid_installments'] < $minTenureMonths) {
        throw new Exception("Foreclosure not allowed. Minimum $minTenureMonths months of payments required.");
    }
    
    // Prevent multiple foreclosure attempts
    $checkPreviousQuery = "SELECT COUNT(*) as foreclosure_count 
                           FROM emi_plans 
                           WHERE id = ? AND (foreclosure_date IS NOT NULL OR status = 'completed')";
    $stmt = $conn->prepare($checkPreviousQuery);
    $stmt->bind_param("i", $emiPlanId);
    $stmt->execute();
    $previousForeclosure = $stmt->get_result()->fetch_assoc();
    
    if ($previousForeclosure['foreclosure_count'] > 0) {
        throw new Exception('This EMI plan has already been foreclosed');
    }
    
    // Calculate remaining principal
    $query = "SELECT 
                SUM(CASE WHEN payment_status = 'paid' THEN principal_component ELSE 0 END) as total_principal_paid,
                SUM(CASE WHEN payment_status = 'pending' THEN principal_component ELSE 0 END) as remaining_principal
              FROM emi_installments
              WHERE emi_plan_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $emiPlanId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $totalPrincipalPaid = $result['total_principal_paid'] ?? 0;
    
    // Calculate remaining amount
    $remainingPrincipal = $emiPlan['total_amount'] - $totalPrincipalPaid;
    
    // Apply foreclosure charges (2% of remaining principal)
    $foreclosureCharge = $remainingPrincipal * 0.02;
    $totalForeclosureAmount = $remainingPrincipal + $foreclosureCharge;
    
    // Create foreclosure payment record
    $paymentQuery = "INSERT INTO payments (
                        transaction_id,
                        customer_id,
                        property_id,
                        amount,
                        payment_type,
                        payment_method,
                        description,
                        status,
                        payment_date,
                        created_by
                    ) VALUES (?, ?, ?, ?, 'emi_foreclosure', ?, ?, 'completed', NOW(), ?)";
                    
    $transactionId = 'FORE' . time() . rand(1000, 9999);
    $description = "EMI Plan Foreclosure Payment (Including 2% foreclosure charge)";
    $paymentMethod = $_POST['payment_method'] ?? 'bank_transfer';
    
    $stmt = $conn->prepare($paymentQuery);
    $stmt->bind_param(
        "siidssi",
        $transactionId,
        $emiPlan['customer_id'],
        $emiPlan['property_id'],
        $totalForeclosureAmount,
        $paymentMethod,
        $description,
        $_SESSION['admin_id']
    );
    $stmt->execute();
    $paymentId = $stmt->insert_id;
    
    // Update remaining installments
    $updateInstallments = "UPDATE emi_installments 
                          SET payment_status = 'cancelled'
                          WHERE emi_plan_id = ? AND payment_status = 'pending'";
    $stmt = $conn->prepare($updateInstallments);
    $stmt->bind_param("i", $emiPlanId);
    $stmt->execute();
    
    // Update EMI plan status
    $updatePlan = "UPDATE emi_plans 
                   SET status = 'completed',
                       foreclosure_date = NOW(),
                       foreclosure_amount = ?,
                       foreclosure_payment_id = ?,
                       updated_at = NOW()
                   WHERE id = ?";
    $stmt = $conn->prepare($updatePlan);
    $stmt->bind_param("dii", $totalForeclosureAmount, $paymentId, $emiPlanId);
    $stmt->execute();
    
    // Create notification
    $notificationQuery = "INSERT INTO notifications (
                            user_id,
                            type,
                            title,
                            message,
                            link,
                            created_at
                         ) VALUES (?, 'emi_foreclosure', 'EMI Plan Foreclosed', ?, ?, NOW())";
                         
    $notificationMessage = "Your EMI plan has been foreclosed. Foreclosure amount paid: ₹" . 
                          number_format($totalForeclosureAmount, 2);
    $notificationLink = "payments/view.php?id=" . $paymentId;
    
    $stmt = $conn->prepare($notificationQuery);
    $stmt->bind_param("iss", $emiPlan['customer_id'], $notificationMessage, $notificationLink);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'EMI plan foreclosed successfully',
        'data' => [
            'payment_id' => $paymentId,
            'transaction_id' => $transactionId,
            'foreclosure_amount' => $totalForeclosureAmount
        ]
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log('EMI Foreclosure Error: ' . $e->getMessage());
    
    // Log foreclosure attempt
    if (isset($emiPlanId)) {
        // Log foreclosure attempt using EMIForeclosureLogger
        require_once '../includes/emi_foreclosure_logger.php';
        $logger = new EMIForeclosureLogger($conn);
        $logger->logForeclosureAttempt(
            $emiPlanId, 
            'failed', 
            $e->getMessage()
        );
    }
    
    // Rollback transaction
    if (isset($conn)) {
        $conn->rollback();
    }
    
    // Prepare detailed error response
    $response = [
        'success' => false,
        'message' => 'Failed to foreclose EMI plan',
        'error_details' => [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ];
    
    // Send error response
    echo json_encode($response);
    
    // Optional: Send error notification to admin
    if (isset($_SESSION['admin_id'])) {
        $notificationQuery = "INSERT INTO notifications (
            user_id,
            type,
            title,
            message,
            link,
            created_at
        ) VALUES (?, 'system_error', 'EMI Foreclosure Error', ?, ?, NOW())";
        
        $stmt = $conn->prepare($notificationQuery);
        $stmt->bind_param(
            "iss", 
            $_SESSION['admin_id'], 
            $e->getMessage(), 
            "admin/accounting/emi_errors.php"
        );
        $stmt->execute();
    }
    
    exit;
}
?>
