<?php
/**
 * Customer Ledger & Credit Management System
 * Part of Advanced Accounting System - Better than Khatabook
 */

session_start();
require_once '../includes/config.php';

// Check authentication and access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'] ?? 'admin';
$allowed_roles = ['admin', 'company_owner', 'accountant', 'finance_manager', 'sales_manager'];
if (!in_array($user_role, $allowed_roles)) {
    header("Location: index.php?error=access_denied");
    exit();
}

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_customer') {
        $customer_name = trim($_POST['customer_name']);
        $mobile = trim($_POST['mobile']);
        $email = trim($_POST['email']) ?: null;
        $address = trim($_POST['address']) ?: null;
        $gst_number = trim($_POST['gst_number']) ?: null;
        $pan_number = trim($_POST['pan_number']) ?: null;
        $credit_limit = floatval($_POST['credit_limit']);
        $credit_days = intval($_POST['credit_days']);
        $opening_balance = floatval($_POST['opening_balance']);
        
        $stmt = $conn->prepare("INSERT INTO customers_ledger (customer_name, mobile, email, address, gst_number, pan_number, credit_limit, credit_days, opening_balance, current_balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssiidd", $customer_name, $mobile, $email, $address, $gst_number, $pan_number, $credit_limit, $credit_days, $opening_balance, $opening_balance);
        
        if ($stmt->execute()) {
            $message = "Customer added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding customer: " . $stmt->error;
            $message_type = "danger";
        }
    }
    
    elseif ($action == 'record_payment') {
        $customer_id = intval($_POST['customer_id']);
        $amount = floatval($_POST['amount']);
        $payment_method = $_POST['payment_method'];
        $bank_account_id = $_POST['bank_account_id'] ?: null;
        $reference_number = trim($_POST['reference_number']) ?: null;
        $description = trim($_POST['description']);
        
        $payment_number = 'PAY' . date('Ymd') . sprintf('%04d', time() % 10000);
        
        $stmt = $conn->prepare("INSERT INTO accounting_payments (payment_number, payment_date, payment_type, party_type, party_id, amount, payment_method, bank_account_id, reference_number, description, created_by) VALUES (?, CURDATE(), 'received', 'customer', ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidssssi", $payment_number, $customer_id, $amount, $payment_method, $bank_account_id, $reference_number, $description, $_SESSION['admin_id']);
        
        if ($stmt->execute()) {
            // Update customer balance
            $update_stmt = $conn->prepare("UPDATE customers_ledger SET current_balance = current_balance - ?, total_payments = total_payments + ?, last_payment_date = CURDATE() WHERE id = ?");
            $update_stmt->bind_param("ddi", $amount, $amount, $customer_id);
            $update_stmt->execute();
            
            $message = "Payment recorded successfully!";
            $message_type = "success";
        } else {
            $message = "Error recording payment: " . $stmt->error;
            $message_type = "danger";
        }
    }
}

// Get customers list
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_conditions = ["1=1"];
$params = [];
$types = "";

if ($search) {
    $where_conditions[] = "(customer_name LIKE ? OR mobile LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= "sss";
}

$where_clause = implode(" AND ", $where_conditions);

$count_query = "SELECT COUNT(*) as total FROM customers_ledger WHERE $where_clause";
$count_stmt = $conn->prepare($count_query);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

$query = "SELECT * FROM customers_ledger WHERE $where_clause ORDER BY customer_name ASC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get summary statistics
$stats = [];
$stats_query = "SELECT 
    COUNT(*) as total_customers,
    SUM(CASE WHEN current_balance > 0 THEN current_balance ELSE 0 END) as total_receivables,
    SUM(CASE WHEN current_balance < 0 THEN ABS(current_balance) ELSE 0 END) as total_advances,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_customers
    FROM customers_ledger";
