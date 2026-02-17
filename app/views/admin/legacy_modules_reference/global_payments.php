<?php
require_once __DIR__ . '/core/init.php';

// Check if user is logged in and has admin privileges
adminAccessControl(['superadmin', 'admin']);

$db = \App\Core\App::database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }
    
    // Logic to record payment would go here
    $client = $_POST['client'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $currency = $_POST['currency'] ?? 'INR';
    $purpose = $_POST['purpose'] ?? '';
    
    $sql = "INSERT INTO global_payments (client, amount, currency, purpose, status, created_at) VALUES (?, ?, ?, ?, 'Completed', NOW())";
    $db->execute($sql, [$client, $amount, $currency, $purpose]);
    
    $message = "Payment recorded successfully!";
}

$payments = $db->fetchAll("SELECT * FROM global_payments ORDER BY created_at DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Global Payments & In-App Purchases</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'>
</head>
<body>
<div class='container py-4'>
    <h2>Global Payments & In-App Purchases</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method='post'>
        <?php echo getCsrfField(); ?>
        <div class='mb-3'>
            <label>Client/User</label>
            <input type='text' name='client' class='form-control' required>
        </div>
        <div class='mb-3'>
            <label>Amount</label>
            <input type='number' name='amount' class='form-control' required>
        </div>
        <div class='mb-3'>
            <label>Currency</label>
            <select name='currency' class='form-control'>
                <option>INR</option>
                <option>USD</option>
                <option>EUR</option>
                <option>GBP</option>
                <option>JPY</option>
            </select>
        </div>
        <div class='mb-3'>
            <label>Purpose</label>
            <input type='text' name='purpose' class='form-control' required>
        </div>
        <button class='btn btn-success'>Record Payment</button>
    </form>
    
    <table class='table table-bordered mt-4'>
        <thead>
            <tr>
                <th>Client</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payments)): ?>
                <?php foreach($payments as $p): ?>
                    <tr>
                        <td><?= h($p['client']) ?></td>
                        <td><?= h($p['amount']) ?></td>
                        <td><?= h($p['currency']) ?></td>
                        <td><?= h($p['purpose']) ?></td>
                        <td><?= h($p['status']) ?></td>
                        <td><?= $p['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No payments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <p class='mt-3'>*Ready for integration with Stripe, PayPal, Razorpay, or any global/local payment gateway. Supports in-app purchases and multi-currency payments.</p>
</div>
</body>
</html>

