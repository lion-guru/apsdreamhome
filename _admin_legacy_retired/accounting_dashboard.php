<?php
/**
 * Advanced Accounting Dashboard - Better than Khatabook
 * Complete Financial Management System
 */

session_start();
require_once '../includes/config.php';

// Check if user is logged in and has proper access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get user details
$user_id = $_SESSION['admin_id'];
$user_role = $_SESSION['role'] ?? 'admin';

// Check if user has accounting access
$allowed_roles = ['admin', 'company_owner', 'accountant', 'finance_manager'];
if (!in_array($user_role, $allowed_roles)) {
    header("Location: index.php?error=access_denied");
    exit();
}

// Get current financial year
$current_fy_query = "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1";
$current_fy_result = $conn->query($current_fy_query);
$current_fy = $current_fy_result ? $current_fy_result->fetch_assoc() : null;

// Get dashboard statistics
$stats = [];

// Total Cash in Hand
$cash_query = "SELECT SUM(current_balance) as total_cash FROM chart_of_accounts WHERE account_code = '1110'";
$cash_result = $conn->query($cash_query);
$stats['cash_in_hand'] = $cash_result ? ($cash_result->fetch_assoc()['total_cash'] ?? 0) : 0;

// Total Bank Balance
$bank_query = "SELECT SUM(current_balance) as total_bank FROM bank_accounts WHERE status = 'active'";
$bank_result = $conn->query($bank_query);
$stats['bank_balance'] = $bank_result ? ($bank_result->fetch_assoc()['total_bank'] ?? 0) : 0;

// Total Receivables (Customer Outstanding)
$receivables_query = "SELECT SUM(current_balance) as total_receivables FROM customers_ledger WHERE current_balance > 0";
$receivables_result = $conn->query($receivables_query);
$stats['total_receivables'] = $receivables_result ? ($receivables_result->fetch_assoc()['total_receivables'] ?? 0) : 0;

// Total Payables (Supplier Outstanding)
$payables_query = "SELECT SUM(current_balance) as total_payables FROM suppliers WHERE current_balance > 0";
$payables_result = $conn->query($payables_query);
$stats['total_payables'] = $payables_result ? ($payables_result->fetch_assoc()['total_payables'] ?? 0) : 0;

// Monthly Income/Expense
$current_month = date('Y-m');
$income_query = "SELECT SUM(amount) as monthly_income FROM income_records WHERE DATE_FORMAT(income_date, '%Y-%m') = '$current_month' AND status = 'received'";
$income_result = $conn->query($income_query);
$stats['monthly_income'] = $income_result ? ($income_result->fetch_assoc()['monthly_income'] ?? 0) : 0;

$expense_query = "SELECT SUM(amount) as monthly_expense FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = '$current_month' AND status = 'paid'";
$expense_result = $conn->query($expense_query);
$stats['monthly_expense'] = $expense_result ? ($expense_result->fetch_assoc()['monthly_expense'] ?? 0) : 0;

// Recent transactions for activity feed
$recent_transactions = [];
$recent_query = "
    (SELECT 'income' as type, income_number as ref_number, amount, income_date as trans_date, description, customer_name as party_name 
     FROM income_records WHERE status = 'received' ORDER BY income_date DESC LIMIT 3)
    UNION ALL
    (SELECT 'expense' as type, expense_number as ref_number, amount, expense_date as trans_date, description, vendor_name as party_name 
     FROM expenses WHERE status = 'paid' ORDER BY expense_date DESC LIMIT 3)
    ORDER BY trans_date DESC LIMIT 10";
$recent_result = $conn->query($recent_query);
if ($recent_result) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_transactions[] = $row;
    }
}

// Get pending approvals count
$pending_approvals = 0;
$approval_query = "SELECT COUNT(*) as pending FROM expenses WHERE status = 'pending'";
$approval_result = $conn->query($approval_query);
if ($approval_result) {
    $pending_approvals = $approval_result->fetch_assoc()['pending'] ?? 0;
}

