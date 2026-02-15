<?php
require_once __DIR__ . '/../core/init.php';

$page_title = "Accounting Dashboard";
include '../admin_header.php';

try {
    $db = \App\Core\App::database();
    $today = date('Y-m-d');

    // Fetch today's income from payments
    $incomeQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE DATE(payment_date) = ? AND status = 'completed'";
    $income_data = $db->fetchOne($incomeQuery, [$today]);
    $today_income = $income_data['total'] ?? 0;

    // Fetch today's expenses
    $expensesQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE DATE(expense_date) = ?";
    $expenses_data = $db->fetchOne($expensesQuery, [$today]);
    $today_expenses = $expenses_data['total'] ?? 0;

    // Fetch recent transactions (Income + Expenses)
    $recentQuery = "
        (SELECT payment_date as date, CONCAT('Income: ', payment_method) as description, amount, 'Income' as type
         FROM payments
         WHERE status = 'completed'
         ORDER BY payment_date DESC LIMIT 5)
        UNION ALL
        (SELECT expense_date as date, description, amount, 'Expense' as type
         FROM expenses
         ORDER BY expense_date DESC LIMIT 5)
        ORDER BY date DESC LIMIT 10";

    $recent_transactions = $db->fetchAll($recentQuery);

} catch (Exception $e) {
    error_log("Accounting Dashboard Error: " . $e->getMessage());
    $today_income = 0;
    $today_expenses = 0;
    $recent_transactions = [];
}
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <?php include '../admin_sidebar.php'; ?>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo h($mlSupport->translate('Accounting Dashboard')); ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="payments.php" class="btn btn-sm btn-outline-primary"><?php echo h($mlSupport->translate('Manage Payments')); ?></a>
                            <a href="emi.php" class="btn btn-sm btn-outline-secondary"><?php echo h($mlSupport->translate('EMI Plans')); ?></a>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100 bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-subtitle opacity-75"><?php echo h($mlSupport->translate('Today\'s Income')); ?></h6>
                                    <i class="fas fa-arrow-up fa-2x opacity-50"></i>
                                </div>
                                <h2 class="card-title mb-0 fw-bold">₹<?php echo h(number_format($today_income, 2)); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100 bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-subtitle opacity-75"><?php echo h($mlSupport->translate('Today\'s Expenses')); ?></h6>
                                    <i class="fas fa-arrow-down fa-2x opacity-50"></i>
                                </div>
                                <h2 class="card-title mb-0 fw-bold">₹<?php echo h(number_format($today_expenses, 2)); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Quick Links -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title mb-0 fw-bold text-primary"><?php echo h($mlSupport->translate('Quick Links')); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="payments.php" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave me-3 text-primary"></i>
                                        <?php echo h($mlSupport->translate('Manage Payments')); ?>
                                    </a>
                                    <a href="emi.php" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                        <i class="fas fa-calendar-alt me-3 text-success"></i>
                                        <?php echo h($mlSupport->translate('EMI Management')); ?>
                                    </a>
                                    <a href="../add_income.php" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                        <i class="fas fa-plus-circle me-3 text-info"></i>
                                        <?php echo h($mlSupport->translate('Add/View Income')); ?>
                                    </a>
                                    <a href="../add_expenses.php" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                        <i class="fas fa-minus-circle me-3 text-danger"></i>
                                        <?php echo h($mlSupport->translate('Add/View Expenses')); ?>
                                    </a>
                                    <a href="../finance_dashboard.php" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                        <i class="fas fa-chart-line me-3 text-warning"></i>
                                        <?php echo h($mlSupport->translate('Financial Reports')); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title mb-0 fw-bold text-primary"><?php echo h($mlSupport->translate('Recent Transactions')); ?></h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 px-4"><?php echo h($mlSupport->translate('Date')); ?></th>
                                                <th class="border-0"><?php echo h($mlSupport->translate('Description')); ?></th>
                                                <th class="border-0"><?php echo h($mlSupport->translate('Type')); ?></th>
                                                <th class="border-0 text-end px-4"><?php echo h($mlSupport->translate('Amount')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_transactions)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">
                                                        <?php echo h($mlSupport->translate('No recent transactions.')); ?>
                                                    </td>
                                                </tr>
                                            <?php else: foreach ($recent_transactions as $txn): ?>
                                                <tr>
                                                    <td class="px-4"><?php echo date('d M Y', strtotime($txn['date'])); ?></td>
                                                    <td><?php echo h($txn['description']); ?></td>
                                                    <td>
                                                        <span class="badge rounded-pill <?php echo $txn['type'] === 'Income' ? 'bg-success-light text-success' : 'bg-danger-light text-danger'; ?>">
                                                            <?php echo h($mlSupport->translate($txn['type'])); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end px-4 fw-bold <?php echo $txn['type'] === 'Income' ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo $txn['type'] === 'Income' ? '+' : '-'; ?>₹<?php echo h(number_format($txn['amount'], 2)); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<style>
.bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
.bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }
</style>

<?php include '../admin_footer.php'; ?>
