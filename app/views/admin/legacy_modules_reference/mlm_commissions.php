<?php
require_once __DIR__ . '/core/init.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setSessionget_flash('error', "Invalid security token. Please refresh the page and try again.");
        header("Location: mlm_commissions.php");
        exit();
    }

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve_commission':
                $commissionId = $_POST['commission_id'];
                $db->execute("UPDATE mlm_commissions SET status = 'paid', paid_at = NOW() WHERE id = :id", ['id' => $commissionId]);
                setSessionget_flash('success', "Commission approved and marked as paid");
                header("Location: mlm_commissions.php");
                exit();
                break;

            case 'reject_commission':
                $commissionId = $_POST['commission_id'];
                $reason = $_POST['reason'];
                $db->execute("UPDATE mlm_commissions SET status = 'rejected', notes = :reason WHERE id = :id", [
                    'reason' => $reason,
                    'id' => $commissionId
                ]);
                setSessionget_flash('success', "Commission rejected");
                header("Location: mlm_commissions.php");
                exit();
                break;

            case 'bulk_approve':
                if (isset($_POST['commission_ids'])) {
                    $ids = $_POST['commission_ids'];
                    $placeholders = [];
                    $params = [];
                    foreach ($ids as $index => $id) {
                        $key = "id$index";
                        $placeholders[] = ":$key";
                        $params[$key] = $id;
                    }
                    $placeholders_str = implode(',', $placeholders);
                    $db->execute("UPDATE mlm_commissions SET status = 'paid', paid_at = NOW() WHERE id IN ($placeholders_str)", $params);
                    setSessionget_flash('success', count($ids) . " commissions approved");
                    header("Location: mlm_commissions.php");
                    exit();
                }
                break;

            case 'export':
                // Export functionality
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="commissions_' . date('Y-m-d') . '.csv"');

                $output = fopen('php://output', 'w');
                fputcsv($output, ['ID', 'Associate', 'User', 'Amount', 'Type', 'Level', 'Status', 'Date']);

                $commissions = $db->fetchAll("
                    SELECT mc.*, a.company_name, u.uname as user_name
                    FROM mlm_commissions mc
                    LEFT JOIN associates a ON mc.associate_id = a.id
                    LEFT JOIN user u ON mc.user_id = u.uid
                    ORDER BY mc.created_at DESC
                ");

                foreach ($commissions as $commission) {
                    fputcsv($output, [
                        $commission['id'],
                        $commission['company_name'],
                        $commission['user_name'],
                        $commission['commission_amount'],
                        $commission['commission_type'],
                        $commission['level'],
                        $commission['status'],
                        $commission['created_at']
                    ]);
                }

                fclose($output);
                exit();
                break;
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "mc.status = :status";
    $params['status'] = $status_filter;
}