// Get overdue invoices
$overdue_invoices = 0;
$overdue_query = "SELECT COUNT(*) as overdue FROM sales_invoices WHERE due_date < CURDATE() AND status NOT IN ('paid', 'cancelled')";
$overdue_result = $conn->query($overdue_query);
if ($overdue_result) {
    $overdue_invoices = $overdue_result->fetch_assoc()['overdue'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.min.js" rel="preload" as="script">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .dashboard-container { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .stat-card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .bg-gradient-success { background: linear-gradient(45deg, #28a745, #20c997); }
        .bg-gradient-info { background: linear-gradient(45deg, #17a2b8, #6f42c1); }
        .bg-gradient-warning { background: linear-gradient(45deg, #ffc107, #fd7e14); }
        .bg-gradient-danger { background: linear-gradient(45deg, #dc3545, #e83e8c); }
        .bg-gradient-primary { background: linear-gradient(45deg, #007bff, #6610f2); }
        .bg-gradient-secondary { background: linear-gradient(45deg, #6c757d, #495057); }
        .quick-action-btn { border-radius: 10px; padding: 15px; margin-bottom: 10px; transition: all 0.3s; }
        .quick-action-btn:hover { transform: translateY(-2px); }
        .activity-item { border-left: 4px solid #007bff; padding: 10px 15px; margin-bottom: 10px; background: #f8f9fa; border-radius: 0 10px 10px 0; }
        .activity-item.income { border-left-color: #28a745; }
        .activity-item.expense { border-left-color: #dc3545; }
        .chart-container { position: relative; height: 300px; }
        .module-card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: all 0.3s; cursor: pointer; }
        .module-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .notification-badge { position: absolute; top: -5px; right: -5px; }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-calculator me-2"></i>
                Accounting Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php"><i class="fas fa-home me-2"></i>Admin Dashboard</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showAccountingSettings()"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">
        <div class="dashboard-container p-4">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <h1 class="mb-1">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Accounting Dashboard
                    </h1>
                    <p class="text-muted mb-0">Complete Financial Management System</p>
                    <?php if ($current_fy): ?>
                    <small class="badge bg-info">
                        Financial Year: <?php echo htmlspecialchars($current_fy['year_name']); ?>
                        (<?php echo date('d M Y', strtotime($current_fy['start_date'])); ?> - <?php echo date('d M Y', strtotime($current_fy['end_date'])); ?>)
                    </small>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary btn-lg" onclick="showQuickTransaction()">
                        <i class="fas fa-plus me-2"></i>Quick Transaction
                    </button>
                </div>
            </div>

            <!-- Financial Overview Stats -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-gradient-success text-white mx-auto mb-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h5 class="card-title text-success">Cash in Hand</h5>
                            <h3 class="text-dark">₹<?php echo number_format($stats['cash_in_hand'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-gradient-info text-white mx-auto mb-3">
                                <i class="fas fa-university"></i>
                            </div>
                            <h5 class="card-title text-info">Bank Balance</h5>
                            <h3 class="text-dark">₹<?php echo number_format($stats['bank_balance'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-gradient-warning text-white mx-auto mb-3">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <h5 class="card-title text-warning">Receivables</h5>
                            <h3 class="text-dark">₹<?php echo number_format($stats['total_receivables'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-gradient-danger text-white mx-auto mb-3">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h5 class="card-title text-danger">Payables</h5>
                            <h3 class="text-dark">₹<?php echo number_format($stats['total_payables'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-gradient-primary text-white mx-auto mb-3">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <h5 class="card-title text-primary">Monthly Income</h5>
                            <h3 class="text-dark">₹<?php echo number_format($stats['monthly_income'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-gradient-secondary text-white mx-auto mb-3">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <h5 class="card-title text-secondary">Monthly Expense</h5>
                            <h3 class="text-dark">₹<?php echo number_format($stats['monthly_expense'], 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Accounting Modules -->
                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-th-large me-2"></i>
                                Accounting Modules
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Customer Management -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('customers')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                            <h6>Customer Ledger</h6>
                                            <small class="text-muted">Manage customer accounts & receivables</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Supplier Management -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('suppliers')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-truck fa-2x text-success mb-2"></i>
                                            <h6>Supplier Management</h6>
                                            <small class="text-muted">Manage vendors & payables</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Invoicing -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('invoices')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-invoice fa-2x text-info mb-2"></i>
                                            <h6>Invoicing</h6>
                                            <small class="text-muted">Create & manage invoices</small>
                                            <?php if ($overdue_invoices > 0): ?>
                                            <span class="badge bg-danger notification-badge"><?php echo $overdue_invoices; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Expense Management -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('expenses')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-receipt fa-2x text-warning mb-2"></i>
                                            <h6>Expense Management</h6>
                                            <small class="text-muted">Track business expenses</small>
                                            <?php if ($pending_approvals > 0): ?>
                                            <span class="badge bg-warning notification-badge"><?php echo $pending_approvals; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Income Tracking -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('income')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                                            <h6>Income Tracking</h6>
                                            <small class="text-muted">Record all income sources</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Bank Management -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('banks')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-university fa-2x text-primary mb-2"></i>
                                            <h6>Bank Management</h6>
                                            <small class="text-muted">Manage bank accounts & reconciliation</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- GST Management -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('gst')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-percentage fa-2x text-danger mb-2"></i>
                                            <h6>GST Management</h6>
                                            <small class="text-muted">Handle GST calculations & returns</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Financial Reports -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('reports')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                            <h6>Financial Reports</h6>
                                            <small class="text-muted">P&L, Balance Sheet, Cash Flow</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Loan Management -->
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card module-card" onclick="openModule('loans')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-hand-holding-usd fa-2x text-warning mb-2"></i>
                                            <h6>Loan Management</h6>
                                            <small class="text-muted">Track loans & EMI schedules</small>
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
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success w-100 quick-action-btn" onclick="showQuickIncome()">
                                <i class="fas fa-plus me-2"></i>Record Income
                            </button>
                            <button class="btn btn-danger w-100 quick-action-btn" onclick="showQuickExpense()">
                                <i class="fas fa-minus me-2"></i>Record Expense
                            </button>
                            <button class="btn btn-info w-100 quick-action-btn" onclick="showQuickPayment()">
                                <i class="fas fa-exchange-alt me-2"></i>Record Payment
                            </button>
                            <button class="btn btn-warning w-100 quick-action-btn" onclick="openModule('invoices', 'create')">
                                <i class="fas fa-file-invoice me-2"></i>Create Invoice
                            </button>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Recent Activity
                            </h6>
                        </div>
                        <div class="card-body">
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php if (!empty($recent_transactions)): ?>
                                    <?php foreach ($recent_transactions as $transaction): ?>
                                    <div class="activity-item <?php echo $transaction['type']; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?php echo ucfirst($transaction['type']); ?></strong>
                                                <br>
                                                <small><?php echo htmlspecialchars($transaction['description']); ?></small>
                                                <?php if ($transaction['party_name']): ?>
                                                <br><small class="text-muted">To: <?php echo htmlspecialchars($transaction['party_name']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <strong class="text-<?php echo $transaction['type'] == 'income' ? 'success' : 'danger'; ?>">
                                                    <?php echo $transaction['type'] == 'income' ? '+' : '-'; ?>₹<?php echo number_format($transaction['amount'], 2); ?>
                                                </strong>
                                                <br>
                                                <small class="text-muted"><?php echo date('d M', strtotime($transaction['trans_date'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-history fa-2x mb-2"></i>
                                        <p>No recent transactions</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.min.js"></script>
    
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
            // Open quick transaction modal (to be implemented)
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
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>