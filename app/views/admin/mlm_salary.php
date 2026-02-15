<?php
require_once __DIR__ . '/core/init.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setSessionget_flash('error', "Invalid security token. Please refresh the page and try again.");
        header("Location: mlm_salary.php");
        exit();
    }

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_contract_status':
                $contractId = $_POST['contract_id'];
                $status = $_POST['status'];
                $db->execute("UPDATE salary_contracts SET status = :status, updated_at = NOW() WHERE id = :id", [
                    'status' => $status,
                    'id' => $contractId
                ]);
                setSessionget_flash('success', "Contract status updated successfully");
                header("Location: mlm_salary.php");
                exit();
                break;

            case 'process_monthly_salary':
                // Process monthly salary for all active contracts
                $currentMonth = date('Y-m-01');
                $nextMonth = date('Y-m-01', strtotime('+1 month'));

                // Get active contracts
                $contracts = $db->fetchAll("
                    SELECT sc.*, sp.monthly_salary, a.user_id, u.uname as user_name, a.company_name
                    FROM salary_contracts sc
                    JOIN salary_plans sp ON sc.plan_id = sp.id
                    JOIN associates a ON sc.associate_id = a.id
                    JOIN user u ON a.user_id = u.uid
                    WHERE sc.status = 'active'
                    AND (sc.last_month_activity IS NULL OR sc.last_month_activity < CURDATE() - INTERVAL 1 MONTH)
                ");

                $processed = 0;
                foreach ($contracts as $contract) {
                    // Check minimum monthly activity
                    $business = $db->fetch("
                        SELECT COALESCE(SUM(sale_amount), 0) as total_business
                        FROM mlm_commissions 
                        WHERE associate_id = :associate_id 
                        AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    ", ['associate_id' => $contract['associate_id']]);

                    if ($business['total_business'] >= $contract['min_monthly_activity']) {
                        // Process salary payment
                        $db->execute("
                            INSERT INTO salary_payouts (contract_id, associate_id, amount, payout_date, status)
                            VALUES (:contract_id, :associate_id, :amount, CURDATE(), 'processed')
                        ", [
                            'contract_id' => $contract['id'],
                            'associate_id' => $contract['associate_id'],
                            'amount' => $contract['monthly_salary']
                        ]);

                        // Update contract
                        $db->execute("
                            UPDATE salary_contracts 
                            SET months_paid = months_paid + 1, 
                                last_month_activity = CURDATE(),
                                updated_at = NOW()
                            WHERE id = :id
                        ", ['id' => $contract['id']]);

                        $processed++;
                    }
                }

                setSessionget_flash('success', "Processed salary for $processed associates");
                header("Location: mlm_salary.php");
                exit();
                break;

            case 'upgrade_contract':
                $contractId = $_POST['contract_id'];
                $newPlanId = $_POST['new_plan_id'];

                // Get current contract
                $currentContract = $db->fetch("SELECT * FROM salary_contracts WHERE id = :id", ['id' => $contractId]);

                if ($currentContract) {
                    // Mark old contract as upgraded
                    $db->execute("UPDATE salary_contracts SET status = 'upgraded', updated_at = NOW() WHERE id = :id", ['id' => $contractId]);

                    // Create new contract
                    $db->execute("
                        INSERT INTO salary_contracts 
                        (associate_id, plan_id, qualified_at, salary_start, salary_end, status, upgrade_from_contract, remarks)
                        VALUES (:associate_id, :plan_id, NOW(), CURDATE(), DATE_ADD(CURDATE(), INTERVAL (SELECT duration_months FROM salary_plans WHERE id = :plan_id) MONTH), 'active', :old_contract_id, 'Upgraded by admin')
                    ", [
                        'associate_id' => $currentContract['associate_id'],
                        'plan_id' => $newPlanId,
                        'old_contract_id' => $contractId
                    ]);

                    setSessionget_flash('success', "Contract upgraded successfully");
                }
                header("Location: mlm_salary.php");
                exit();
                break;
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$plan_filter = $_GET['plan'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "sc.status = :status";
    $params['status'] = $status_filter;
}

if (!empty($plan_filter)) {
    $where_conditions[] = "sc.plan_id = :plan_id";
    $params['plan_id'] = $plan_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get salary contracts
try {
    $sql = "
        SELECT sc.*, sp.name as plan_name, sp.monthly_salary, sp.duration_months, sp.min_monthly_activity,
               a.company_name, u.uname as user_name, u.uemail as email,
               (SELECT SUM(commission_amount) FROM mlm_commissions WHERE associate_id = sc.associate_id AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as current_month_business
        FROM salary_contracts sc
        JOIN salary_plans sp ON sc.plan_id = sp.id
        JOIN associates a ON sc.associate_id = a.id
        JOIN user u ON a.user_id = u.uid
        $where_clause
        ORDER BY sc.created_at DESC
    ";

    $contracts = $db->fetchAll($sql, $params);
} catch (Exception $e) {
    $contracts = [];
    $error = $e->getMessage();
}

// Get salary plans for dropdown
try {
    $plans = $db->fetchAll("SELECT * FROM salary_plans WHERE is_active = 1 ORDER BY target_amount");
} catch (Exception $e) {
    $plans = [];
}

// Get statistics
try {
    $stats = $db->fetch("
        SELECT 
            COUNT(*) as total_contracts,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_contracts,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_contracts,
            COUNT(CASE WHEN status = 'upgraded' THEN 1 END) as upgraded_contracts,
            COALESCE(SUM(CASE WHEN status = 'active' THEN sp.monthly_salary ELSE 0 END), 0) as monthly_payout,
            COALESCE(AVG(CASE WHEN status = 'active' THEN months_paid ELSE 0 END), 0) as avg_months_paid
        FROM salary_contracts sc
        JOIN salary_plans sp ON sc.plan_id = sp.id
    ");
} catch (Exception $e) {
    $stats = ['total_contracts' => 0, 'active_contracts' => 0, 'completed_contracts' => 0, 'upgraded_contracts' => 0, 'monthly_payout' => 0, 'avg_months_paid' => 0];
}

?>

<?php
$page_title = "MLM Salary Management";
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">MLM Salary Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Salary Management</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <form method="POST" class="d-inline">
                        <?php echo getCsrfField(); ?>
                        <input type="hidden" name="action" value="process_monthly_salary">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Process monthly salary for all eligible associates?')">
                            <i class="fas fa-calculator"></i> Process Monthly Salary
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
        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-primary">
                                <i class="fas fa-file-contract"></i>
                            </span>
                            <div class="dash-count">
                                <div class="dash-title">Total Contracts</div>
                                <div class="dash-counts">
                                    <h4><?php echo number_format($stats['total_contracts']); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="dash-count">
                                <div class="dash-title">Active Contracts</div>
                                <div class="dash-counts">
                                    <h4><?php echo number_format($stats['active_contracts']); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-info">
                                <i class="fas fa-rupee-sign"></i>
                            </span>
                            <div class="dash-count">
                                <div class="dash-title">Monthly Payout</div>
                                <div class="dash-counts">
                                    <h4>₹<?php echo number_format($stats['monthly_payout'], 0); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-warning">
                                <i class="fas fa-calendar-check"></i>
                            </span>
                            <div class="dash-count">
                                <div class="dash-title">Avg Months Paid</div>
                                <div class="dash-counts">
                                    <h4><?php echo number_format($stats['avg_months_paid'], 1); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="upgraded" <?php echo $status_filter === 'upgraded' ? 'selected' : ''; ?>>Upgraded</option>
                            <option value="paused" <?php echo $status_filter === 'paused' ? 'selected' : ''; ?>>Paused</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Plan</label>
                        <select class="form-select" name="plan">
                            <option value="">All Plans</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?php echo (int)$plan['id']; ?>" <?php echo $plan_filter == $plan['id'] ? 'selected' : ''; ?>>
                                    <?php echo h($plan['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="mlm_salary.php" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Salary Plans Overview -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Available Salary Plans</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($plans as $plan): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-none border">
                                <div class="card-body">
                                    <h6 class="card-title text-primary"><?php echo h($plan['name']); ?></h6>
                                    <p class="card-text mb-0">
                                        <strong>Target:</strong> ₹<?php echo number_format($plan['target_amount'], 0); ?><br>
                                        <strong>Salary:</strong> ₹<?php echo number_format($plan['monthly_salary'], 0); ?>/month<br>
                                        <strong>Duration:</strong> <?php echo (int)$plan['duration_months']; ?> months<br>
                                        <strong>Min Activity:</strong> ₹<?php echo number_format($plan['min_monthly_activity'], 0); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Salary Contracts Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Salary Contracts</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0 datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Associate</th>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Monthly Salary</th>
                                <th>Duration</th>
                                <th>Months Paid</th>
                                <th>Current Month Business</th>
                                <th>Status</th>
                                <th>Qualified</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($contracts)): ?>
                                <?php foreach ($contracts as $contract): ?>
                                    <tr>
                                        <td><?php echo h($contract['id']); ?></td>
                                        <td>
                                            <strong><?php echo h($contract['company_name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo h($contract['user_name']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo h($contract['email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-light"><?php echo h($contract['plan_name']); ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-success">₹<?php echo number_format($contract['monthly_salary'], 0); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo h($contract['months_paid']); ?> / <?php echo h($contract['duration_months']); ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($contract['salary_start'])); ?> -
                                                <?php echo date('M j, Y', strtotime($contract['salary_end'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 10px;">
                                                <?php
                                                $percentage = ($contract['months_paid'] / $contract['duration_months']) * 100;
                                                ?>
                                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                            <small><?php echo round($percentage); ?>% Completed</small>
                                        </td>
                                        <td>
                                            <?php
                                            $minActivity = $contract['min_monthly_activity'];
                                            $currentBusiness = $contract['current_month_business'] ?? 0;
                                            $meetsRequirement = $currentBusiness >= $minActivity;
                                            ?>
                                            <strong class="<?php echo $meetsRequirement ? 'text-success' : 'text-danger'; ?>">
                                                ₹<?php echo number_format($currentBusiness, 0); ?>
                                            </strong>
                                            <br>
                                            <small class="text-muted">Min: ₹<?php echo number_format($minActivity, 0); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php
                                                                    echo match ($contract['status']) {
                                                                        'active' => 'success',
                                                                        'completed' => 'primary',
                                                                        'upgraded' => 'info',
                                                                        'paused' => 'warning',
                                                                        'cancelled' => 'danger',
                                                                        default => 'secondary'
                                                                    };
                                                                    ?>-light">
                                                <?php echo ucfirst(h($contract['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($contract['qualified_at'])); ?></td>
                                        <td class="text-end">
                                            <div class="actions">
                                                <?php if ($contract['status'] === 'active'): ?>
                                                    <button type="button" class="btn btn-sm bg-warning-light me-2" onclick="pauseContract(<?php echo $contract['id']; ?>)">
                                                        <i class="fas fa-pause"></i> Pause
                                                    </button>
                                                    <button type="button" class="btn btn-sm bg-info-light me-2" onclick="upgradeContract(<?php echo $contract['id']; ?>)">
                                                        <i class="fas fa-arrow-up"></i> Upgrade
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm bg-primary-light" onclick="viewContract(<?php echo $contract['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center">No salary contracts found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pause Contract Modal -->
<div class="modal fade" id="pauseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pause Contract</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="pauseForm">
                <?php echo getCsrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_contract_status">
                    <input type="hidden" name="contract_id" id="pauseContractId">
                    <input type="hidden" name="status" value="paused">
                    <p>Are you sure you want to pause this contract? Salary payments will be stopped until resumed.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Pause Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upgrade Contract Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upgrade Contract</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="upgradeForm">
                <?php echo getCsrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="action" value="upgrade_contract">
                    <input type="hidden" name="contract_id" id="upgradeContractId">
                    <div class="mb-3">
                        <label for="new_plan_id" class="form-label">Select New Plan</label>
                        <select class="form-select" id="new_plan_id" name="new_plan_id" required>
                            <option value="">Choose a plan...</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?php echo (int)$plan['id']; ?>">
                                    <?php echo h($plan['name']); ?> - ₹<?php echo number_format($plan['monthly_salary'], 0); ?>/month
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p class="text-muted">Note: This will create a new contract and mark the current one as upgraded.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Upgrade Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

<script>
    function pauseContract(contractId) {
        document.getElementById('pauseContractId').value = contractId;
        new bootstrap.Modal(document.getElementById('pauseModal')).show();
    }

    function upgradeContract(contractId) {
        document.getElementById('upgradeContractId').value = contractId;
        new bootstrap.Modal(document.getElementById('upgradeModal')).show();
    }

    function viewContract(contractId) {
        window.open('mlm_contract_view.php?id=' + contractId, '_blank');
    }
</script>
</body>

</html>