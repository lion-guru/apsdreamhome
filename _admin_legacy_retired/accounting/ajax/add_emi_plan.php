<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
$db = \App\Core\App::database();
require_once __DIR__ . '/../../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
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
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Start transaction
    $db->getConnection()->beginTransaction();

    // Calculate EMI amount
    $principal = floatval($_POST['total_amount']) - floatval($_POST['down_payment']);
    $rate = floatval($_POST['interest_rate']) / (12 * 100); // Monthly interest rate
    $tenure = intval($_POST['tenure_months']);

    if ($rate > 0) {
        $emiAmount = $principal * $rate * pow(1 + $rate, $tenure) / (pow(1 + $rate, $tenure) - 1);
    } else {
        $emiAmount = $principal / $tenure;
    }

    // Calculate end date
    $startDate = new DateTime($_POST['start_date']);
    $endDate = clone $startDate;
    $endDate->modify("+$tenure months");
    $endDateFormatted = $endDate->format('Y-m-d');

    // Insert EMI plan
    $emiPlanId = $db->insert('emi_plans', [
        'customer_id' => $_POST['customer_id'],
        'property_id' => $_POST['property_id'],
        'total_amount' => $_POST['total_amount'],
        'interest_rate' => $_POST['interest_rate'],
        'tenure_months' => $tenure,
        'emi_amount' => $emiAmount,
        'down_payment' => $_POST['down_payment'],
        'start_date' => $_POST['start_date'],
        'end_date' => $endDateFormatted,
        'created_by' => $_SESSION['admin_id']
    ]);

    if (!$emiPlanId) {
        throw new Exception("Failed to create EMI plan");
    }

    // Create installments
    $installmentDate = clone $startDate;
    $remainingPrincipal = $principal;

    for ($i = 1; $i <= $tenure; $i++) {
        $interestComponent = $remainingPrincipal * $rate;
        $principalComponent = $emiAmount - $interestComponent;
        $remainingPrincipal -= $principalComponent;
        $installmentDateFormatted = $installmentDate->format('Y-m-d');

        $db->insert('emi_installments', [
            'emi_plan_id' => $emiPlanId,
            'installment_number' => $i,
            'due_date' => $installmentDateFormatted,
            'amount' => $emiAmount,
            'principal_component' => $principalComponent,
            'interest_component' => $interestComponent
        ]);

        $installmentDate->modify('+1 month');
    }

    // Create notification for the customer
    $notificationMessage = "Your EMI plan has been created with monthly installment of ₹" . number_format($emiAmount, 2);
    $notificationLink = "emi/view.php?id=" . $emiPlanId;

    $db->insert('notifications', [
        'user_id' => $_POST['customer_id'],
        'type' => 'emi',
        'title' => 'EMI Plan Created',
        'message' => $notificationMessage,
        'link' => $notificationLink,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // Commit transaction
    $db->getConnection()->commit();

    echo json_encode([
        'success' => true,
        'message' => 'EMI plan created successfully',
        'data' => [
            'emi_plan_id' => $emiPlanId,
            'emi_amount' => $emiAmount
        ]
    ]);
} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->getConnection()->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create EMI plan: ' . $e->getMessage()
    ]);
}
