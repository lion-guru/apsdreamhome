<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db_connection.php';
require_once '../../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    $conn = getDbConnection();
    
    // Validate required fields
    $requiredFields = [
        'customer_id', 'property_id', 'total_amount', 'interest_rate',
        'tenure_months', 'down_payment', 'start_date'
    ];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Calculate EMI amount
    $principal = floatval($_POST['total_amount']) - floatval($_POST['down_payment']);
    $rate = floatval($_POST['interest_rate']) / (12 * 100); // Monthly interest rate
    $tenure = intval($_POST['tenure_months']);
    
    $emiAmount = $principal * $rate * pow(1 + $rate, $tenure) / (pow(1 + $rate, $tenure) - 1);
    
    // Calculate end date
    $startDate = new DateTime($_POST['start_date']);
    $endDate = clone $startDate;
    $endDate->modify("+$tenure months");
    
    // Insert EMI plan
    $query = "INSERT INTO emi_plans (
                customer_id,
                property_id,
                total_amount,
                interest_rate,
                tenure_months,
                emi_amount,
                down_payment,
                start_date,
                end_date,
                created_by
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "iiddiddsssi",
        $_POST['customer_id'],
        $_POST['property_id'],
        $_POST['total_amount'],
        $_POST['interest_rate'],
        $tenure,
        $emiAmount,
        $_POST['down_payment'],
        $_POST['start_date'],
        $endDate->format('Y-m-d'),
        $_SESSION['admin_id']
    );
    $stmt->execute();
    $emiPlanId = $stmt->insert_id;
    
    // Create installments
    $installmentDate = clone $startDate;
    $remainingPrincipal = $principal;
    
    for ($i = 1; $i <= $tenure; $i++) {
        $interestComponent = $remainingPrincipal * $rate;
        $principalComponent = $emiAmount - $interestComponent;
        $remainingPrincipal -= $principalComponent;
        
        $query = "INSERT INTO emi_installments (
                    emi_plan_id,
                    installment_number,
                    due_date,
                    amount,
                    principal_component,
                    interest_component
                  ) VALUES (?, ?, ?, ?, ?, ?)";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "iisddd",
            $emiPlanId,
            $i,
            $installmentDate->format('Y-m-d'),
            $emiAmount,
            $principalComponent,
            $interestComponent
        );
        $stmt->execute();
        
        $installmentDate->modify('+1 month');
    }
    
    // Create notification for the customer
    $notificationQuery = "INSERT INTO notifications (
                            user_id,
                            type,
                            title,
                            message,
                            link,
                            created_at
                         ) VALUES (?, 'emi', 'EMI Plan Created', ?, ?, NOW())";
                         
    $notificationMessage = "Your EMI plan has been created with monthly installment of ₹" . number_format($emiAmount, 2);
    $notificationLink = "emi/view.php?id=" . $emiPlanId;
    
    $stmt = $conn->prepare($notificationQuery);
    $stmt->bind_param("iss", $_POST['customer_id'], $notificationMessage, $notificationLink);
    $stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'EMI plan created successfully',
        'data' => [
            'emi_plan_id' => $emiPlanId,
            'emi_amount' => $emiAmount
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create EMI plan: ' . $e->getMessage()
    ]);
}
?>
