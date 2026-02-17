<?php
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setSessionget_flash('error', "Invalid security token. Please refresh the page and try again.");
        header("Location: mlm_settings.php");
        exit();
    }

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_commission_rate':
                $level = $_POST['level'];
                $rate = $_POST['rate'];

                $db->execute("UPDATE commission_rates SET rate = :rate WHERE level = :level", ['rate' => $rate, 'level' => $level]);

                setSessionget_flash('success', "Commission rate updated successfully");
                header("Location: mlm_settings.php");
                exit();
                break;

            case 'add_commission_rate':
                $level = $_POST['level'];
                $rate = $_POST['rate'];
                $description = $_POST['description'];

                $db->execute("
                    INSERT INTO commission_rates (level, rate, description) 
                    VALUES (:level, :rate, :description)
                ", [
                    'level' => $level,
                    'rate' => $rate,
                    'description' => $description
                ]);

                setSessionget_flash('success', "Commission rate added successfully");
                header("Location: mlm_settings.php");
                exit();
                break;

            case 'update_salary_plan':
                $planId = $_POST['plan_id'];
                $name = $_POST['name'];
                $targetAmount = $_POST['target_amount'];
                $monthlySalary = $_POST['monthly_salary'];
                $durationMonths = $_POST['duration_months'];
                $minActivity = $_POST['min_monthly_activity'];
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                $db->execute("
                    UPDATE salary_plans 
                    SET name = ?, target_amount = ?, monthly_salary = ?, 
                        duration_months = ?, min_monthly_activity = ?, is_active = ?
                    WHERE id = ?
                ", [$name, $targetAmount, $monthlySalary, $durationMonths, $minActivity, $isActive, $planId]);

                setSessionget_flash('success', "Salary plan updated successfully");
                header("Location: mlm_settings.php");
                exit();
                break;

            case 'add_salary_plan':
                $name = $_POST['name'];
                $targetAmount = $_POST['target_amount'];
                $monthlySalary = $_POST['monthly_salary'];
                $durationMonths = $_POST['duration_months'];
                $minActivity = $_POST['min_monthly_activity'];
                $maxDays = $_POST['max_days'];

                $db->execute("
                    INSERT INTO salary_plans 
                    (name, target_amount, monthly_salary, duration_months, min_monthly_activity, max_days, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ", [$name, $targetAmount, $monthlySalary, $durationMonths, $minActivity, $maxDays]);

                setSessionget_flash('success', "Salary plan added successfully");
                header("Location: mlm_settings.php");
                exit();
                break;

            case 'update_system_settings':
                $minCommission = $_POST['min_commission_amount'];
                $maxCommission = $_POST['max_commission_amount'];
                $payoutThreshold = $_POST['payout_threshold'];
                $autoApprove = isset($_POST['auto_approve_commissions']) ? 1 : 0;

                // Update or insert system settings using individual queries since ON DUPLICATE KEY with multiple values can be tricky for simple execute
                $settings_to_update = [
                    'min_commission_amount' => $minCommission,
                    'max_commission_amount' => $maxCommission,
                    'payout_threshold' => $payoutThreshold,
                    'auto_approve_commissions' => $autoApprove
                ];

                foreach ($settings_to_update as $key => $value) {
                    $db->execute("
                        INSERT INTO mlm_settings (setting_key, setting_value) 
                        VALUES (:key, :value)
                        ON DUPLICATE KEY UPDATE setting_value = :update_value
                    ", [
                        'key' => $key,
                        'value' => $value,
                        'update_value' => $value
                    ]);
                }

                setSessionget_flash('success', "System settings updated successfully");
                header("Location: mlm_settings.php");
                exit();
                break;
        }
    }
}