$stats_result = $conn->query($stats_query);
if ($stats_result) {
    $stats = $stats_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Ledger Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-container { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .stat-card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-3px); }
        .customer-card { border: none; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); transition: all 0.3s; }
        .customer-card:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .balance-positive { color: #dc3545; font-weight: bold; }
        .balance-negative { color: #28a745; font-weight: bold; }
        .balance-zero { color: #6c757d; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <div class="main-container p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">
                        <i class="fas fa-users text-primary me-2"></i>
                        Customer Ledger Management
                    </h1>
                    <p class="text-muted mb-0">Manage customer accounts, credit limits, and receivables</p>
                </div>
                <div>
                    <a href="accounting_dashboard.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="fas fa-plus me-2"></i>Add Customer
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h4 class="text-primary"><?php echo $stats['total_customers'] ?? 0; ?></h4>
                            <small class="text-muted">Total Customers</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-hand-holding-usd fa-2x text-warning mb-2"></i>
                            <h4 class="text-warning">₹<?php echo number_format($stats['total_receivables'] ?? 0, 2); ?></h4>
                            <small class="text-muted">Total Receivables</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                            <h4 class="text-success">₹<?php echo number_format($stats['total_advances'] ?? 0, 2); ?></h4>
                            <small class="text-muted">Customer Advances</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                            <h4 class="text-info"><?php echo $stats['active_customers'] ?? 0; ?></h4>
                            <small class="text-muted">Active Customers</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, mobile, or email">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                            <a href="?" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Customers List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Customers List
                        <span class="badge bg-primary ms-2"><?php echo $total_records; ?> total</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($customers)): ?>
                    <div class="row">
                        <?php foreach ($customers as $customer): ?>
                        <div class="col-xl-6 col-lg-6 mb-3">
                            <div class="card customer-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-user text-primary me-2"></i>
                                            <?php echo htmlspecialchars($customer['customer_name']); ?>
                                        </h6>
                                        <span class="badge bg-<?php echo $customer['status'] == 'active' ? 'success' : ($customer['status'] == 'blocked' ? 'danger' : 'secondary'); ?>">
                                            <?php echo ucfirst($customer['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Mobile:</small><br>
                                            <strong><?php echo htmlspecialchars($customer['mobile']); ?></strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Balance:</small><br>
                                            <span class="<?php echo $customer['current_balance'] > 0 ? 'balance-positive' : ($customer['current_balance'] < 0 ? 'balance-negative' : 'balance-zero'); ?>">
                                                ₹<?php echo number_format($customer['current_balance'], 2); ?>
                                                <?php if ($customer['current_balance'] > 0): ?>
                                                (Due)
                                                <?php elseif ($customer['current_balance'] < 0): ?>
                                                (Advance)
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <small class="text-muted">Credit Limit:</small><br>
                                            <span class="text-info">₹<?php echo number_format($customer['credit_limit'], 2); ?></span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Total Sales:</small><br>
                                            <span class="text-success">₹<?php echo number_format($customer['total_sales'], 2); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-success" onclick="recordPayment(<?php echo $customer['id']; ?>, '<?php echo addslashes($customer['customer_name']); ?>')">
                                            <i class="fas fa-money-bill me-1"></i>Payment
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewLedger(<?php echo $customer['id']; ?>)">
                                            <i class="fas fa-book me-1"></i>Ledger
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No customers found</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="fas fa-plus me-2"></i>Add Customer
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Add New Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_customer">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile Number *</label>
                                <input type="tel" class="form-control" name="mobile" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GST Number</label>
                                <input type="text" class="form-control" name="gst_number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Credit Limit</label>
                                <input type="number" step="0.01" class="form-control" name="credit_limit" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Opening Balance</label>
                                <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="record_payment">
                        <input type="hidden" name="customer_id" id="payment_customer_id">
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <input type="text" class="form-control" id="payment_customer_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount *</label>
                            <input type="number" step="0.01" class="form-control" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Online</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reference Number</label>
                            <input type="text" class="form-control" name="reference_number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function recordPayment(customerId, customerName) {
            document.getElementById('payment_customer_id').value = customerId;
            document.getElementById('payment_customer_name').value = customerName;
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        }
        
        function viewLedger(customerId) {
            window.location.href = `accounting_customer_ledger.php?customer_id=${customerId}`;
        }
    </script>
</body>
</html>