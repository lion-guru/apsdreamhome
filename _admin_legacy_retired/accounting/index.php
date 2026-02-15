<?php
// Accounting Dashboard Page
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /index.php');
    exit();
}
// Example: Fetch today's income and expenses (replace with real DB queries)
$today_income = 0; // TODO: Query from DB
$today_expenses = 0; // TODO: Query from DB
$recent_transactions = []; // TODO: Query from DB
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Dashboard</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <h1>Accounting Dashboard</h1>
    <section>
        <h2>Today's Summary</h2>
        <table border="1" cellpadding="8">
            <tr><th>Income</th><th>Expenses</th></tr>
            <tr><td>₹<?php echo $today_income; ?></td><td>₹<?php echo $today_expenses; ?></td></tr>
        </table>
    </section>
    <section>
        <h2>Quick Links</h2>
        <ul>
            <li><a href="/admin/add_income.php">Add/View Income</a></li>
            <li><a href="/admin/add_expenses.php">Add/View Expenses</a></li>
            <li><a href="/admin/add_transaction.php">Manage Transactions</a></li>
            <li><a href="/admin/finance_dashboard.php">Financial Reports</a></li>
            <li><a href="/admin/analytics_bi.php">Analytics & BI</a></li>
        </ul>
    </section>
    <section>
        <h2>Recent Transactions</h2>
        <table border="1" cellpadding="8">
            <tr><th>Date</th><th>Description</th><th>Amount</th><th>Type</th></tr>
            <?php if (empty($recent_transactions)): ?>
                <tr><td colspan="4">No recent transactions.</td></tr>
            <?php else: foreach ($recent_transactions as $txn): ?>
                <tr>
                    <td><?php echo htmlspecialchars($txn['date']); ?></td>
                    <td><?php echo htmlspecialchars($txn['description']); ?></td>
                    <td>₹<?php echo htmlspecialchars($txn['amount']); ?></td>
                    <td><?php echo htmlspecialchars($txn['type']); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </table>
    </section>
    <p>Welcome to the accounting section. Here you can manage financial records, view reports, and perform accounting operations.</p>
    <!-- Add your accounting features here -->
</body>
</html>