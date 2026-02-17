<?php
require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid request method'))
    ]);
    exit;
}

// CSRF Validation
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid CSRF token'))
    ]);
    exit;
}

// RBAC Protection - Only Super Admin and Manager can create EMI plans
$currentRole = $_SESSION['admin_role'] ?? '';
if ($currentRole !== 'superadmin' && $currentRole !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can create EMI plans'))
    ]);
    exit;
}

try {
    // Validate required fields
    $requiredFields = [
        'customer_id', 'property_id', 'total_amount', 'interest_rate',
        'tenure_months', 'down_payment', 'start_date'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception($mlSupport->translate(str_replace('_', ' ', $field) . " is required"));
        }
    }

    // Start transaction
    \App\Core\App::database()->beginTransaction();

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

    \App\Core\App::database()->execute($query, [
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
    ]);
    $emiPlanId = \App\Core\App::database()->lastInsertId();

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

        \App\Core\App::database()->execute($query, [
            $emiPlanId,
            $i,
            $installmentDate->format('Y-m-d'),
            $emiAmount,
            $principalComponent,
            $interestComponent
        ]);

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

    $notificationMessage = h($mlSupport->translate("Your EMI plan has been created with monthly installment of ₹")) . h(number_format($emiAmount, 2));
    $notificationLink = "emi/view.php?id=" . intval($emiPlanId);

    \App\Core\App::database()->execute($notificationQuery, [
        $_POST['customer_id'],
        $notificationMessage,
        $notificationLink
    ]);

    // Commit the transaction
    \App\Core\App::database()->commit();

    echo json_encode([
        'success' => true,
        'message' => h($mlSupport->translate('EMI plan created successfully')),
        'data' => [
            'emi_plan_id' => $emiPlanId,
            'emi_amount' => $emiAmount
        ]
    ]);

} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    \App\Core\App::database()->rollBack();

    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Failed to create EMI plan') . ': ' . $e->getMessage())
    ]);
}
?>
