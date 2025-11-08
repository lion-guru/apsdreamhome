<?php
/**
 * Advanced MLM Plan Builder Interface
 * Supports Binary, Unilevel, Matrix, and Hybrid Plans
 */

require_once 'includes/config.php';
require_once 'includes/associate_permissions.php';

// Check if user is admin or super admin
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$admin_role = $_SESSION['role'] ?? 'admin';
if (!in_array($admin_role, ['admin', 'company_owner'])) {
    $_SESSION['error_message'] = "You don't have permission to access plan builder.";
    header("Location: index.php");
    exit();
}

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_plan'])) {
        $result = createMLMPlan($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            header("Location: mlm_plan_manager.php");
            exit();
        } else {
            $message = $result['message'];
            $message_type = 'danger';
        }
    }

    if (isset($_POST['test_calculation'])) {
        $result = testPlanCalculation($_POST);
        if ($result['success']) {
            $test_results = $result['data'];
            $message = "Calculation completed successfully!";
            $message_type = 'success';
        } else {
            $message = $result['message'];
            $message_type = 'danger';
        }
    }
}

function createMLMPlan($data) {
    global $conn;

    try {
        $conn->beginTransaction();

        // Insert main plan
        $plan_query = "INSERT INTO mlm_commission_plans
                      (plan_name, plan_code, plan_type, description, joining_fee, monthly_target,
                       status, created_by, created_at)
                      VALUES (?, ?, ?, ?, ?, ?, 'active', ?, NOW())";

        $plan_stmt = $conn->prepare($plan_query);
        $plan_stmt->bind_param("sssdiii",
            $data['plan_name'],
            $data['plan_code'],
            $data['plan_type'],
            $data['description'],
            $data['joining_fee'],
            $data['monthly_target'],
            $_SESSION['admin_id']
        );
        $plan_stmt->execute();

        $plan_id = $conn->insert_id;

        // Insert plan levels
        if (isset($data['levels']) && is_array($data['levels'])) {
            foreach ($data['levels'] as $index => $level) {
                if (!empty($level['level_name'])) {
                    $level_query = "INSERT INTO mlm_plan_levels
                                   (plan_id, level_name, level_order, direct_commission, team_commission,
                                    level_bonus, matching_bonus, leadership_bonus, performance_bonus,
                                    monthly_target, created_at)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                    $level_stmt = $conn->prepare($level_query);
                    $level_stmt->bind_param("isdddddddd",
                        $plan_id,
                        $level['level_name'],
                        ($index + 1),
                        $level['direct_commission'] ?? 0,
                        $level['team_commission'] ?? 0,
                        $level['level_bonus'] ?? 0,
                        $level['matching_bonus'] ?? 0,
                        $level['leadership_bonus'] ?? 0,
                        $level['performance_bonus'] ?? 0,
                        $level['monthly_target'] ?? 0
                    );
                    $level_stmt->execute();
                }
            }
        }

        // Insert plan bonuses
        if (isset($data['bonuses']) && is_array($data['bonuses'])) {
            foreach ($data['bonuses'] as $bonus) {
                if (!empty($bonus['bonus_name'])) {
                    $bonus_query = "INSERT INTO mlm_plan_bonuses
                                   (plan_id, bonus_name, bonus_type, bonus_percentage, min_achievement,
                                    max_achievement, is_active, created_at)
                                   VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";

                    $bonus_stmt = $conn->prepare($bonus_query);
                    $bonus_stmt->bind_param("isdddd",
                        $plan_id,
                        $bonus['bonus_name'],
                        $bonus['bonus_type'] ?? 'percentage',
                        $bonus['bonus_percentage'] ?? 0,
                        $bonus['min_achievement'] ?? 0,
                        $bonus['max_achievement'] ?? 0
                    );
                    $bonus_stmt->execute();
                }
            }
        }

        $conn->commit();
        return ['success' => true, 'message' => 'MLM Plan created successfully!'];

    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Error creating plan: ' . $e->getMessage()];
    }
}