// Get commission rates
try {
    $commissionRates = $db->fetchAll("
        SELECT * FROM commission_rates 
        ORDER BY level ASC
    ");
} catch (Exception $e) {
    $commissionRates = [];
}

// Get salary plans
try {
    $salaryPlans = $db->fetchAll("
        SELECT * FROM salary_plans 
        ORDER BY target_amount ASC
    ");
} catch (Exception $e) {
    $salaryPlans = [];
}

// Get system settings
try {
    $rows = $db->fetchAll("
        SELECT setting_key, setting_value 
        FROM mlm_settings
    ");
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $settings = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLM Settings - APS Dream Home</title>
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
                            <a class="nav-link" href="mlm_commissions.php">
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
                            <a class="nav-link active" href="mlm_settings.php">
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
                    <h1 class="h2">MLM System Settings</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary" onclick="exportSettings()">
                                <i class="fas fa-download"></i> Export Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success = getSessionget_flash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error = getSessionget_flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- System Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>System Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php echo getCsrfField(); ?>
                            <input type="hidden" name="action" value="update_system_settings">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="min_commission_amount" class="form-label">Min Commission Amount (₹)</label>
                                    <input type="number" class="form-control" id="min_commission_amount" name="min_commission_amount"
                                        value="<?php echo h($settings['min_commission_amount'] ?? 100); ?>" step="0.01" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="max_commission_amount" class="form-label">Max Commission Amount (₹)</label>
                                    <input type="number" class="form-control" id="max_commission_amount" name="max_commission_amount"
                                        value="<?php echo h($settings['max_commission_amount'] ?? 50000); ?>" step="0.01" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="payout_threshold" class="form-label">Payout Threshold (₹)</label>
                                    <input type="number" class="form-control" id="payout_threshold" name="payout_threshold"
                                        value="<?php echo h($settings['payout_threshold'] ?? 1000); ?>" step="0.01" required>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="auto_approve_commissions" name="auto_approve_commissions"
                                            <?php echo ($settings['auto_approve_commissions'] ?? 0) == 1 ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="auto_approve_commissions">
                                            Auto Approve Commissions
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update System Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Commission Rates -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-percentage me-2"></i>Commission Rates
                        </h5>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCommissionModal">
                            <i class="fas fa-plus"></i> Add Rate
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Rate (%)</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($commissionRates)): ?>
                                        <?php foreach ($commissionRates as $rate): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">Level <?php echo $rate['level']; ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo number_format($rate['rate'], 2); ?>%</strong>
                                                </td>
                                                <td><?php echo h($rate['description']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $rate['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $rate['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary" onclick="editCommissionRate(<?php echo $rate['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-<?php echo $rate['is_active'] ? 'warning' : 'success'; ?>"
                                                            onclick="toggleCommissionRate(<?php echo $rate['id']; ?>, <?php echo $rate['is_active'] ? 0 : 1; ?>)">
                                                            <i class="fas fa-<?php echo $rate['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <p class="text-muted">No commission rates configured</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Salary Plans -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-hand-holding-usd me-2"></i>Salary Plans
                        </h5>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSalaryPlanModal">
                            <i class="fas fa-plus"></i> Add Plan
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Plan Name</th>
                                        <th>Target Amount</th>
                                        <th>Monthly Salary</th>
                                        <th>Duration</th>
                                        <th>Min Activity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($salaryPlans)): ?>
                                        <?php foreach ($salaryPlans as $plan): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo h($plan['name']); ?></strong>
                                                </td>
                                                <td>
                                                    <strong>₹<?php echo number_format($plan['target_amount'], 0); ?></strong>
                                                </td>
                                                <td>
                                                    <strong class="text-success">₹<?php echo number_format($plan['monthly_salary'], 0); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo $plan['duration_months']; ?> months
                                                </td>
                                                <td>
                                                    ₹<?php echo number_format($plan['min_monthly_activity'], 0); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $plan['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary" onclick="editSalaryPlan(<?php echo $plan['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-<?php echo $plan['is_active'] ? 'warning' : 'success'; ?>"
                                                            onclick="toggleSalaryPlan(<?php echo $plan['id']; ?>, <?php echo $plan['is_active'] ? 0 : 1; ?>)">
                                                            <i class="fas fa-<?php echo $plan['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <p class="text-muted">No salary plans configured</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Backup & Restore -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-database me-2"></i>Backup & Restore
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Backup Settings</h6>
                                <p class="text-muted">Download a backup of all MLM settings and configurations.</p>
                                <button type="button" class="btn btn-outline-primary" onclick="backupSettings()">
                                    <i class="fas fa-download"></i> Download Backup
                                </button>
                            </div>
                            <div class="col-md-6">
                                <h6>Restore Settings</h6>
                                <p class="text-muted">Restore settings from a backup file.</p>
                                <button type="button" class="btn btn-outline-warning" onclick="document.getElementById('restoreFile').click()">
                                    <i class="fas fa-upload"></i> Restore Backup
                                </button>
                                <input type="file" id="restoreFile" style="display: none;" accept=".json" onchange="restoreSettings(this)">
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Commission Rate Modal -->
    <div class="modal fade" id="addCommissionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Commission Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?php echo getCsrfField(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_commission_rate">
                        <div class="mb-3">
                            <label for="level" class="form-label">Level</label>
                            <input type="number" class="form-control" id="level" name="level" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="rate" class="form-label">Commission Rate (%)</label>
                            <input type="number" class="form-control" id="rate" name="rate" step="0.01" min="0" max="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Salary Plan Modal -->
    <div class="modal fade" id="addSalaryPlanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Salary Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?php echo getCsrfField(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_salary_plan">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Plan Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="target_amount" class="form-label">Target Amount (₹)</label>
                                <input type="number" class="form-control" id="target_amount" name="target_amount" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label for="monthly_salary" class="form-label">Monthly Salary (₹)</label>
                                <input type="number" class="form-control" id="monthly_salary" name="monthly_salary" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label for="duration_months" class="form-label">Duration (months)</label>
                                <input type="number" class="form-control" id="duration_months" name="duration_months" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="min_monthly_activity" class="form-label">Min Monthly Activity (₹)</label>
                                <input type="number" class="form-control" id="min_monthly_activity" name="min_monthly_activity" step="0.01" value="100000" required>
                            </div>
                            <div class="col-md-6">
                                <label for="max_days" class="form-label">Max Days to Qualify</label>
                                <input type="number" class="form-control" id="max_days" name="max_days" min="1" value="90" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCommissionRate(id) {
            // Implement edit commission rate functionality
            console.log('Edit commission rate:', id);
        }

        function toggleCommissionRate(id, status) {
            if (confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this commission rate?')) {
                // Implement toggle functionality
                location.reload();
            }
        }

        function editSalaryPlan(id) {
            // Implement edit salary plan functionality
            console.log('Edit salary plan:', id);
        }

        function toggleSalaryPlan(id, status) {
            if (confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this salary plan?')) {
                // Implement toggle functionality
                location.reload();
            }
        }

        function exportSettings() {
            // Implement export settings functionality
            console.log('Export settings');
        }

        function backupSettings() {
            // Implement backup functionality
            console.log('Backup settings');
        }

        function restoreSettings(input) {
            if (input.files && input.files[0]) {
                // Implement restore functionality
                console.log('Restore settings:', input.files[0]);
            }
        }
    </script>
</body>

</html>