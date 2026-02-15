<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/core/autoload.php';
require_once BASE_PATH . '/app/Core/Config.php';

use App\Models\Payment;
use App\Models\Customer;
use App\Core\Database;

echo "Starting Payment CRUD Test...\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // 1. Setup Test Customer
    echo "\n[TEST] Setting up Test Customer...\n";
    $timestamp = time();
    $customerId = 'CUST_TEST_' . $timestamp;
    $customerSql = "INSERT INTO customers (id, name, email, phone, status) VALUES (?, ?, ?, ?, 'active')";
    $stmt = $conn->prepare($customerSql);
    // Use a unique phone number
    $phone = '9' . substr($timestamp, -9);
    $stmt->execute([$customerId, 'Test Customer', 'test_pay_' . $timestamp . '@example.com', $phone]);
    echo "Created Customer ID: $customerId\n";

    $paymentModel = new Payment();

    // 2. Create Payment
    echo "\n[TEST] Recording Payment...\n";
    $paymentData = [
        'customer_id' => $customerId,
        'amount' => 5000.00,
        'payment_type' => 'booking',
        'payment_method' => 'cash',
        'description' => 'Test Payment via Script',
        'status' => 'completed'
    ];

    $paymentId = $paymentModel->recordPayment($paymentData);
    if ($paymentId) {
        echo "Payment recorded with ID: $paymentId\n";
    } else {
        echo "FAIL: recordPayment returned false.\n";
        exit(1);
    }

    // 3. Read Payment (via Paginated List)
    echo "\n[TEST] Reading Payments...\n";
    $payments = $paymentModel->getPaginatedPayments(0, 10, '', []);
    $found = false;
    foreach ($payments as $p) {
        if ($p['id'] == $paymentId) {
            echo "PASS: Payment found in list.\n";
            echo "Amount: " . $p['amount'] . "\n";
            echo "Notes: " . $p['notes'] . "\n";
            echo "Customer Name: " . $p['customer_name'] . "\n";
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "FAIL: Payment not found in paginated list.\n";
    }

    // 4. Dashboard Stats
    echo "\n[TEST] Checking Dashboard Stats...\n";
    $stats = $paymentModel->getDashboardStats();
    echo "Monthly Revenue: " . $stats['monthly_revenue'] . "\n";
    // We can't easily assert the exact value without knowing prior state, but we can check format/existence.
    if (isset($stats['monthly_revenue'])) {
        echo "PASS: Dashboard stats returned.\n";
    } else {
        echo "FAIL: Dashboard stats missing.\n";
    }

    // 5. Clean up
    // Optional: Delete payment and customer
    // $conn->exec("DELETE FROM payments WHERE id = $paymentId");
    // $conn->exec("DELETE FROM customers WHERE id = '$customerId'");

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