function testPlanCalculation($data) {
    try {
        // Simulate calculation with test data
        $test_data = [
            'associate_id' => 1,
            'business_volume' => $data['test_volume'] ?? 1000000,
            'current_level' => $data['test_level'] ?? 1,
            'team_size' => $data['test_team_size'] ?? 10
        ];

        // Return test calculation results
        return [
            'success' => true,
            'data' => [
                'binary_commission' => $test_data['business_volume'] * 0.1,
                'unilevel_commission' => $test_data['business_volume'] * 0.08,
                'matrix_commission' => $test_data['business_volume'] * 0.06,
                'leadership_bonus' => $test_data['team_size'] >= 5 ? $test_data['business_volume'] * 0.02 : 0,
                'total_commission' => $test_data['business_volume'] * 0.26
            ]
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Calculation error: ' . $e->getMessage()];
    }
}

// Get existing plans for reference
$existing_plans = [];
$plans_query = "SELECT * FROM mlm_commission_plans WHERE status = 'active' ORDER BY created_at DESC";
$plans_result = $conn->query($plans_query);
if ($plans_result) {
    $existing_plans = $plans_result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced MLM Plan Builder - APS Dream Home</title>

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
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .plan-builder-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px 0;
            overflow: hidden;
        }

        .plan-type-card {
            border: 2px solid #e3e6f0;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .plan-type-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .plan-type-card.selected {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .level-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--info-color);
        }

        .commission-input {
            position: relative;
        }

        .commission-input input {
            padding-right: 3rem;
        }

        .commission-input .input-group-text {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            background: none;
            border: none;
            color: #6c757d;
        }

        .plan-preview {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border: 2px solid var(--success-color);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .calculation-result {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0;
        }

        .btn-create-plan {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .btn-create-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .plan-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
        }

        .step {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .step.active {
            background: var(--primary-color);
            color: white;
        }

        .step.completed {
            background: var(--success-color);
            color: white;
        }

        .drag-handle {
            cursor: move;
            color: #6c757d;
            margin-right: 0.5rem;
        }

        .level-actions {
            opacity: 0;
            transition: opacity 0.3s;
        }

        .level-card:hover .level-actions {
            opacity: 1;
        }

        .commission-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .hybrid-features {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 2px solid var(--info-color);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-home me-2"></i>APS Dream Homes
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="manage_users.php"><i class="fas fa-users me-2"></i>Users</a></li>
                        <li><a class="dropdown-item" href="mlm_dashboard.php"><i class="fas fa-network-wired me-2"></i>MLM Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="plan-builder-container">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <h1 class="mb-3">
                                <i class="fas fa-tools text-primary me-2"></i>Advanced MLM Plan Builder
                            </h1>
                            <p class="text-muted">
                                Create sophisticated MLM compensation plans with Binary, Unilevel, Matrix, and Hybrid options
                            </p>
                        </div>

                        <!-- Messages -->
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Step Indicator -->
                        <div class="step-indicator">
                            <div class="step active" data-step="1">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="step" data-step="2">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="step" data-step="3">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="step" data-step="4">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>

                        <!-- Plan Builder Form -->
                        <form method="post" action="" id="planBuilderForm">

                            <!-- Step 1: Plan Type Selection -->
                            <div class="form-step active" id="step1">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4 class="mb-4">
                                            <i class="fas fa-info-circle text-primary me-2"></i>Select Plan Type
                                        </h4>

                                        <div class="row">
                                            <!-- Binary Plan -->
                                            <div class="col-md-6 mb-3">
                                                <div class="plan-type-card" onclick="selectPlanType('binary')">
                                                    <div class="text-center mb-3">
                                                        <i class="fas fa-code-branch fa-3x text-primary"></i>
                                                    </div>
                                                    <h5>Binary Plan</h5>
                                                    <p class="text-muted">Two legs (left/right) with balancing. Simple and effective for team building.</p>
                                                    <div class="mt-3">
                                                        <span class="badge bg-primary">Left/Right Structure</span>
                                                        <span class="badge bg-info">10-15% Commission</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Unilevel Plan -->
                                            <div class="col-md-6 mb-3">
                                                <div class="plan-type-card" onclick="selectPlanType('unilevel')">
                                                    <div class="text-center mb-3">
                                                        <i class="fas fa-sitemap fa-3x text-success"></i>
                                                    </div>
                                                    <h5>Unilevel Plan</h5>
                                                    <p class="text-muted">Unlimited width, limited depth. Perfect for wide network growth.</p>
                                                    <div class="mt-3">
                                                        <span class="badge bg-success">Unlimited Frontline</span>
                                                        <span class="badge bg-warning">7-10% Commission</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Matrix Plan -->
                                            <div class="col-md-6 mb-3">
                                                <div class="plan-type-card" onclick="selectPlanType('matrix')">
                                                    <div class="text-center mb-3">
                                                        <i class="fas fa-th fa-3x text-warning"></i>
                                                    </div>
                                                    <h5>Matrix Plan</h5>
                                                    <p class="text-muted">Fixed width and depth structure. Forces teamwork and spillover.</p>
                                                    <div class="mt-3">
                                                        <span class="badge bg-warning">3x9, 4x7, etc.</span>
                                                        <span class="badge bg-danger">8-12% Commission</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hybrid Plan -->
                                            <div class="col-md-6 mb-3">
                                                <div class="plan-type-card" onclick="selectPlanType('hybrid')">
                                                    <div class="text-center mb-3">
                                                        <i class="fas fa-atom fa-3x text-info"></i>
                                                    </div>
                                                    <h5>Hybrid Plan</h5>
                                                    <p class="text-muted">Combines best features of all plans. Maximum flexibility and earning potential.</p>
                                                    <div class="mt-3">
                                                        <span class="badge bg-info">Best of All Plans</span>
                                                        <span class="badge bg-success">15-25% Commission</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="plan_type" id="selected_plan_type" value="">
                                    </div>

                                    <div class="col-md-4">
                                        <div class="hybrid-features">
                                            <h6 class="text-center mb-3">
                                                <i class="fas fa-star text-warning me-2"></i>Why Choose Hybrid?
                                            </h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>Combines strengths of all plans</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Maximum earning potential</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Flexible structure</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Balanced growth incentives</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Suitable for all business sizes</li>
                                            </ul>
                                        </div>

                                        <div class="text-center">
                                            <button type="button" class="btn btn-primary btn-lg" onclick="nextStep(2)">
                                                <i class="fas fa-arrow-right me-2"></i>Next: Configure Plan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Plan Configuration -->
                            <div class="form-step" id="step2">
                                <h4 class="mb-4">
                                    <i class="fas fa-cogs text-primary me-2"></i>Configure Your Plan
                                </h4>

                                <div class="row">
                                    <div class="col-md-8">
                                        <!-- Basic Plan Information -->
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">Basic Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Plan Name *</label>
                                                            <input type="text" class="form-control form-control-lg"
                                                                   name="plan_name" required
                                                                   placeholder="e.g., Premium Hybrid Plan V2">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Plan Code *</label>
                                                            <input type="text" class="form-control form-control-lg"
                                                                   name="plan_code" required
                                                                   placeholder="e.g., PREMIUM_HYBRID_V2">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="description" rows="3"
                                                              placeholder="Describe your MLM plan features and benefits..."></textarea>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Joining Fee (₹)</label>
                                                            <input type="number" class="form-control" name="joining_fee"
                                                                   value="5000" min="0" step="100">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Monthly Target (₹)</label>
                                                            <input type="number" class="form-control" name="monthly_target"
                                                                   value="100000" min="0" step="1000">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Level Configuration -->
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Level Configuration</h6>
                                                <button type="button" class="btn btn-sm btn-success" onclick="addLevel()">
                                                    <i class="fas fa-plus me-1"></i>Add Level
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div id="levelsContainer">
                                                    <!-- Default Level 1 -->
                                                    <div class="level-card" data-level-id="1">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="mb-0">
                                                                <i class="fas fa-crown me-2"></i>Level 1 - Associate
                                                            </h6>
                                                            <span class="plan-badge">Starter Level</span>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Level Name</label>
                                                                    <input type="text" class="form-control"
                                                                           name="levels[0][level_name]" value="Associate" required>
                                                                    <input type="hidden" name="levels[0][level_id]" value="1">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Monthly Target (₹)</label>
                                                                    <div class="commission-input">
                                                                        <input type="number" class="form-control"
                                                                               name="levels[0][monthly_target]" value="100000" step="1000" min="0">
                                                                        <div class="input-group-text">₹</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Direct Commission (%)</label>
                                                                    <div class="commission-input">
                                                                        <input type="number" class="form-control"
                                                                               name="levels[0][direct_commission]" value="10" step="0.1" min="0" max="50">
                                                                        <div class="input-group-text">%</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Team Commission (%)</label>
                                                                    <div class="commission-input">
                                                                        <input type="number" class="form-control"
                                                                               name="levels[0][team_commission]" value="5" step="0.1" min="0" max="20">
                                                                        <div class="input-group-text">%</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Level Bonus (%)</label>
                                                                    <div class="commission-input">
                                                                        <input type="number" class="form-control"
                                                                               name="levels[0][level_bonus]" value="2" step="0.1" min="0" max="20">
                                                                        <div class="input-group-text">%</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Matching Bonus (%)</label>
                                                                    <div class="commission-input">
                                                                        <input type="number" class="form-control"
                                                                               name="levels[0][matching_bonus]" value="3" step="0.1" min="0" max="30">
                                                                        <div class="input-group-text">%</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="level-actions">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLevel(this)">
                                                                <i class="fas fa-trash me-1"></i>Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-center mt-3">
                                                    <button type="button" class="btn btn-outline-primary" onclick="addLevel()">
                                                        <i class="fas fa-plus me-2"></i>Add Another Level
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bonus Configuration -->
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Bonus Configuration</h6>
                                                <button type="button" class="btn btn-sm btn-info" onclick="addBonus()">
                                                    <i class="fas fa-plus me-1"></i>Add Bonus
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div id="bonusesContainer">
                                                    <!-- Default Leadership Bonus -->
                                                    <div class="bonus-card mb-3 p-3 border rounded">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Bonus Name</label>
                                                                    <input type="text" class="form-control"
                                                                           name="bonuses[0][bonus_name]" value="Leadership Bonus" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Bonus Type</label>
                                                                    <select class="form-select" name="bonuses[0][bonus_type]">
                                                                        <option value="percentage">Percentage</option>
                                                                        <option value="fixed">Fixed Amount</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Bonus (%/₹)</label>
                                                                    <input type="number" class="form-control"
                                                                           name="bonuses[0][bonus_percentage]" value="2" step="0.1" min="0">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Min Achievement</label>
                                                                    <input type="number" class="form-control"
                                                                           name="bonuses[0][min_achievement]" value="500000" min="0">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <button type="button" class="btn btn-sm btn-outline-danger mt-4" onclick="removeBonus(this)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-center">
                                                    <button type="button" class="btn btn-outline-info" onclick="addBonus()">
                                                        <i class="fas fa-plus me-2"></i>Add Custom Bonus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <!-- Plan Preview -->
                                        <div class="plan-preview">
                                            <h6 class="text-center mb-3">
                                                <i class="fas fa-eye text-success me-2"></i>Plan Preview
                                            </h6>

                                            <div class="commission-summary">
                                                <div class="summary-row">
                                                    <span>Plan Type:</span>
                                                    <span id="preview_plan_type">Select a plan type</span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Joining Fee:</span>
                                                    <span id="preview_joining_fee">₹0</span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Monthly Target:</span>
                                                    <span id="preview_monthly_target">₹0</span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Max Commission:</span>
                                                    <span id="preview_max_commission">₹0</span>
                                                </div>
                                            </div>

                                            <div class="text-center mt-3">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="testCalculation()">
                                                    <i class="fas fa-calculator me-1"></i>Test Calculation
                                                </button>
                                            </div>

                                            <?php if (isset($test_results)): ?>
                                            <div class="calculation-result mt-3">
                                                <h6>Test Results (₹10,00,000 volume):</h6>
                                                <div class="row text-center">
                                                    <div class="col-6">
                                                        <strong>Binary:</strong><br>
                                                        ₹<?php echo number_format($test_results['binary_commission']); ?>
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Unilevel:</strong><br>
                                                        ₹<?php echo number_format($test_results['unilevel_commission']); ?>
                                                    </div>
                                                </div>
                                                <div class="row text-center mt-2">
                                                    <div class="col-6">
                                                        <strong>Matrix:</strong><br>
                                                        ₹<?php echo number_format($test_results['matrix_commission']); ?>
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Total:</strong><br>
                                                        <span class="text-success">₹<?php echo number_format($test_results['total_commission']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-secondary me-2" onclick="previousStep(1)">
                                                <i class="fas fa-arrow-left me-2"></i>Back
                                            </button>
                                            <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                                <i class="fas fa-arrow-right me-2"></i>Next: Review
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Review & Create -->
                            <div class="form-step" id="step3">
                                <h4 class="mb-4">
                                    <i class="fas fa-check-circle text-primary me-2"></i>Review & Create Plan
                                </h4>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle me-2"></i>Plan Summary</h6>
                                            <div id="plan_summary">
                                                <!-- Plan summary will be populated here -->
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Final Configuration</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="final_config">
                                                    <!-- Final configuration will be populated here -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Quick Actions</h6>
                                            </div>
                                            <div class="card-body">
                                                <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="editPlan()">
                                                    <i class="fas fa-edit me-2"></i>Edit Plan
                                                </button>
                                                <button type="button" class="btn btn-outline-info w-100 mb-2" onclick="duplicatePlan()">
                                                    <i class="fas fa-copy me-2"></i>Duplicate Plan
                                                </button>
                                                <button type="button" class="btn btn-outline-success w-100 mb-2" onclick="exportPlan()">
                                                    <i class="fas fa-download me-2"></i>Export Plan
                                                </button>
                                            </div>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-secondary me-2" onclick="previousStep(2)">
                                                <i class="fas fa-arrow-left me-2"></i>Back
                                            </button>
                                            <button type="submit" name="create_plan" class="btn btn-create-plan">
                                                <i class="fas fa-rocket me-2"></i>Create Plan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentStep = 1;
        let selectedPlanType = '';

        function selectPlanType(planType) {
            // Remove selected class from all cards
            document.querySelectorAll('.plan-type-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');

            // Set selected plan type
            selectedPlanType = planType;
            document.getElementById('selected_plan_type').value = planType;

            // Update preview
            updatePlanPreview();
        }

        function nextStep(step) {
            if (step === 2 && !selectedPlanType) {
                alert('Please select a plan type first!');
                return;
            }

            // Mark current step as completed
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('completed');

            // Hide current step
            document.getElementById(`step${currentStep}`).classList.remove('active');

            // Show next step
            document.getElementById(`step${step}`).classList.add('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');

            currentStep = step;
        }

        function previousStep(step) {
            // Mark current step as active
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('completed');

            // Hide current step
            document.getElementById(`step${currentStep}`).classList.remove('active');

            // Show previous step
            document.getElementById(`step${step}`).classList.add('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');

            currentStep = step;
        }

        function addLevel() {
            const levelsContainer = document.getElementById('levelsContainer');
            const levelCount = levelsContainer.children.length + 1;

            const levelCard = document.createElement('div');
            levelCard.className = 'level-card';
            levelCard.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="fas fa-crown me-2"></i>Level ${levelCount}
                    </h6>
                    <span class="plan-badge">Level ${levelCount}</span>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Level Name</label>
                            <input type="text" class="form-control" name="levels[${levelCount - 1}][level_name]" required>
                            <input type="hidden" name="levels[${levelCount - 1}][level_id]" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Monthly Target (₹)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelCount - 1}][monthly_target]" value="500000" step="1000" min="0">
                                <div class="input-group-text">₹</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Direct Commission (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelCount - 1}][direct_commission]" value="8" step="0.1" min="0" max="50">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Team Commission (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelCount - 1}][team_commission]" value="4" step="0.1" min="0" max="20">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Level Bonus (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelCount - 1}][level_bonus]" value="1" step="0.1" min="0" max="20">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Matching Bonus (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelCount - 1}][matching_bonus]" value="2" step="0.1" min="0" max="30">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="level-actions">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLevel(this)">
                        <i class="fas fa-trash me-1"></i>Remove
                    </button>
                </div>
            `;

            levelsContainer.appendChild(levelCard);
        }

        function removeLevel(button) {
            button.closest('.level-card').remove();
        }

        function addBonus() {
            const bonusesContainer = document.getElementById('bonusesContainer');
            const bonusCount = bonusesContainer.children.length;

            const bonusCard = document.createElement('div');
            bonusCard.className = 'bonus-card mb-3 p-3 border rounded';
            bonusCard.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Bonus Name</label>
                            <input type="text" class="form-control" name="bonuses[${bonusCount}][bonus_name]" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Bonus Type</label>
                            <select class="form-select" name="bonuses[${bonusCount}][bonus_type]">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">Bonus (%/₹)</label>
                            <input type="number" class="form-control" name="bonuses[${bonusCount}][bonus_percentage]" step="0.1" min="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">Min Achievement</label>
                            <input type="number" class="form-control" name="bonuses[${bonusCount}][min_achievement]" min="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-outline-danger mt-4" onclick="removeBonus(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            bonusesContainer.appendChild(bonusCard);
        }

        function removeBonus(button) {
            button.closest('.bonus-card').remove();
        }

        function updatePlanPreview() {
            const planType = selectedPlanType;
            const joiningFee = document.querySelector('input[name="joining_fee"]').value || 0;
            const monthlyTarget = document.querySelector('input[name="monthly_target"]').value || 0;

            document.getElementById('preview_plan_type').textContent = planType.charAt(0).toUpperCase() + planType.slice(1);
            document.getElementById('preview_joining_fee').textContent = '₹' + Number(joiningFee).toLocaleString();
            document.getElementById('preview_monthly_target').textContent = '₹' + Number(monthlyTarget).toLocaleString();

            // Calculate max potential commission
            const maxCommission = calculateMaxCommission();
            document.getElementById('preview_max_commission').textContent = '₹' + Number(maxCommission).toLocaleString();
        }

        function calculateMaxCommission() {
            // Simple calculation for preview
            const joiningFee = document.querySelector('input[name="joining_fee"]').value || 0;
            const monthlyTarget = document.querySelector('input[name="monthly_target"]').value || 0;

            // Assume 20% average commission rate
            return (joiningFee + monthlyTarget) * 0.2;
        }

        function testCalculation() {
            const testVolume = 1000000; // 10 lakhs
            const testLevel = 1;
            const testTeamSize = 10;

            // Simulate calculation
            const results = {
                binary_commission: testVolume * 0.1,
                unilevel_commission: testVolume * 0.08,
                matrix_commission: testVolume * 0.06,
                leadership_bonus: testTeamSize >= 5 ? testVolume * 0.02 : 0,
                total_commission: testVolume * 0.26
            };

            // Show results in preview area
            const calculationDiv = document.createElement('div');
            calculationDiv.className = 'calculation-result mt-3';
            calculationDiv.innerHTML = `
                <h6>Test Results (₹10,00,000 volume):</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <strong>Binary:</strong><br>
                        ₹${Number(results.binary_commission).toLocaleString()}
                    </div>
                    <div class="col-6">
                        <strong>Unilevel:</strong><br>
                        ₹${Number(results.unilevel_commission).toLocaleString()}
                    </div>
                </div>
                <div class="row text-center mt-2">
                    <div class="col-6">
                        <strong>Matrix:</strong><br>
                        ₹${Number(results.matrix_commission).toLocaleString()}
                    </div>
                    <div class="col-6">
                        <strong>Total:</strong><br>
                        <span class="text-success">₹${Number(results.total_commission).toLocaleString()}</span>
                    </div>
                </div>
            `;

            // Remove existing calculation result
            const existingResult = document.querySelector('.calculation-result');
            if (existingResult) {
                existingResult.remove();
            }

            // Add new calculation result
            document.querySelector('.plan-preview').appendChild(calculationDiv);
        }

        // Initialize preview updates
        document.addEventListener('DOMContentLoaded', function() {
            // Update preview when form values change
            ['joining_fee', 'monthly_target'].forEach(fieldName => {
                const field = document.querySelector(`input[name="${fieldName}"]`);
                if (field) {
                    field.addEventListener('input', updatePlanPreview);
                }
            });

            updatePlanPreview();
        });
    </script>
</body>
</html>
