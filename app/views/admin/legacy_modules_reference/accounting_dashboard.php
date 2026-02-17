<?php
/**
 * Advanced Accounting Dashboard - Better than Khatabook
 * Complete Financial Management System - Standardized UI
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/performance_manager.php';

// Initialize Performance Manager
$perfManager = getPerformanceManager();

// Get user details
$user_id = getAuthUserId();
$user_role = getAuthSubRole() ?? 'admin';

// Check if user has accounting access
$allowed_roles = ['admin', 'company_owner', 'accountant', 'finance_manager', 'super_admin', 'superadmin'];
if (!hasRole('admin') || !in_array($user_role, $allowed_roles)) {
    header("Location: index.php?error=access_denied");
    exit();
}

// Get current financial year
$current_fy_data = $perfManager->executeCachedQuery("SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1", 3600);
$current_fy = $current_fy_data[0] ?? null;

// Get dashboard statistics
$stats = [];

// Total Cash in Hand
$cash_data = $perfManager->executeCachedQuery("SELECT SUM(current_balance) as total_cash FROM chart_of_accounts WHERE account_code = '1110'", 300);
$stats['cash_in_hand'] = $cash_data[0]['total_cash'] ?? 0;

// Total Bank Balance
$bank_data = $perfManager->executeCachedQuery("SELECT SUM(current_balance) as total_bank FROM bank_accounts WHERE status = 'active'", 300);
$stats['bank_balance'] = $bank_data[0]['total_bank'] ?? 0;

// Total Receivables (Customer Outstanding)
$receivables_data = $perfManager->executeCachedQuery("SELECT SUM(current_balance) as total_receivables FROM customers_ledger WHERE current_balance > 0", 300);
$stats['total_receivables'] = $receivables_data[0]['total_receivables'] ?? 0;

// Total Payables (Supplier Outstanding)
$payables_data = $perfManager->executeCachedQuery("SELECT SUM(current_balance) as total_payables FROM suppliers WHERE current_balance > 0", 300);
$stats['total_payables'] = $payables_data[0]['total_payables'] ?? 0;

// Monthly Income/Expense
$current_month = date('Y-m');
$income_data = $perfManager->executeCachedQuery("SELECT SUM(amount) as monthly_income FROM income_records WHERE DATE_FORMAT(income_date, '%Y-%m') = ? AND status = 'received'", 300, [$current_month]);
$stats['monthly_income'] = $income_data[0]['monthly_income'] ?? 0;

$expense_data = $perfManager->executeCachedQuery("SELECT SUM(amount) as monthly_expense FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = ? AND status = 'paid'", 300, [$current_month]);
$stats['monthly_expense'] = $expense_data[0]['monthly_expense'] ?? 0;

// Recent transactions for activity feed
$recent_transactions = $perfManager->executeCachedQuery("
    (SELECT 'income' as type, income_number as ref_number, amount, income_date as trans_date, description, customer_name as party_name
     FROM income_records WHERE status = 'received' ORDER BY income_date DESC LIMIT 3)
    UNION ALL
    (SELECT 'expense' as type, expense_number as ref_number, amount, expense_date as trans_date, description, vendor_name as party_name
     FROM expenses WHERE status = 'paid' ORDER BY expense_date DESC LIMIT 3)
    ORDER BY trans_date DESC LIMIT 10", 60);

// Get pending approvals count
$approval_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as pending FROM expenses WHERE status = 'pending'", 300);
$pending_approvals = $approval_data[0]['pending'] ?? 0;

// Get overdue invoices
$overdue_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as overdue FROM sales_invoices WHERE due_date < CURDATE() AND status NOT IN ('paid', 'cancelled')", 300);
$overdue_invoices = $overdue_data[0]['overdue'] ?? 0;

$page_title = 'Accounting Dashboard';
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Accounting Dashboard')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Accounting')); ?></li>
                    </ul>
                    <?php if ($current_fy): ?>
                    <div class="mt-2">
                        <span class="badge badge-info">
                            <?php echo h($mlSupport->translate('Financial Year')); ?>: <?php echo h($current_fy['year_name']); ?>
                            (<?php echo h(date('d M Y', strtotime($current_fy['start_date']))); ?> - <?php echo h(date('d M Y', strtotime($current_fy['end_date']))); ?>)
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-auto float-right ml-auto">
                    <button class="btn btn-primary btn-lg" onclick="showQuickTransaction()">
                        <i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Quick Transaction')); ?>
                    </button>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Financial Overview Stats -->
        <div class="row">
            <div class="col-xl-2 col-sm-4 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-success border-success">
                                <i class="fa fa-money"></i>
                            </span>
                            <div class="dash-count">
                                <h3>₹<?= h(number_format($stats['cash_in_hand'], 0)) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted"><?php echo h($mlSupport->translate('Cash in Hand')); ?></h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-sm-4 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-info border-info">
                                <i class="fa fa-university"></i>
                            </span>
                            <div class="dash-count">
                                <h3>₹<?= h(number_format($stats['bank_balance'], 0)) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted"><?php echo h($mlSupport->translate('Bank Balance')); ?></h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-info w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-sm-4 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-warning border-warning">
                                <i class="fa fa-hand-holding-usd"></i>
                            </span>
                            <div class="dash-count">
                                <h3>₹<?= h(number_format($stats['total_receivables'], 0)) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted"><?php echo h($mlSupport->translate('Receivables')); ?></h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-warning w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-sm-4 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-danger border-danger">
                                <i class="fa fa-credit-card"></i>
                            </span>
                            <div class="dash-count">
                                <h3>₹<?= h(number_format($stats['total_payables'], 0)) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted"><?php echo h($mlSupport->translate('Payables')); ?></h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-danger w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-sm-4 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-primary border-primary">
                                <i class="fa fa-arrow-up"></i>
                            </span>
                            <div class="dash-count">
                                <h3>₹<?= h(number_format($stats['monthly_income'], 0)) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted"><?php echo h($mlSupport->translate('Monthly Income')); ?></h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-sm-4 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-secondary border-secondary">
                                <i class="fa fa-arrow-down"></i>
                            </span>
                            <div class="dash-count">
                                <h3>₹<?= h(number_format($stats['monthly_expense'], 0)) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted"><?php echo h($mlSupport->translate('Monthly Expense')); ?></h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-secondary w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Accounting Modules -->
            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><?php echo h($mlSupport->translate('Accounting Modules')); ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Customer Management -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('customers')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <i class="fa fa-users fa-2x text-primary mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('Customer Ledger')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Manage customer accounts & receivables')); ?></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Supplier Management -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('suppliers')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <i class="fa fa-truck fa-2x text-success mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('Supplier Management')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Manage vendors & payables')); ?></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoicing -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('invoices')" style="cursor: pointer;">
                                    <div class="card-body text-center position-relative">
                                        <i class="fa fa-file-text fa-2x text-info mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('Invoicing')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Create & manage invoices')); ?></small>
                                        <?php if ($overdue_invoices > 0): ?>
                                        <span class="badge badge-danger position-absolute" style="top: 10px; right: 10px;"><?php echo h($overdue_invoices); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Expense Management -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('expenses')" style="cursor: pointer;">
                                    <div class="card-body text-center position-relative">
                                        <i class="fa fa-minus-square fa-2x text-warning mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('Expense Management')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Track business expenses')); ?></small>
                                        <?php if ($pending_approvals > 0): ?>
                                        <span class="badge badge-warning position-absolute" style="top: 10px; right: 10px;"><?php echo h($pending_approvals); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Income Tracking -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('income')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <i class="fa fa-line-chart fa-2x text-success mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('Income Tracking')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Record all income sources')); ?></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Management -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('banks')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <i class="fa fa-university fa-2x text-primary mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('Bank Management')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Manage bank accounts')); ?></small>
                                    </div>
                                </div>
                            </div>

                            <!-- GST Management -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('gst')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <i class="fa fa-percent fa-2x text-danger mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('GST Management')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Handle GST returns')); ?></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Reports -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('reports')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <i class="fa fa-bar-chart fa-2x text-info mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('Financial Reports')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('P&L, Balance Sheet')); ?></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Loan Management -->
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card flex-fill bg-white mb-3" onclick="openModule('loans')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <i class="fa fa-handshake-o fa-2x text-warning mb-2"></i>
                                        <h6><?php echo h($mlSupport->translate('Loan Management')); ?></h6>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Track loans & EMI')); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Activity -->
            <div class="col-xl-4 col-lg-5">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><?php echo h($mlSupport->translate('Quick Actions')); ?></h4>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-success btn-block mb-2" onclick="showQuickIncome()">
                            <i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Record Income')); ?>
                        </button>
                        <button class="btn btn-danger btn-block mb-2" onclick="showQuickExpense()">
                            <i class="fa fa-minus"></i> <?php echo h($mlSupport->translate('Record Expense')); ?>
                        </button>
                        <button class="btn btn-info btn-block mb-2" onclick="showQuickPayment()">
                            <i class="fa fa-exchange"></i> <?php echo h($mlSupport->translate('Record Payment')); ?>
                        </button>
                        <button class="btn btn-warning btn-block mb-2" onclick="openModule('invoices', 'create')">
                            <i class="fa fa-file-text"></i> <?php echo h($mlSupport->translate('Create Invoice')); ?>
                        </button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><?php echo h($mlSupport->translate('Recent Activity')); ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="activity-feed" style="max-height: 400px; overflow-y: auto;">
                            <?php if (!empty($recent_transactions)): ?>
                                <?php foreach ($recent_transactions as $transaction): ?>
                                <div class="feed-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="badge badge-<?php echo h($transaction['type']) == 'income' ? 'success' : 'danger'; ?> mb-1">
                                                <?php echo h($mlSupport->translate(ucfirst($transaction['type']))); ?>
                                            </span>
                                            <p class="mb-0 text-dark"><?php echo h($transaction['description']); ?></p>
                                            <?php if ($transaction['party_name']): ?>
                                            <small class="text-muted"><?php echo h($mlSupport->translate('Party')); ?>: <?php echo h($transaction['party_name']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-right">
                                            <h6 class="text-<?php echo h($transaction['type']) == 'income' ? 'success' : 'danger'; ?> mb-0">
                                                <?php echo h($transaction['type']) == 'income' ? '+' : '-'; ?>₹<?php echo h(number_format($transaction['amount'], 0)); ?>
                                            </h6>
                                            <small class="text-muted"><?php echo h(date('d M', strtotime($transaction['trans_date']))); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fa fa-history fa-2x mb-2"></i>
                                    <p><?php echo h($mlSupport->translate('No recent transactions')); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_specific_js = <<<'JS'
<script>
    // Module navigation
    function openModule(module, action = null) {
        let url = `accounting_${module}.php`;
        if (action) {
            url += `?action=${action}`;
        }
        window.location.href = url;
    }

    // Quick transaction functions
    function showQuickTransaction() {
        alert('Quick transaction modal will be implemented');
    }

    function showQuickIncome() {
        openModule('income', 'create');
    }

    function showQuickExpense() {
        openModule('expenses', 'create');
    }

    function showQuickPayment() {
        openModule('payments', 'create');
    }

    function showAccountingSettings() {
        openModule('settings');
    }
</script>
JS;
include 'admin_footer.php';
?>
