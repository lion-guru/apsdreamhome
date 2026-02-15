<?php

/**
 * Commission Plans Management System
 * Admin interface for creating and managing MLM commission plans
 */

require_once 'includes/config.php';
require_once 'includes/associate_permissions.php';

$db = \App\Core\App::database();

// Check if user is admin (only Site Manager, President, VP can access)
session_start();
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
if (!isAssociateAdmin($associate_id)) {
    $_SESSION['error_message'] = "You don't have permission to access plan management.";
    header("Location: associate_dashboard.php");
    exit();
}

$associate_name = $_SESSION['associate_name'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_plan'])) {
        createCommissionPlan($_POST);
    } elseif (isset($_POST['update_plan'])) {
        updateCommissionPlan($_POST);
    } elseif (isset($_POST['activate_plan'])) {
        activatePlan($_POST['plan_id']);
    } elseif (isset($_POST['deactivate_plan'])) {
        deactivatePlan($_POST['plan_id']);
    } elseif (isset($_POST['delete_plan'])) {
        deletePlan($_POST['plan_id']);
    }
}

// Get all plans
$plans = getAllCommissionPlans();

// Get active plan
$active_plan = getActiveCommissionPlan();

function createCommissionPlan($data)
{
    $db = \App\Core\App::database();

    try {
        $plan_id = $db->insert('mlm_commission_plans', [
            'plan_name' => $data['plan_name'],
            'plan_code' => $data['plan_code'],
            'description' => $data['description'],
            'plan_type' => $data['plan_type'],
            'status' => 'draft',
            'created_by' => $_SESSION['associate_id']
        ]);

        // Create default levels
        createDefaultLevels($plan_id);

        $_SESSION['success_message'] = "Commission plan created successfully!";
        header("Location: commission_plan_manager.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error creating plan: " . $e->getMessage();
    }
}

function updateCommissionPlan($data)
{
    $db = \App\Core\App::database();

    try {
        $plan_id = $data['plan_id'];

        // Update plan details
        $db->update('mlm_commission_plans', [
            'plan_name' => $data['plan_name'],
            'plan_code' => $data['plan_code'],
            'description' => $data['description'],
            'plan_type' => $data['plan_type'],
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $plan_id]);

        // Check if levels need to be updated - usually done via separate API/method
        // But if provided in $data, we could update them here.
        // For now, we assume this function only updates the main plan details.

        $_SESSION['success_message'] = "Commission plan updated successfully!";
        header("Location: commission_plan_manager.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating plan: " . $e->getMessage();
    }
}

function createDefaultLevels($plan_id)
{
    $db = \App\Core\App::database();

    $default_levels = [
        ['Associate', 5.00, 2.00, 0.00, 0.00, 0.00, 0.00, 1000000],
        ['Sr. Associate', 7.00, 3.00, 2.00, 5.00, 0.00, 0.00, 3500000],
        ['BDM', 10.00, 4.00, 3.00, 8.00, 1.00, 0.00, 7000000],
        ['Sr. BDM', 12.00, 5.00, 4.00, 10.00, 2.00, 1.00, 15000000],
        ['Vice President', 15.00, 6.00, 5.00, 12.00, 3.00, 2.00, 30000000],
        ['President', 18.00, 7.00, 6.00, 15.00, 4.00, 3.00, 50000000],
        ['Site Manager', 20.00, 8.00, 7.00, 18.00, 5.00, 5.00, 999999999]
    ];

    foreach ($default_levels as $index => $level) {
        $db->insert('mlm_plan_levels', [
            'plan_id' => $plan_id,
            'level_name' => $level[0],
            'level_order' => ($index + 1),
            'direct_commission' => $level[1],
            'team_commission' => $level[2],
            'level_bonus' => $level[3],
            'matching_bonus' => $level[4],
            'leadership_bonus' => $level[5],
            'performance_bonus' => $level[6],
            'monthly_target' => $level[7]
        ]);
    }
}

function getAllCommissionPlans()
{
    $db = \App\Core\App::database();

    $query = "SELECT p.*, a.full_name as created_by_name,
              (SELECT COUNT(*) FROM mlm_plan_levels WHERE plan_id = p.id) as level_count
              FROM mlm_commission_plans p
              LEFT JOIN mlm_agents a ON p.created_by = a.id
              ORDER BY p.created_at DESC";

    return $db->fetch($query);
}

function getActiveCommissionPlan()
{
    $db = \App\Core\App::database();

    $query = "SELECT * FROM mlm_commission_plans WHERE status = 'active' LIMIT 1";
    return $db->fetch($query, [], false); // false for single row
}

function activatePlan($plan_id)
{
    $db = \App\Core\App::database();

    try {
        // Deactivate current active plan
        $db->execute("UPDATE mlm_commission_plans SET status = 'inactive', deactivated_at = NOW() WHERE status = 'active'");

        // Activate new plan
        $db->update(
            'mlm_commission_plans',
            ['status' => 'active', 'activated_at' => date('Y-m-d H:i:s')],
            ['id' => $plan_id]
        );

        $_SESSION['success_message'] = "Commission plan activated successfully!";
        header("Location: commission_plan_manager.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error activating plan: " . $e->getMessage();
    }
}

function deactivatePlan($plan_id)
{
    $db = \App\Core\App::database();

    try {
        $db->update(
            'mlm_commission_plans',
            ['status' => 'inactive', 'deactivated_at' => date('Y-m-d H:i:s')],
            ['id' => $plan_id]
        );

        $_SESSION['success_message'] = "Commission plan deactivated successfully!";
        header("Location: commission_plan_manager.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error deactivating plan: " . $e->getMessage();
    }
}

function deletePlan($plan_id)
{
    $db = \App\Core\App::database();

    try {
        // Check if plan is active
        $plan = $db->fetch("SELECT status FROM mlm_commission_plans WHERE id = :id", ['id' => $plan_id], false);

        if ($plan && $plan['status'] == 'active') {
            $_SESSION['error_message'] = "Cannot delete active plan. Please deactivate first.";
            header("Location: commission_plan_manager.php");
            exit();
        }

        $db->execute("DELETE FROM mlm_commission_plans WHERE id = :id", ['id' => $plan_id]);

        $_SESSION['success_message'] = "Commission plan deleted successfully!";
        header("Location: commission_plan_manager.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error deleting plan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Plan Manager - APS Dream Homes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --dark-color: #343a40;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            overflow: hidden;
        }

        .plan-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-archived {
            background-color: #e2e3e5;
            color: #6c757d;
        }

        .level-progress {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin: 0.5rem 0;
        }

        .commission-display {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 8px;
            margin: 0.25rem 0;
            border-left: 4px solid var(--primary-color);
        }

        .btn-create {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1rem 0;
        }

        .level-input-group {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin: 0.5rem 0;
            border-left: 4px solid var(--info-color);
        }

        .action-buttons .btn {
            margin: 0.25rem;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="associate_dashboard.php">
                <i class="fas fa-home me-2"></i>APS Dream Homes
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($associate_name); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="associate_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                        <li><a class="dropdown-item" href="commission_dashboard.php">
                                <i class="fas fa-rupee-sign me-2"></i>Commission Dashboard
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="associate_logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="dashboard-container">
                    <div class="p-4">
                        <h5 class="mb-4">Plan Management</h5>
                        <div class="list-group">
                            <a href="#plans" class="list-group-item list-group-item-action active">
                                <i class="fas fa-list me-2"></i>All Plans
                            </a>
                            <a href="#create" class="list-group-item list-group-item-action">
                                <i class="fas fa-plus me-2"></i>Create Plan
                            </a>
                            <a href="#calculator" class="list-group-item list-group-item-action">
                                <i class="fas fa-calculator me-2"></i>Plan Calculator
                            </a>
                            <a href="#analytics" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-bar me-2"></i>Plan Analytics
                            </a>
                            <a href="#settings" class="list-group-item list-group-item-action">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="dashboard-container">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h1 class="mb-2">Commission Plan Manager</h1>
                                <p class="mb-0 text-muted">Create and manage MLM commission plans</p>
                            </div>
                            <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                                <i class="fas fa-plus me-2"></i>Create New Plan
                            </button>
                        </div>

                        <!-- Alerts -->
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php unset($_SESSION['success_message']);
                        endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php unset($_SESSION['error_message']);
                        endif; ?>

                        <!-- Plans List -->
                        <div id="plans" class="mb-5">
                            <h3 class="mb-4">All Commission Plans</h3>

                            <div class="row">
                                <?php foreach ($plans as $plan): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card plan-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h5 class="card-title"><?php echo htmlspecialchars($plan['plan_name']); ?></h5>
                                                        <p class="card-text small text-muted">
                                                            Code: <?php echo htmlspecialchars($plan['plan_code']); ?>
                                                        </p>
                                                    </div>
                                                    <span class="status-badge status-<?php echo $plan['status']; ?>">
                                                        <?php echo ucfirst($plan['status']); ?>
                                                    </span>
                                                </div>

                                                <p class="card-text"><?php echo htmlspecialchars($plan['description']); ?></p>

                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>Created: <?php echo date('M d, Y', strtotime($plan['created_at'])); ?><br>
                                                        <i class="fas fa-user me-1"></i>By: <?php echo htmlspecialchars($plan['created_by_name']); ?><br>
                                                        <i class="fas fa-layer-group me-1"></i><?php echo $plan['level_count']; ?> levels
                                                    </small>
                                                </div>

                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPlan(<?php echo $plan['id']; ?>)">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </button>
                                                    <?php if ($plan['status'] == 'draft'): ?>
                                                        <button class="btn btn-sm btn-warning" onclick="editPlan(<?php echo $plan['id']; ?>)">
                                                            <i class="fas fa-edit me-1"></i>Edit
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($plan['status'] == 'draft'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="activatePlan(<?php echo $plan['id']; ?>)">
                                                            <i class="fas fa-play me-1"></i>Activate
                                                        </button>
                                                    <?php elseif ($plan['status'] == 'active'): ?>
                                                        <button class="btn btn-sm btn-warning" onclick="deactivatePlan(<?php echo $plan['id']; ?>)">
                                                            <i class="fas fa-pause me-1"></i>Deactivate
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($plan['status'] != 'active'): ?>
                                                        <button class="btn btn-sm btn-danger" onclick="deletePlan(<?php echo $plan['id']; ?>)">
                                                            <i class="fas fa-trash me-1"></i>Delete
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (empty($plans)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No commission plans found</h5>
                                    <p class="text-muted">Create your first commission plan to get started</p>
                                    <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                                        <i class="fas fa-plus me-2"></i>Create New Plan
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Plan Calculator -->
                        <div id="calculator" class="mb-5">
                            <h3 class="mb-4">Plan Calculator</h3>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Test Commission Calculations</h5>
                                            <form id="calculatorForm">
                                                <div class="mb-3">
                                                    <label class="form-label">Select Plan</label>
                                                    <select class="form-select" name="plan_id" required>
                                                        <option value="">Choose a plan...</option>
                                                        <?php foreach ($plans as $plan): ?>
                                                            <option value="<?php echo $plan['id']; ?>">
                                                                <?php echo htmlspecialchars($plan['plan_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Associate Level</label>
                                                    <select class="form-select" name="level" required>
                                                        <option value="">Choose level...</option>
                                                        <option value="Associate">Associate</option>
                                                        <option value="Sr. Associate">Sr. Associate</option>
                                                        <option value="BDM">BDM</option>
                                                        <option value="Sr. BDM">Sr. BDM</option>
                                                        <option value="Vice President">Vice President</option>
                                                        <option value="President">President</option>
                                                        <option value="Site Manager">Site Manager</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Property Value (₹)</label>
                                                    <input type="number" class="form-control" name="property_value" min="1" required>
                                                </div>
                                                <button type="button" class="btn btn-primary" onclick="calculateCommission()">
                                                    <i class="fas fa-calculator me-2"></i>Calculate
                                                </button>
                                            </form>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Calculation Results</h5>
                                            <div id="calculationResults">
                                                <div class="text-center py-4 text-muted">
                                                    <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                                                    Select parameters and click Calculate
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Plan Modal -->
    <div class="modal fade" id="createPlanModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Commission Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-section">
                                    <h6>Plan Details</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Plan Name *</label>
                                        <input type="text" class="form-control" name="plan_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Plan Code *</label>
                                        <input type="text" class="form-control" name="plan_code" required>
                                        <small class="text-muted">Unique identifier (e.g., PREMIUM_V1)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Plan Type</label>
                                        <select class="form-select" name="plan_type">
                                            <option value="standard">Standard</option>
                                            <option value="custom">Custom</option>
                                            <option value="promotional">Promotional</option>
                                            <option value="seasonal">Seasonal</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-section">
                                    <h6>Default Level Structure</h6>
                                    <p class="text-muted small">Default levels will be created automatically. You can customize them later.</p>
                                    <div class="level-input-group">
                                        <strong>Associate:</strong> 5% Direct, 2% Team<br>
                                        <strong>Sr. Associate:</strong> 7% Direct, 3% Team, 2% Level Bonus<br>
                                        <strong>BDM:</strong> 10% Direct, 4% Team, 3% Level Bonus, 1% Leadership<br>
                                        <strong>Sr. BDM:</strong> 12% Direct, 5% Team, 4% Level Bonus, 2% Leadership<br>
                                        <strong>VP:</strong> 15% Direct, 6% Team, 5% Level Bonus, 3% Leadership<br>
                                        <strong>President:</strong> 18% Direct, 7% Team, 6% Level Bonus, 4% Leadership<br>
                                        <strong>Site Manager:</strong> 20% Direct, 8% Team, 7% Level Bonus, 5% Leadership
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_plan" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Create Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function viewPlan(planId) {
            // Implement view plan functionality
            alert('View plan functionality will be implemented');
        }

        function editPlan(planId) {
            // Implement edit plan functionality
            alert('Edit plan functionality will be implemented');
        }

        function activatePlan(planId) {
            if (confirm('Are you sure you want to activate this plan? This will deactivate the current active plan.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="plan_id" value="${planId}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deactivatePlan(planId) {
            if (confirm('Are you sure you want to deactivate this plan?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="plan_id" value="${planId}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deletePlan(planId) {
            if (confirm('Are you sure you want to delete this plan? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="plan_id" value="${planId}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function calculateCommission() {
            const form = document.getElementById('calculatorForm');
            const formData = new FormData(form);

            // Get calculation results placeholder
            const resultsDiv = document.getElementById('calculationResults');

            // Basic validation
            if (!formData.get('plan_id') || !formData.get('level') || !formData.get('property_value')) {
                resultsDiv.innerHTML = '<div class="alert alert-warning">Please fill all fields</div>';
                return;
            }

            // Simulate calculation (in real implementation, this would call an API)
            const propertyValue = parseFloat(formData.get('property_value'));
            const level = formData.get('level');

            let directCommission = 0;
            let teamCommission = 0;
            let levelBonus = 0;
            let matchingBonus = 0;
            let leadershipBonus = 0;
            let performanceBonus = 0;
            let total = 0;

            // Level-based commission rates
            switch (level) {
                case 'Associate':
                    directCommission = propertyValue * 0.05;
                    teamCommission = propertyValue * 0.02;
                    total = directCommission + teamCommission;
                    break;
                case 'Sr. Associate':
                    directCommission = propertyValue * 0.07;
                    teamCommission = propertyValue * 0.03;
                    levelBonus = propertyValue * 0.02;
                    matchingBonus = propertyValue * 0.05;
                    total = directCommission + teamCommission + levelBonus + matchingBonus;
                    break;
                case 'BDM':
                    directCommission = propertyValue * 0.10;
                    teamCommission = propertyValue * 0.04;
                    levelBonus = propertyValue * 0.03;
                    matchingBonus = propertyValue * 0.08;
                    leadershipBonus = propertyValue * 0.01;
                    total = directCommission + teamCommission + levelBonus + matchingBonus + leadershipBonus;
                    break;
                case 'Sr. BDM':
                    directCommission = propertyValue * 0.12;
                    teamCommission = propertyValue * 0.05;
                    levelBonus = propertyValue * 0.04;
                    matchingBonus = propertyValue * 0.10;
                    leadershipBonus = propertyValue * 0.02;
                    performanceBonus = propertyValue * 0.01;
                    total = directCommission + teamCommission + levelBonus + matchingBonus + leadershipBonus + performanceBonus;
                    break;
                case 'Vice President':
                    directCommission = propertyValue * 0.15;
                    teamCommission = propertyValue * 0.06;
                    levelBonus = propertyValue * 0.05;
                    matchingBonus = propertyValue * 0.12;
                    leadershipBonus = propertyValue * 0.03;
                    performanceBonus = propertyValue * 0.02;
                    total = directCommission + teamCommission + levelBonus + matchingBonus + leadershipBonus + performanceBonus;
                    break;
                case 'President':
                    directCommission = propertyValue * 0.18;
                    teamCommission = propertyValue * 0.07;
                    levelBonus = propertyValue * 0.06;
                    matchingBonus = propertyValue * 0.15;
                    leadershipBonus = propertyValue * 0.04;
                    performanceBonus = propertyValue * 0.03;
                    total = directCommission + teamCommission + levelBonus + matchingBonus + leadershipBonus + performanceBonus;
                    break;
                case 'Site Manager':
                    directCommission = propertyValue * 0.20;
                    teamCommission = propertyValue * 0.08;
                    levelBonus = propertyValue * 0.07;
                    matchingBonus = propertyValue * 0.18;
                    leadershipBonus = propertyValue * 0.05;
                    performanceBonus = propertyValue * 0.05;
                    total = directCommission + teamCommission + levelBonus + matchingBonus + leadershipBonus + performanceBonus;
                    break;
            }

            // Display results
            resultsDiv.innerHTML = `
                <div class="calculation-results">
                    <h6>Commission Breakdown for ${level}</h6>
                    <div class="commission-display">
                        <strong>Direct Commission:</strong> ₹${directCommission.toLocaleString('en-IN')} (${(directCommission/propertyValue*100).toFixed(2)}%)
                    </div>
                    <div class="commission-display">
                        <strong>Team Commission:</strong> ₹${teamCommission.toLocaleString('en-IN')} (${(teamCommission/propertyValue*100).toFixed(2)}%)
                    </div>
                    ${levelBonus > 0 ? `<div class="commission-display">
                        <strong>Level Difference Bonus:</strong> ₹${levelBonus.toLocaleString('en-IN')} (${(levelBonus/propertyValue*100).toFixed(2)}%)
                    </div>` : ''}
                    ${matchingBonus > 0 ? `<div class="commission-display">
                        <strong>Matching Bonus:</strong> ₹${matchingBonus.toLocaleString('en-IN')} (${(matchingBonus/propertyValue*100).toFixed(2)}%)
                    </div>` : ''}
                    ${leadershipBonus > 0 ? `<div class="commission-display">
                        <strong>Leadership Bonus:</strong> ₹${leadershipBonus.toLocaleString('en-IN')} (${(leadershipBonus/propertyValue*100).toFixed(2)}%)
                    </div>` : ''}
                    ${performanceBonus > 0 ? `<div class="commission-display">
                        <strong>Performance Bonus:</strong> ₹${performanceBonus.toLocaleString('en-IN')} (${(performanceBonus/propertyValue*100).toFixed(2)}%)
                    </div>` : ''}
                    <hr>
                    <div class="commission-display" style="background: linear-gradient(135deg, var(--success-color), #20c997); color: white;">
                        <strong>Total Commission:</strong> ₹${total.toLocaleString('en-IN')} (${(total/propertyValue*100).toFixed(2)}%)
                    </div>
                </div>
            `;
        }
    </script>
</body>

</html>