if (!empty($type_filter)) {
    $where_conditions[] = "mc.commission_type = :type";
    $params['type'] = $type_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(mc.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(mc.created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get commissions
try {
    $sql = "
        SELECT mc.*, a.company_name, u.uname as user_name, u.uemail as email,
               sp.name as plan_name
        FROM mlm_commissions mc
        LEFT JOIN associates a ON mc.associate_id = a.id
        LEFT JOIN user u ON mc.user_id = u.uid
        LEFT JOIN mlm_commission_plans sp ON a.commission_plan_id = sp.id
        $where_clause
        ORDER BY mc.created_at DESC
    ";

    $commissions = $db->fetchAll($sql, $params);
} catch (Exception $e) {
    $commissions = [];
    $error = $e->getMessage();
}

// Get statistics
try {
    $stats = $db->fetch("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN commission_amount ELSE 0 END), 0) as pending_amount,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN commission_amount ELSE 0 END), 0) as paid_amount
        FROM mlm_commissions
    ");
} catch (Exception $e) {
    $stats = ['total' => 0, 'pending' => 0, 'paid' => 0, 'rejected' => 0, 'pending_amount' => 0, 'paid_amount' => 0];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLM Commissions Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="text-center mb-4">MLM Admin</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="mlm_dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mlm_associates.php">
                                <i class="fas fa-users"></i> Associates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="mlm_commissions.php">
                                <i class="fas fa-money-bill-wave"></i> Commissions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mlm_salary.php">
                                <i class="fas fa-hand-holding-usd"></i> Salary Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mlm_payouts.php">
                                <i class="fas fa-credit-card"></i> Payouts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mlm_reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mlm_settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="admin_dashboard.php">
                                <i class="fas fa-arrow-left"></i> Back to Main Admin
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">MLM Commissions Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <form method="POST" class="d-inline">
                                <?php echo getCsrfField(); ?>
                                <input type="hidden" name="action" value="export">
                                <button type="submit" class="btn btn-outline-success">
                                    <i class="fas fa-download"></i> Export CSV
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success = getSessionget_flash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo h($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error = getSessionget_flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo h($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Commissions</h5>
                                <h3><?php echo number_format($stats['total']); ?></h3>
                                <small>All time transactions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <h3>₹<?php echo number_format($stats['pending_amount'], 0); ?></h3>
                                <small><?php echo $stats['pending']; ?> transactions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Paid</h5>
                                <h3>₹<?php echo number_format($stats['paid_amount'], 0); ?></h3>
                                <small><?php echo $stats['paid']; ?> transactions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Rejected</h5>
                                <h3><?php echo number_format($stats['rejected']); ?></h3>
                                <small>Rejected commissions</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="type">
                                    <option value="">All Types</option>
                                    <option value="direct_commission" <?php echo $type_filter === 'direct_commission' ? 'selected' : ''; ?>>Direct</option>
                                    <option value="difference_commission" <?php echo $type_filter === 'difference_commission' ? 'selected' : ''; ?>>Difference</option>
                                    <option value="team_bonus" <?php echo $type_filter === 'team_bonus' ? 'selected' : ''; ?>>Team Bonus</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="date_from" value="<?php echo h($date_from); ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="date_to" value="<?php echo h($date_to); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="mlm_commissions.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <?php if (!empty($commissions)): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="POST" id="bulkActionForm">
                                <?php echo getCsrfField(); ?>
                                <input type="hidden" name="action" value="bulk_approve">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                            <label class="form-check-label" for="selectAll">
                                                Select All Pending Commissions
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve selected commissions?')">
                                            <i class="fas fa-check"></i> Approve Selected
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Commissions Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Commissions List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" class="form-check-input" id="mainCheckbox">
                                        </th>
                                        <th>ID</th>
                                        <th>Associate</th>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Level</th>
                                        <th>Sale Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($commissions)): ?>
                                        <?php foreach ($commissions as $commission): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($commission['status'] === 'pending'): ?>
                                                        <input type="checkbox" class="form-check-input commission-checkbox" name="commission_ids[]" value="<?php echo $commission['id']; ?>">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $commission['id']; ?></td>
                                                <td>
                                                    <strong><?php echo h($commission['company_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo h($commission['plan_name'] ?? 'N/A'); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo h($commission['user_name']); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo h($commission['email']); ?></small>
                                                </td>
                                                <td>
                                                    <strong class="text-success">₹<?php echo number_format($commission['commission_amount'], 0); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo ucwords(str_replace('_', ' ', $commission['commission_type'])); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">Level <?php echo $commission['level']; ?></span>
                                                </td>
                                                <td>₹<?php echo number_format($commission['sale_amount'], 0); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                                            echo match ($commission['status']) {
                                                                                'pending' => 'warning',
                                                                                'paid' => 'success',
                                                                                'rejected' => 'danger',
                                                                                default => 'secondary'
                                                                            };
                                                                            ?>">
                                                        <?php echo ucfirst($commission['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y H:i', strtotime($commission['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($commission['status'] === 'pending'): ?>
                                                            <button type="button" class="btn btn-outline-success" onclick="approveCommission(<?php echo $commission['id']; ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger" onclick="rejectCommission(<?php echo $commission['id']; ?>)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-primary" onclick="viewCommission(<?php echo $commission['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center">
                                                <p class="text-muted">No commissions found</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Approve Commission Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Commission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this commission and mark it as paid?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="approveForm">
                        <?php echo getCsrfField(); ?>
                        <input type="hidden" name="action" value="approve_commission">
                        <input type="hidden" name="commission_id" id="approveCommissionId">
                        <button type="submit" class="btn btn-success">Approve</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Commission Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Commission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="rejectForm">
                    <?php echo getCsrfField(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject_commission">
                        <input type="hidden" name="commission_id" id="rejectCommissionId">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Checkbox functionality
        document.getElementById('mainCheckbox').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.commission-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.commission-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            document.getElementById('mainCheckbox').checked = this.checked;
        });

        function approveCommission(commissionId) {
            document.getElementById('approveCommissionId').value = commissionId;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }

        function rejectCommission(commissionId) {
            document.getElementById('rejectCommissionId').value = commissionId;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        function viewCommission(commissionId) {
            // Implement view commission details
            window.open('mlm_commission_view.php?id=' + commissionId, '_blank');
        }
    </script>
</body>

</html>