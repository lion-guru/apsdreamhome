<?php
/**
 * Salary Income Plan Management System
 * APS Dream Homes - Based on Diwali Dhamaka Plan
 */

require_once 'admin-functions.php';
use App\Core\Database;

// Check admin access
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

$db = \App\Core\App::database();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'apply_salary_plan') {
        $associate_id = intval($_POST['associate_id']);
        $plan_id = intval($_POST['plan_id']);
        $duration_months = intval($_POST['duration_months']);

        try {
            // Check if associate already has an active plan
            $existing_plan = $db->fetch("SELECT id FROM salary_contracts WHERE associate_id = :associate_id AND status = 'active'", ['associate_id' => $associate_id]);

            if ($existing_plan) {
                $message = "Associate already has an active salary plan!";
                $message_type = "warning";
            } else {
                $sql = "INSERT INTO salary_contracts (associate_id, plan_id, qualified_at, salary_start, salary_end, status)
                        VALUES (:associate_id, :plan_id, NOW(), CURDATE(), DATE_ADD(CURDATE(), INTERVAL :months MONTH), 'active')";
                $db->execute($sql, [
                    'associate_id' => $associate_id,
                    'plan_id' => $plan_id,
                    'months' => $duration_months
                ]);

                $message = "Salary income plan activated successfully!";
                $message_type = "success";
            }
        } catch (Exception $e) {
            $message = "Error activating salary plan: " . $e->getMessage();
            $message_type = "danger";
        }
    }

    elseif ($action == 'process_salary_payment') {
        $contract_id = intval($_POST['plan_id']); // Using plan_id as contract_id from form
        $associate_id = intval($_POST['associate_id']);
        $salary_amount = floatval($_POST['salary_amount']);

        try {
            $db->beginTransaction();

            $db->execute("INSERT INTO salary_payouts (contract_id, associate_id, amount, payout_date, status) VALUES (:contract_id, :associate_id, :amount, CURDATE(), 'processed')", [
                'contract_id' => $contract_id,
                'associate_id' => $associate_id,
                'amount' => $salary_amount
            ]);

            // Update plan payment count
            $db->execute("UPDATE salary_contracts SET months_paid = months_paid + 1 WHERE id = :id", ['id' => $contract_id]);

            $db->commit();
            $message = "Salary payment processed successfully!";
            $message_type = "success";
        } catch (Exception $e) {
            $db->rollBack();
            $message = "Error processing salary payment: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Get all associates for dropdown
try {
    $associates = $db->fetchAll("SELECT a.id, u.uname as full_name, u.uphone as mobile, a.current_level, a.total_business
                                FROM associates a
                                JOIN user u ON a.user_id = u.uid
                                WHERE a.status = 'active'
                                ORDER BY u.uname");
} catch (Exception $e) {
    $associates = [];
}

// Get active salary plans
try {
    $active_plans = $db->fetchAll("SELECT sc.*, u.uname as full_name, u.uphone as mobile, a.current_level, sp.target_amount, sp.monthly_salary, sp.duration_months
                                    FROM salary_contracts sc
                                    JOIN associates a ON sc.associate_id = a.id
                                    JOIN user u ON a.user_id = u.uid
                                    JOIN salary_plans sp ON sc.plan_id = sp.id
                                    WHERE sc.status = 'active'
                                    ORDER BY sc.created_at DESC");
} catch (Exception $e) {
    $active_plans = [];
}

// Get pending salary payments
try {
    $pending_payments = $db->fetchAll("SELECT sc.*, u.uname as full_name, u.uphone as mobile, sp.duration_months,
                                      DATEDIFF(sc.salary_end, CURDATE()) as days_remaining,
                                      (sp.duration_months - sc.months_paid) as pending_months,
                                      sp.monthly_salary
                                      FROM salary_contracts sc
                                      JOIN associates a ON sc.associate_id = a.id
                                      JOIN user u ON a.user_id = u.uid
                                      JOIN salary_plans sp ON sc.plan_id = sp.id
                                      WHERE sc.status = 'active'
                                      AND sc.months_paid < sp.duration_months
                                      ORDER BY sc.salary_start ASC");
} catch (Exception $e) {
    $pending_payments = [];
}

// Salary plan templates
try {
    $rows = $db->fetchAll("SELECT * FROM salary_plans WHERE is_active = 1 ORDER BY target_amount");
    $salary_plan_templates = [];
    foreach ($rows as $row) {
        $salary_plan_templates[] = [
            'id' => $row['id'],
            'target' => $row['target_amount'],
            'duration_days' => $row['max_days'],
            'salary' => $row['monthly_salary'],
            'duration_months' => $row['duration_months'],
            'description' => $row['name']
        ];
    }
} catch (Exception $e) {
    $salary_plan_templates = [];
}

// Get statistics
try {
    $stats = $db->fetch("SELECT
                        COUNT(*) as total_plans,
                        SUM(CASE WHEN sc.status = 'active' THEN 1 ELSE 0 END) as active_plans,
                        SUM(sp.monthly_salary) as total_monthly_commitment,
                        SUM(sp.target_amount) as total_target_business
                        FROM salary_contracts sc
                        JOIN salary_plans sp ON sc.plan_id = sp.id");

    $paid_stats = $db->fetch("SELECT SUM(amount) as total_paid FROM salary_payouts WHERE status = 'processed'");
    $stats['total_paid'] = $paid_stats['total_paid'] ?? 0;
} catch (Exception $e) {
    $stats = ['total_plans' => 0, 'active_plans' => 0, 'total_paid' => 0, 'total_target_business' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Income Plan Management - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-container { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .header-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
        }
        .diwali-theme {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            position: relative;
            overflow: hidden;
        }
        .diwali-theme::before {
            content: '🪔';
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 2rem;
            opacity: 0.7;
        }
        .plan-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .target-badge {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .salary-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <div class="main-container p-4">
            <!-- Header -->
            <div class="header-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">
                            <i class="fas fa-coins me-2"></i>
                            Salary Income Plan Management
                        </h1>
                        <p class="mb-0">Diwali Dhamaka Salary Income Plans - Motivate Your Associates</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="index.php" class="btn btn-light me-2">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#newPlanModal">
                            <i class="fas fa-plus me-2"></i>New Plan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mt-3" role="alert">
                <?php echo h($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Diwali Theme Section -->
            <div class="diwali-theme">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">🎉 APS DREAM HOMES PRESENTS 🎉</h2>
                        <h1 class="mb-2">DIWALI DHAMAKA</h1>
                        <h3 class="mb-3">SALARY INCOME PLAN</h3>
                        <p class="mb-0">Achieve business targets and get guaranteed monthly salary for months!</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="bg-white text-dark p-3 rounded">
                            <h4 class="text-primary mb-1"><?php echo $stats['active_plans'] ?? 0; ?></h4>
                            <small>Active Plans</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                            <h4 class="text-primary"><?php echo $stats['total_plans'] ?? 0; ?></h4>
                            <small class="text-muted">Total Plans Created</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-play fa-2x text-success mb-2"></i>
                            <h4 class="text-success"><?php echo $stats['active_plans'] ?? 0; ?></h4>
                            <small class="text-muted">Active Plans</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-rupee-sign fa-2x text-warning mb-2"></i>
                            <h4 class="text-warning">₹<?php echo number_format($stats['total_paid'] ?? 0); ?></h4>
                            <small class="text-muted">Total Salary Paid</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-target fa-2x text-info mb-2"></i>
                            <h4 class="text-info">₹<?php echo number_format($stats['total_target_business'] ?? 0); ?></h4>
                            <small class="text-muted">Target Business</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Salary Plan Templates -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-star me-2"></i>
                                Available Salary Plans
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($salary_plan_templates as $template): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card plan-card">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <span class="target-badge">₹<?php echo number_format($template['target']); ?></span>
                                            </div>
                                            <h6><?php echo $template['description']; ?></h6>
                                            <div class="row mt-3">
                                                <div class="col-6">
                                                    <strong><?php echo $template['duration_days']; ?> Days</strong><br>
                                                    <small class="text-muted">Target Duration</small>
                                                </div>
                                                <div class="col-6">
                                                    <strong>₹<?php echo number_format($template['salary']); ?></strong><br>
                                                    <small class="text-muted">Monthly Salary</small>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="salary-badge">
                                                    <?php echo $template['duration_months']; ?> Months Duration
                                                </div>
                                            </div>
                                            <button class="btn btn-primary btn-sm mt-3"
                                                    onclick="selectPlan(<?php echo h(json_encode($template)); ?>)">
                                                <i class="fas fa-check me-1"></i>Select Plan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Plans -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Active Salary Plans
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($active_plans)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Associate</th>
                                            <th>Target</th>
                                            <th>Monthly Salary</th>
                                            <th>Progress</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($active_plans as $plan): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo h($plan['full_name']); ?></strong><br>
                                                <small><?php echo h($plan['mobile']); ?></small>
                                            </td>
                                            <td>₹<?php echo number_format($plan['target_amount']); ?></td>
                                            <td>₹<?php echo number_format($plan['monthly_salary']); ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <?php
                                                    $progress = ($plan['months_paid'] / $plan['duration_months']) * 100;
                                                    ?>
                                                    <div class="progress-bar" style="width: <?php echo $progress; ?>%">
                                                        <?php echo $plan['months_paid']; ?>/<?php echo $plan['duration_months']; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary"
                                                        onclick="processSalaryPayment(<?php echo $plan['id']; ?>, <?php echo $plan['associate_id']; ?>, <?php echo $plan['monthly_salary']; ?>, '<?php echo addslashes($plan['full_name']); ?>')">
                                                    <i class="fas fa-money-bill me-1"></i>Pay Salary
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No active salary plans</h6>
                                <p class="text-muted">Create salary plans to motivate associates</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Pending Payments
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($pending_payments)): ?>
                                <?php foreach (array_slice($pending_payments, 0, 5) as $pending): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                                    <div>
                                        <strong><?php echo h($pending['full_name']); ?></strong><br>
                                        <small>₹<?php echo number_format($pending['monthly_salary']); ?>/month</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-warning"><?php echo $pending['pending_months']; ?> pending</span><br>
                                        <small><?php echo $pending['days_remaining']; ?> days left</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <p class="text-muted mb-0">All payments up to date</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Plan Modal -->
    <div class="modal fade" id="newPlanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Salary Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="apply_salary_plan">
                        <input type="hidden" name="plan_id" id="selected_plan_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Associate *</label>
                                <select class="form-select" name="associate_id" required>
                                    <option value="">Choose Associate</option>
                                    <?php foreach ($associates as $associate): ?>
                                    <option value="<?php echo $associate['id']; ?>">
                                        <?php echo h($associate['full_name']); ?> - <?php echo h($associate['mobile']); ?>
                                        (Business: ₹<?php echo number_format($associate['total_business']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Target Amount *</label>
                                <input type="number" class="form-control" name="target_amount" id="target_amount" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Monthly Salary *</label>
                                <input type="number" class="form-control" name="monthly_salary" id="monthly_salary" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Duration (Months) *</label>
                                <input type="number" class="form-control" name="duration_months" id="duration_months" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target Achievement Days *</label>
                            <input type="number" class="form-control" name="target_days" id="target_days" required>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Plan Summary:</strong> Associate must achieve <span id="summary_target">₹0</span> business in <span id="summary_days">0</span> days to receive <span id="summary_salary">₹0</span> monthly salary for <span id="summary_months">0</span> months.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Activate Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Salary Payment Modal -->
    <div class="modal fade" id="salaryPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Salary Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="process_salary_payment">
                        <input type="hidden" name="plan_id" id="payment_plan_id">
                        <input type="hidden" name="associate_id" id="payment_associate_id">

                        <div class="mb-3">
                            <label class="form-label">Associate</label>
                            <input type="text" class="form-control" id="payment_associate_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Salary Amount</label>
                            <input type="number" class="form-control" name="salary_amount" id="payment_salary_amount" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Month</label>
                            <input type="month" class="form-control" name="month_year" value="<?php echo date('Y-m'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-money-bill me-2"></i>Process Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function selectPlan(plan) {
            document.getElementById('selected_plan_id').value = plan.id;
            document.getElementById('target_amount').value = plan.target;
            document.getElementById('monthly_salary').value = plan.salary;
            document.getElementById('duration_months').value = plan.duration_months;
            document.getElementById('target_days').value = plan.duration_days;
            updateSummary();
            new bootstrap.Modal(document.getElementById('newPlanModal')).show();
        }

        function updateSummary() {
            const target = document.getElementById('target_amount').value;
            const salary = document.getElementById('monthly_salary').value;
            const months = document.getElementById('duration_months').value;
            const days = document.getElementById('target_days').value;

            document.getElementById('summary_target').textContent = '₹' + Number(target).toLocaleString();
            document.getElementById('summary_salary').textContent = '₹' + Number(salary).toLocaleString();
            document.getElementById('summary_months').textContent = months;
            document.getElementById('summary_days').textContent = days;
        }

        function processSalaryPayment(planId, associateId, salaryAmount, associateName) {
            document.getElementById('payment_plan_id').value = planId;
            document.getElementById('payment_associate_id').value = associateId;
            document.getElementById('payment_salary_amount').value = salaryAmount;
            document.getElementById('payment_associate_name').value = associateName;
            new bootstrap.Modal(document.getElementById('salaryPaymentModal')).show();
        }

        // Update summary when values change
        document.addEventListener('DOMContentLoaded', function() {
            ['target_amount', 'monthly_salary', 'duration_months', 'target_days'].forEach(id => {
                document.getElementById(id).addEventListener('input', updateSummary);
            });
        });
    </script>
</body>
</html>
