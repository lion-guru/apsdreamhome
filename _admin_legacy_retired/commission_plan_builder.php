<?php
/**
 * Commission Plan Builder
 * Advanced interface for creating and customizing MLM commission plans
 */

require_once 'includes/config.php';
require_once 'includes/associate_permissions.php';

// Check if user is admin
session_start();
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
if (!isAssociateAdmin($associate_id)) {
    $_SESSION['error_message'] = "You don't have permission to access plan builder.";
    header("Location: associate_dashboard.php");
    exit();
}

$associate_name = $_SESSION['associate_name'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_plan'])) {
        $result = saveCommissionPlan($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            header("Location: commission_plan_manager.php");
            exit();
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

// Get plan data if editing
$plan_id = $_GET['plan_id'] ?? null;
$plan_data = null;
$plan_levels = [];

if ($plan_id) {
    $plan_data = getPlanById($plan_id);
    $plan_levels = getPlanLevels($plan_id);
}

function saveCommissionPlan($data) {
    $db = \App\Core\App::database();

    try {
        $db->beginTransaction();

        if (isset($data['plan_id']) && !empty($data['plan_id'])) {
            // Update existing plan
            $query = "UPDATE mlm_commission_plans SET
                      plan_name = :plan_name, plan_code = :plan_code, description = :description, plan_type = :plan_type, updated_at = NOW()
                      WHERE id = :plan_id";

            $db->execute($query, [
                'plan_name' => $data['plan_name'],
                'plan_code' => $data['plan_code'],
                'description' => $data['description'],
                'plan_type' => $data['plan_type'],
                'plan_id' => $data['plan_id']
            ]);

            $plan_id = $data['plan_id'];
        } else {
            // Create new plan
            $query = "INSERT INTO mlm_commission_plans
                      (plan_name, plan_code, description, plan_type, status, created_by)
                      VALUES (:plan_name, :plan_code, :description, :plan_type, 'draft', :created_by)";

            $db->execute($query, [
                'plan_name' => $data['plan_name'],
                'plan_code' => $data['plan_code'],
                'description' => $data['description'],
                'plan_type' => $data['plan_type'],
                'created_by' => $_SESSION['associate_id']
            ]);

            $plan_id = $db->lastInsertId();
        }

        // Save levels
        if (isset($data['levels']) && is_array($data['levels'])) {
            foreach ($data['levels'] as $index => $level) {
                if (!empty($level['level_name'])) {
                    if (isset($level['level_id']) && !empty($level['level_id'])) {
                        // Update existing level
                        $level_query = "UPDATE mlm_plan_levels SET
                                       level_name = :level_name, level_order = :level_order, direct_commission = :direct_commission, team_commission = :team_commission,
                                       level_bonus = :level_bonus, matching_bonus = :matching_bonus, leadership_bonus = :leadership_bonus, performance_bonus = :performance_bonus, monthly_target = :monthly_target
                                       WHERE id = :level_id";

                        $db->execute($level_query, [
                            'level_name' => $level['level_name'],
                            'level_order' => ($index + 1),
                            'direct_commission' => $level['direct_commission'],
                            'team_commission' => $level['team_commission'],
                            'level_bonus' => $level['level_bonus'],
                            'matching_bonus' => $level['matching_bonus'],
                            'leadership_bonus' => $level['leadership_bonus'],
                            'performance_bonus' => $level['performance_bonus'],
                            'monthly_target' => $level['monthly_target'],
                            'level_id' => $level['level_id']
                        ]);
                    } else {
                        // Create new level
                        $level_query = "INSERT INTO mlm_plan_levels
                                       (plan_id, level_name, level_order, direct_commission, team_commission, level_bonus, matching_bonus, leadership_bonus, performance_bonus, monthly_target)
                                       VALUES (:plan_id, :level_name, :level_order, :direct_commission, :team_commission, :level_bonus, :matching_bonus, :leadership_bonus, :performance_bonus, :monthly_target)";

                        $db->execute($level_query, [
                            'plan_id' => $plan_id,
                            'level_name' => $level['level_name'],
                            'level_order' => ($index + 1),
                            'direct_commission' => $level['direct_commission'],
                            'team_commission' => $level['team_commission'],
                            'level_bonus' => $level['level_bonus'],
                            'matching_bonus' => $level['matching_bonus'],
                            'leadership_bonus' => $level['leadership_bonus'],
                            'performance_bonus' => $level['performance_bonus'],
                            'monthly_target' => $level['monthly_target']
                        ]);
                    }
                }
            }
        }

        $db->commit();
        return ['success' => true, 'message' => 'Plan saved successfully!'];

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'message' => 'Error saving plan: ' . $e->getMessage()];
    }
}

function getPlanById($plan_id) {
    $db = \App\Core\App::database();
    return $db->fetch("SELECT * FROM mlm_commission_plans WHERE id = :id", ['id' => $plan_id], false);
}

function getPlanLevels($plan_id) {
    $db = \App\Core\App::database();
    return $db->fetch("SELECT * FROM mlm_plan_levels WHERE plan_id = :plan_id ORDER BY level_order", ['plan_id' => $plan_id]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Builder - APS Dream Homes</title>

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
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px 0;
            overflow: hidden;
        }

        .builder-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            border-left: 5px solid var(--primary-color);
        }

        .level-card {
            background: white;
            border-radius: 10px;
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

        .level-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin: -1rem -1rem 1rem -1rem;
        }

        .btn-add-level {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            margin: 1rem 0;
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .commission-preview {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border: 2px solid var(--success-color);
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .level-badge {
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            font-weight: bold;
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
                        <li><a class="dropdown-item" href="commission_plan_manager.php">
                            <i class="fas fa-cog me-2"></i>Plan Manager
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
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
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="dashboard-container">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <h1 class="mb-3">
                                <i class="fas fa-tools text-primary me-2"></i>Commission Plan Builder
                            </h1>
                            <p class="text-muted">
                                <?php echo $plan_id ? 'Edit existing commission plan' : 'Create a new commission plan'; ?>
                            </p>
                        </div>

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

                        <!-- Builder Form -->
                        <form method="post" action="" id="planBuilderForm">
                            <?php if ($plan_id): ?>
                            <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
                            <?php endif; ?>

                            <!-- Step 1: Basic Information -->
                            <div class="form-step active" id="step1">
                                <div class="builder-section">
                                    <h4 class="mb-4">
                                        <i class="fas fa-info-circle text-primary me-2"></i>Plan Information
                                    </h4>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <i class="fas fa-tag me-1"></i>Plan Name *
                                                </label>
                                                <input type="text" class="form-control form-control-lg"
                                                       name="plan_name" required
                                                       value="<?php echo htmlspecialchars($plan_data['plan_name'] ?? ''); ?>"
                                                       placeholder="e.g., Premium Commission Plan V2">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <i class="fas fa-code me-1"></i>Plan Code *
                                                </label>
                                                <input type="text" class="form-control form-control-lg"
                                                       name="plan_code" required
                                                       value="<?php echo htmlspecialchars($plan_data['plan_code'] ?? ''); ?>"
                                                       placeholder="e.g., PREMIUM_V2">
                                                <small class="text-muted">Unique identifier for the plan</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-align-left me-1"></i>Description
                                        </label>
                                        <textarea class="form-control" name="description" rows="3"
                                                  placeholder="Describe your commission plan..."><?php echo htmlspecialchars($plan_data['description'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <i class="fas fa-layer-group me-1"></i>Plan Type
                                                </label>
                                                <select class="form-select" name="plan_type">
                                                    <option value="standard" <?php echo ($plan_data['plan_type'] ?? '') == 'standard' ? 'selected' : ''; ?>>Standard</option>
                                                    <option value="custom" <?php echo ($plan_data['plan_type'] ?? '') == 'custom' ? 'selected' : ''; ?>>Custom</option>
                                                    <option value="promotional" <?php echo ($plan_data['plan_type'] ?? '') == 'promotional' ? 'selected' : ''; ?>>Promotional</option>
                                                    <option value="seasonal" <?php echo ($plan_data['plan_type'] ?? '') == 'seasonal' ? 'selected' : ''; ?>>Seasonal</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <i class="fas fa-bullseye me-1"></i>Target Audience
                                                </label>
                                                <select class="form-select" name="target_audience">
                                                    <option value="all">All Associates</option>
                                                    <option value="new">New Associates</option>
                                                    <option value="existing">Existing Associates</option>
                                                    <option value="top_performers">Top Performers</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="button" class="btn btn-primary btn-lg" onclick="nextStep(2)">
                                            <i class="fas fa-arrow-right me-2"></i>Next: Configure Levels
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Configure Levels -->
                            <div class="form-step" id="step2">
                                <div class="builder-section">
                                    <h4 class="mb-4">
                                        <i class="fas fa-layer-group text-primary me-2"></i>Configure Levels
                                    </h4>

                                    <div id="levelsContainer">
                                        <?php if (!empty($plan_levels)): ?>
                                        <?php foreach ($plan_levels as $index => $level): ?>
                                        <div class="level-card" data-level-id="<?php echo $level['id']; ?>">
                                            <div class="level-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-crown me-2"></i>Level <?php echo ($index + 1); ?>
                                                    </h6>
                                                    <span class="level-badge">Order: <?php echo $level['level_order']; ?></span>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Level Name</label>
                                                        <input type="text" class="form-control" name="levels[<?php echo $index; ?>][level_name]"
                                                               value="<?php echo htmlspecialchars($level['level_name']); ?>" required>
                                                        <input type="hidden" name="levels[<?php echo $index; ?>][level_id]" value="<?php echo $level['id']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Monthly Target (₹)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[<?php echo $index; ?>][monthly_target]"
                                                                   value="<?php echo $level['monthly_target']; ?>" step="1000" min="0">
                                                            <div class="input-group-text">₹</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Direct Commission (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[<?php echo $index; ?>][direct_commission]"
                                                                   value="<?php echo $level['direct_commission']; ?>" step="0.1" min="0" max="50">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Team Commission (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[<?php echo $index; ?>][team_commission]"
                                                                   value="<?php echo $level['team_commission']; ?>" step="0.1" min="0" max="20">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Level Bonus (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[<?php echo $index; ?>][level_bonus]"
                                                                   value="<?php echo $level['level_bonus']; ?>" step="0.1" min="0" max="20">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Matching Bonus (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[<?php echo $index; ?>][matching_bonus]"
                                                                   value="<?php echo $level['matching_bonus']; ?>" step="0.1" min="0" max="30">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Leadership Bonus (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[<?php echo $index; ?>][leadership_bonus]"
                                                                   value="<?php echo $level['leadership_bonus']; ?>" step="0.1" min="0" max="10">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="level-actions">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLevel(this)">
                                                    <i class="fas fa-trash me-1"></i>Remove Level
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <!-- Default levels for new plan -->
                                        <div class="level-card" data-level-id="">
                                            <div class="level-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-crown me-2"></i>Level 1
                                                    </h6>
                                                    <span class="level-badge">Associate</span>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Level Name</label>
                                                        <input type="text" class="form-control" name="levels[0][level_name]" value="Associate" required>
                                                        <input type="hidden" name="levels[0][level_id]" value="">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Monthly Target (₹)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[0][monthly_target]" value="1000000" step="1000" min="0">
                                                            <div class="input-group-text">₹</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Direct Commission (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[0][direct_commission]" value="5" step="0.1" min="0" max="50">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Team Commission (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[0][team_commission]" value="2" step="0.1" min="0" max="20">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Level Bonus (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[0][level_bonus]" value="0" step="0.1" min="0" max="20">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Matching Bonus (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[0][matching_bonus]" value="0" step="0.1" min="0" max="30">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Leadership Bonus (%)</label>
                                                        <div class="commission-input">
                                                            <input type="number" class="form-control" name="levels[0][leadership_bonus]" value="0" step="0.1" min="0" max="10">
                                                            <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-center">
                                        <button type="button" class="btn btn-add-level" onclick="addNewLevel()">
                                            <i class="fas fa-plus me-2"></i>Add New Level
                                        </button>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(1)">
                                            <i class="fas fa-arrow-left me-2"></i>Previous
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                            <i class="fas fa-arrow-right me-2"></i>Next: Preview & Test
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Preview and Test -->
                            <div class="form-step" id="step3">
                                <div class="builder-section">
                                    <h4 class="mb-4">
                                        <i class="fas fa-calculator text-primary me-2"></i>Preview & Test Plan
                                    </h4>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5>Commission Structure Preview</h5>

                                            <div id="planPreview">
                                                <div class="commission-preview">
                                                    <h6>Plan Summary</h6>
                                                    <div id="planSummary">
                                                        <!-- Summary will be populated by JavaScript -->
                                                    </div>
                                                </div>

                                                <div class="commission-preview">
                                                    <h6>Test Calculation</h6>
                                                    <div class="mb-3">
                                                        <label class="form-label">Test Property Value (₹)</label>
                                                        <input type="number" class="form-control" id="testAmount" value="5000000" min="100000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Select Level to Test</label>
                                                        <select class="form-select" id="testLevel">
                                                            <option value="">Choose level...</option>
                                                            <!-- Options will be populated by JavaScript -->
                                                        </select>
                                                    </div>
                                                    <button type="button" class="btn btn-info" onclick="testCalculation()">
                                                        <i class="fas fa-calculator me-2"></i>Test Calculation
                                                    </button>
                                                </div>

                                                <div id="testResults" class="commission-preview" style="display: none;">
                                                    <h6>Calculation Results</h6>
                                                    <div id="calculationBreakdown">
                                                        <!-- Results will be shown here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <h5>Plan Statistics</h5>

                                            <div class="commission-summary">
                                                <div class="summary-row">
                                                    <span>Total Levels:</span>
                                                    <span id="totalLevels">0</span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Max Direct Commission:</span>
                                                    <span id="maxDirect">0%</span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Max Team Commission:</span>
                                                    <span id="maxTeam">0%</span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Max Total Potential:</span>
                                                    <span id="maxPotential">0%</span>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <h6>Level Distribution</h6>
                                                <div id="levelDistribution">
                                                    <!-- Level distribution chart will be shown here -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(2)">
                                            <i class="fas fa-arrow-left me-2"></i>Previous
                                        </button>
                                        <button type="button" class="btn btn-success btn-lg" onclick="nextStep(4)">
                                            <i class="fas fa-check me-2"></i>Review & Save
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Review and Save -->
                            <div class="form-step" id="step4">
                                <div class="builder-section">
                                    <h4 class="mb-4">
                                        <i class="fas fa-check-circle text-primary me-2"></i>Review & Save Plan
                                    </h4>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Ready to save your commission plan!</strong>
                                        Please review all settings before saving.
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Plan Summary</h5>
                                            <div id="finalSummary">
                                                <!-- Final summary will be shown here -->
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Final Configuration</h5>
                                            <div class="commission-summary">
                                                <div class="summary-row">
                                                    <span>Plan Status:</span>
                                                    <span class="text-warning">Draft</span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Created By:</span>
                                                    <span><?php echo htmlspecialchars($associate_name); ?></span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Created Date:</span>
                                                    <span><?php echo date('M d, Y H:i'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="activatePlan" name="activate_plan">
                                            <label class="form-check-label" for="activatePlan">
                                                <strong>Activate this plan immediately</strong> (will deactivate current active plan)
                                            </label>
                                        </div>

                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="setAsDefault" name="set_as_default">
                                            <label class="form-check-label" for="setAsDefault">
                                                <strong>Set as default plan</strong> for new associates
                                            </label>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(3)">
                                            <i class="fas fa-arrow-left me-2"></i>Previous
                                        </button>
                                        <button type="submit" name="save_plan" class="btn btn-save">
                                            <i class="fas fa-save me-2"></i>Save Commission Plan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let levelsData = [];

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateStepIndicator();
            populateTestLevels();
            updatePlanSummary();
        });

        function nextStep(step) {
            if (validateCurrentStep()) {
                currentStep = step;
                showStep(step);
                updateStepIndicator();
            }
        }

        function prevStep(step) {
            currentStep = step;
            showStep(step);
            updateStepIndicator();
        }

        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(stepDiv => {
                stepDiv.classList.remove('active');
            });

            // Show current step
            document.getElementById('step' + step).classList.add('active');

            // Update step indicators
            updateStepIndicator();
        }

        function updateStepIndicator() {
            document.querySelectorAll('.step').forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index + 1 < currentStep) {
                    step.classList.add('completed');
                } else if (index + 1 === currentStep) {
                    step.classList.add('active');
                }
            });
        }

        function validateCurrentStep() {
            switch(currentStep) {
                case 1:
                    const planName = document.querySelector('input[name="plan_name"]').value;
                    const planCode = document.querySelector('input[name="plan_code"]').value;
                    return planName.trim() !== '' && planCode.trim() !== '';
                case 2:
                    // Check if at least one level is configured
                    const levels = document.querySelectorAll('.level-card');
                    return levels.length > 0;
                case 3:
                    return true; // Preview step doesn't need validation
                case 4:
                    return true; // Final step doesn't need validation
                default:
                    return true;
            }
        }

        function addNewLevel() {
            const container = document.getElementById('levelsContainer');
            const levelIndex = container.children.length;

            const levelCard = document.createElement('div');
            levelCard.className = 'level-card';
            levelCard.innerHTML = `
                <div class="level-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-crown me-2"></i>Level ${levelIndex + 1}
                        </h6>
                        <span class="level-badge">New Level</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Level Name</label>
                            <input type="text" class="form-control" name="levels[${levelIndex}][level_name]" required>
                            <input type="hidden" name="levels[${levelIndex}][level_id]" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Monthly Target (₹)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelIndex}][monthly_target]" value="0" step="1000" min="0">
                                <div class="input-group-text">₹</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Direct Commission (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelIndex}][direct_commission]" value="0" step="0.1" min="0" max="50">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Team Commission (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelIndex}][team_commission]" value="0" step="0.1" min="0" max="20">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Level Bonus (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelIndex}][level_bonus]" value="0" step="0.1" min="0" max="20">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Matching Bonus (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelIndex}][matching_bonus]" value="0" step="0.1" min="0" max="30">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Leadership Bonus (%)</label>
                            <div class="commission-input">
                                <input type="number" class="form-control" name="levels[${levelIndex}][leadership_bonus]" value="0" step="0.1" min="0" max="10">
                                <div class="input-group-text">%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="level-actions">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLevel(this)">
                        <i class="fas fa-trash me-1"></i>Remove Level
                    </button>
                </div>
            `;

            container.appendChild(levelCard);
            updatePlanSummary();
        }

        function removeLevel(button) {
            if (confirm('Are you sure you want to remove this level?')) {
                button.closest('.level-card').remove();
                updatePlanSummary();
            }
        }

        function populateTestLevels() {
            const testLevelSelect = document.getElementById('testLevel');
            const levelCards = document.querySelectorAll('.level-card');

            levelCards.forEach((card, index) => {
                const levelName = card.querySelector('input[name*="[level_name]"]').value;
                const option = document.createElement('option');
                option.value = levelName;
                option.textContent = `Level ${index + 1}: ${levelName}`;
                testLevelSelect.appendChild(option);
            });
        }

        function updatePlanSummary() {
            const levelCards = document.querySelectorAll('.level-card');
            const summaryDiv = document.getElementById('planSummary');
            const totalLevelsDiv = document.getElementById('totalLevels');

            let maxDirect = 0;
            let maxTeam = 0;
            let maxPotential = 0;

            let summaryHTML = `
                <div class="summary-row">
                    <span>Total Levels:</span>
                    <span>${levelCards.length}</span>
                </div>
            `;

            levelCards.forEach((card, index) => {
                const levelName = card.querySelector('input[name*="[level_name]"]').value;
                const directComm = parseFloat(card.querySelector('input[name*="[direct_commission]"]').value) || 0;
                const teamComm = parseFloat(card.querySelector('input[name*="[team_commission]"]').value) || 0;
                const levelBonus = parseFloat(card.querySelector('input[name*="[level_bonus]"]').value) || 0;
                const matchingBonus = parseFloat(card.querySelector('input[name*="[matching_bonus]"]').value) || 0;
                const leadershipBonus = parseFloat(card.querySelector('input[name*="[leadership_bonus]"]').value) || 0;

                const levelTotal = directComm + teamComm + levelBonus + matchingBonus + leadershipBonus;

                maxDirect = Math.max(maxDirect, directComm);
                maxTeam = Math.max(maxTeam, teamComm);
                maxPotential = Math.max(maxPotential, levelTotal);

                summaryHTML += `
                    <div class="summary-row">
                        <span><strong>${levelName}:</strong></span>
                        <span>${levelTotal.toFixed(1)}%</span>
                    </div>
                `;
            });

            summaryHTML += `
                <div class="summary-row" style="border-top: 2px solid #dee2e6; margin-top: 10px; padding-top: 10px;">
                    <span><strong>Maximum Potential:</strong></span>
                    <span class="text-success">${maxPotential.toFixed(1)}%</span>
                </div>
            `;

            summaryDiv.innerHTML = summaryHTML;
            totalLevelsDiv.textContent = levelCards.length;
            document.getElementById('maxDirect').textContent = maxDirect.toFixed(1) + '%';
            document.getElementById('maxTeam').textContent = maxTeam.toFixed(1) + '%';
            document.getElementById('maxPotential').textContent = maxPotential.toFixed(1) + '%';
        }

        function testCalculation() {
            const testAmount = parseFloat(document.getElementById('testAmount').value);
            const testLevel = document.getElementById('testLevel').value;

            if (!testAmount || !testLevel) {
                alert('Please enter test amount and select a level');
                return;
            }

            // Find the selected level data
            const levelCards = document.querySelectorAll('.level-card');
            let selectedLevelData = null;

            for (let card of levelCards) {
                const levelName = card.querySelector('input[name*="[level_name]"]').value;
                if (levelName === testLevel) {
                    selectedLevelData = {
                        direct: parseFloat(card.querySelector('input[name*="[direct_commission]"]').value) || 0,
                        team: parseFloat(card.querySelector('input[name*="[team_commission]"]').value) || 0,
                        level: parseFloat(card.querySelector('input[name*="[level_bonus]"]').value) || 0,
                        matching: parseFloat(card.querySelector('input[name*="[matching_bonus]"]').value) || 0,
                        leadership: parseFloat(card.querySelector('input[name*="[leadership_bonus]"]').value) || 0
                    };
                    break;
                }
            }

            if (!selectedLevelData) {
                alert('Level data not found');
                return;
            }

            // Calculate commissions
            const directComm = (testAmount * selectedLevelData.direct) / 100;
            const teamComm = (testAmount * selectedLevelData.team) / 100;
            const levelBonus = (testAmount * selectedLevelData.level) / 100;
            const matchingBonus = (testAmount * selectedLevelData.matching) / 100;
            const leadershipBonus = (testAmount * selectedLevelData.leadership) / 100;

            const total = directComm + teamComm + levelBonus + matchingBonus + leadershipBonus;

            // Display results
            const resultsDiv = document.getElementById('testResults');
            const breakdownDiv = document.getElementById('calculationBreakdown');

            breakdownDiv.innerHTML = `
                <h6>Test Results for ${testLevel}</h6>
                <div class="summary-row">
                    <span>Property Value:</span>
                    <span>₹${testAmount.toLocaleString('en-IN')}</span>
                </div>
                <div class="summary-row">
                    <span>Direct Commission (${selectedLevelData.direct}%):</span>
                    <span class="text-success">₹${directComm.toLocaleString('en-IN')}</span>
                </div>
                <div class="summary-row">
                    <span>Team Commission (${selectedLevelData.team}%):</span>
                    <span class="text-info">₹${teamComm.toLocaleString('en-IN')}</span>
                </div>
                ${levelBonus > 0 ? `<div class="summary-row">
                    <span>Level Bonus (${selectedLevelData.level}%):</span>
                    <span class="text-warning">₹${levelBonus.toLocaleString('en-IN')}</span>
                </div>` : ''}
                ${matchingBonus > 0 ? `<div class="summary-row">
                    <span>Matching Bonus (${selectedLevelData.matching}%):</span>
                    <span class="text-primary">₹${matchingBonus.toLocaleString('en-IN')}</span>
                </div>` : ''}
                ${leadershipBonus > 0 ? `<div class="summary-row">
                    <span>Leadership Bonus (${selectedLevelData.leadership}%):</span>
                    <span class="text-secondary">₹${leadershipBonus.toLocaleString('en-IN')}</span>
                </div>` : ''}
                <div class="summary-row" style="border-top: 2px solid #28a745; margin-top: 10px; padding-top: 10px;">
                    <span><strong>Total Commission:</strong></span>
                    <span class="text-success"><strong>₹${total.toLocaleString('en-IN')} (${(total/testAmount*100).toFixed(2)}%)</strong></span>
                </div>
            `;

            resultsDiv.style.display = 'block';
        }

        // Add event listeners for real-time updates
        document.addEventListener('input', function(e) {
            if (e.target.closest('.level-card')) {
                updatePlanSummary();
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.closest('.level-card')) {
                updatePlanSummary();
            }
        });

        // Form validation before submission
        document.getElementById('planBuilderForm').addEventListener('submit', function(e) {
            if (!validateCurrentStep()) {
                e.preventDefault();
                alert('Please complete the current step before saving.');
                return false;
            }

            // Show loading state
            const submitBtn = e.target.querySelector('button[name="save_plan"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;

            // Re-enable after 3 seconds as fallback
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    </script>
</body>
</html>
