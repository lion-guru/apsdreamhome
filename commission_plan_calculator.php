<?php
/**
 * Commission Plan Calculator
 * Advanced calculator for testing different commission scenarios
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
    $_SESSION['error_message'] = "You don't have permission to access plan calculator.";
    header("Location: associate_dashboard.php");
    exit();
}

$associate_name = $_SESSION['associate_name'];

// Get all available plans
$plans_query = "SELECT * FROM mlm_commission_plans WHERE status != 'archived' ORDER BY status DESC, created_at DESC";
$plans_result = $conn->query($plans_query);
$plans = $plans_result->fetch_all(MYSQLI_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['calculate_scenario'])) {
        $result = calculateScenario($_POST);
        if ($result['success']) {
            $_SESSION['calculation_result'] = $result;
            header("Location: commission_plan_calculator.php?calculated=1");
            exit();
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

function calculateScenario($data) {
    global $conn;

    try {
        // Get plan details
        $plan_query = "SELECT * FROM mlm_commission_plans WHERE id = ?";
        $stmt = $conn->prepare($plan_query);
        $stmt->bind_param("i", $data['plan_id']);
        $stmt->execute();
        $plan = $stmt->get_result()->fetch_assoc();

        if (!$plan) {
            return ['success' => false, 'message' => 'Plan not found'];
        }

        // Get plan levels
        $levels_query = "SELECT * FROM mlm_plan_levels WHERE plan_id = ? ORDER BY level_order";
        $stmt = $conn->prepare($levels_query);
        $stmt->bind_param("i", $data['plan_id']);
        $stmt->execute();
        $levels = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $results = [];
        $total_associates = 0;
        $total_commission = 0;
        $total_payout = 0;

        // Calculate for each level
        foreach ($levels as $level) {
            $level_name = $level['level_name'];
            $num_associates = $data['associates_' . strtolower(str_replace(' ', '_', $level_name))] ?? 0;

            if ($num_associates <= 0) continue;

            $total_associates += $num_associates;

            // Calculate commissions for this level
            $level_results = calculateLevelCommissions($level, $num_associates, $data);

            $results[$level_name] = $level_results;
            $total_commission += $level_results['total_commission'];
            $total_payout += $level_results['total_payout'];
        }

        return [
            'success' => true,
            'plan' => $plan,
            'results' => $results,
            'summary' => [
                'total_associates' => $total_associates,
                'total_commission' => $total_commission,
                'total_payout' => $total_payout,
                'company_margin' => ($data['total_sales'] - $total_payout),
                'company_margin_percentage' => (($data['total_sales'] - $total_payout) / $data['total_sales']) * 100
            ]
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error calculating scenario: ' . $e->getMessage()];
    }
}

function calculateLevelCommissions($level, $num_associates, $data) {
    $property_value = $data['property_value'];
    $monthly_sales = $property_value * $num_associates;

    // Direct Commission
    $direct_commission = ($property_value * $level['direct_commission'] / 100) * $num_associates;

    // Team Commission (simplified calculation)
    $team_commission = ($property_value * $level['team_commission'] / 100) * $num_associates;

    // Level Bonus (simplified)
    $level_bonus = ($property_value * $level['level_bonus'] / 100) * $num_associates;

    // Matching Bonus (simplified)
    $matching_bonus = ($property_value * $level['matching_bonus'] / 100) * $num_associates;

    // Leadership Bonus (simplified)
    $leadership_bonus = ($property_value * $level['leadership_bonus'] / 100) * $num_associates;

    // Performance Bonus (if target achieved)
    $performance_bonus = 0;
    if ($monthly_sales >= $level['monthly_target']) {
        $performance_bonus = ($property_value * $level['performance_bonus'] / 100) * $num_associates;
    }

    $total_commission = $direct_commission + $team_commission + $level_bonus + $matching_bonus + $leadership_bonus + $performance_bonus;
    $total_payout = $total_commission; // In real scenario, this would be more complex

    return [
        'num_associates' => $num_associates,
        'monthly_sales' => $monthly_sales,
        'commissions' => [
            'direct' => $direct_commission,
            'team' => $team_commission,
            'level_bonus' => $level_bonus,
            'matching' => $matching_bonus,
            'leadership' => $leadership_bonus,
            'performance' => $performance_bonus
        ],
        'total_commission' => $total_commission,
        'total_payout' => $total_payout,
        'avg_commission_per_associate' => $num_associates > 0 ? $total_commission / $num_associates : 0
    ];
}

// Get calculation result from session
$calculation_result = $_SESSION['calculation_result'] ?? null;
if (isset($_GET['calculated']) && $calculation_result) {
    unset($_SESSION['calculation_result']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Plan Calculator - APS Dream Homes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        .calculator-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            border-left: 5px solid var(--primary-color);
        }

        .result-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--success-color);
        }

        .summary-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin: 1rem 0;
        }

        .commission-breakdown {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0;
        }

        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .breakdown-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .breakdown-row.total {
            border-top: 2px solid var(--success-color);
            margin-top: 10px;
            padding-top: 10px;
        }

        .btn-calculate {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .input-group-text {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }

        .scenario-card {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border: 2px solid var(--success-color);
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .comparison-table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }

        .profitability-indicator {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
        }

        .profitable {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            color: white;
        }

        .unprofitable {
            background: linear-gradient(45deg, var(--danger-color), #dc3545);
            color: white;
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
                                <i class="fas fa-calculator text-primary me-2"></i>Commission Plan Calculator
                            </h1>
                            <p class="text-muted">
                                Test and analyze different commission scenarios
                            </p>
                        </div>

                        <!-- Alerts -->
                        <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); endif; ?>

                        <div class="row">
                            <!-- Calculator Form -->
                            <div class="col-md-6">
                                <div class="calculator-section">
                                    <h4 class="mb-4">
                                        <i class="fas fa-edit text-primary me-2"></i>Scenario Setup
                                    </h4>

                                    <form method="post" action="">
                                        <div class="mb-4">
                                            <label class="form-label">
                                                <i class="fas fa-layer-group me-1"></i>Select Commission Plan
                                            </label>
                                            <select class="form-select" name="plan_id" required onchange="loadPlanLevels(this.value)">
                                                <option value="">Choose a plan...</option>
                                                <?php foreach ($plans as $plan): ?>
                                                <option value="<?php echo $plan['id']; ?>">
                                                    <?php echo htmlspecialchars($plan['plan_name']); ?> (<?php echo $plan['plan_code']; ?>)
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">
                                                <i class="fas fa-rupee-sign me-1"></i>Property Value per Sale (₹)
                                            </label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="property_value"
                                                       value="5000000" min="100000" step="100000" required>
                                                <span class="input-group-text">₹</span>
                                            </div>
                                            <small class="text-muted">Average value of properties being sold</small>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">
                                                <i class="fas fa-chart-line me-1"></i>Total Monthly Sales Volume (₹)
                                            </label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="total_sales"
                                                       value="100000000" min="1000000" step="1000000" required>
                                                <span class="input-group-text">₹</span>
                                            </div>
                                            <small class="text-muted">Total sales across all associates</small>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">
                                                <i class="fas fa-users me-1"></i>Associate Distribution by Level
                                            </label>
                                            <div id="levelInputs">
                                                <div class="text-center py-3 text-muted">
                                                    <i class="fas fa-arrow-down fa-2x mb-2"></i><br>
                                                    Select a plan above to see level inputs
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" name="calculate_scenario" class="btn btn-calculate">
                                                <i class="fas fa-calculator me-2"></i>Calculate Scenario
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Results -->
                            <div class="col-md-6">
                                <?php if ($calculation_result): ?>
                                <div class="calculator-section">
                                    <h4 class="mb-4">
                                        <i class="fas fa-chart-bar text-primary me-2"></i>Calculation Results
                                    </h4>

                                    <!-- Summary Cards -->
                                    <div class="summary-card">
                                        <h3><?php echo htmlspecialchars($calculation_result['plan']['plan_name']); ?></h3>
                                        <p class="mb-0"><?php echo htmlspecialchars($calculation_result['plan']['description']); ?></p>
                                    </div>

                                    <!-- Key Metrics -->
                                    <div class="row mb-4">
                                        <div class="col-6">
                                            <div class="result-card text-center">
                                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                                <h4><?php echo number_format($calculation_result['summary']['total_associates']); ?></h4>
                                                <small class="text-muted">Total Associates</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="result-card text-center">
                                                <i class="fas fa-rupee-sign fa-2x text-success mb-2"></i>
                                                <h4>₹<?php echo number_format($calculation_result['summary']['total_payout']); ?></h4>
                                                <small class="text-muted">Total Payout</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Profitability -->
                                    <div class="result-card">
                                        <h6>Company Profitability</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Total Sales:</strong> ₹<?php echo number_format($calculation_result['summary']['total_commission'] + $calculation_result['summary']['company_margin']); ?>
                                            </div>
                                            <div class="profitability-indicator <?php echo $calculation_result['summary']['company_margin'] > 0 ? 'profitable' : 'unprofitable'; ?>">
                                                <?php echo $calculation_result['summary']['company_margin'] > 0 ? 'Profitable' : 'Loss'; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Company Margin:</strong> ₹<?php echo number_format($calculation_result['summary']['company_margin']); ?>
                                            </div>
                                            <div>
                                                <strong>Margin %:</strong> <?php echo number_format($calculation_result['summary']['company_margin_percentage'], 2); ?>%
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detailed Breakdown -->
                                    <div class="result-card">
                                        <h6>Commission Breakdown by Level</h6>

                                        <?php foreach ($calculation_result['results'] as $level_name => $level_data): ?>
                                        <div class="commission-breakdown">
                                            <h6><?php echo $level_name; ?> (<?php echo $level_data['num_associates']; ?> associates)</h6>

                                            <div class="breakdown-row">
                                                <span>Monthly Sales:</span>
                                                <span>₹<?php echo number_format($level_data['monthly_sales']); ?></span>
                                            </div>

                                            <div class="breakdown-row">
                                                <span>Direct Commission:</span>
                                                <span class="text-success">₹<?php echo number_format($level_data['commissions']['direct']); ?></span>
                                            </div>

                                            <div class="breakdown-row">
                                                <span>Team Commission:</span>
                                                <span class="text-info">₹<?php echo number_format($level_data['commissions']['team']); ?></span>
                                            </div>

                                            <?php if ($level_data['commissions']['level_bonus'] > 0): ?>
                                            <div class="breakdown-row">
                                                <span>Level Bonus:</span>
                                                <span class="text-warning">₹<?php echo number_format($level_data['commissions']['level_bonus']); ?></span>
                                            </div>
                                            <?php endif; ?>

                                            <?php if ($level_data['commissions']['matching'] > 0): ?>
                                            <div class="breakdown-row">
                                                <span>Matching Bonus:</span>
                                                <span class="text-primary">₹<?php echo number_format($level_data['commissions']['matching']); ?></span>
                                            </div>
                                            <?php endif; ?>

                                            <?php if ($level_data['commissions']['leadership'] > 0): ?>
                                            <div class="breakdown-row">
                                                <span>Leadership Bonus:</span>
                                                <span class="text-secondary">₹<?php echo number_format($level_data['commissions']['leadership']); ?></span>
                                            </div>
                                            <?php endif; ?>

                                            <?php if ($level_data['commissions']['performance'] > 0): ?>
                                            <div class="breakdown-row">
                                                <span>Performance Bonus:</span>
                                                <span class="text-danger">₹<?php echo number_format($level_data['commissions']['performance']); ?></span>
                                            </div>
                                            <?php endif; ?>

                                            <div class="breakdown-row total">
                                                <span><strong>Total for Level:</strong></span>
                                                <span class="text-success"><strong>₹<?php echo number_format($level_data['total_commission']); ?></strong></span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>

                                        <div class="breakdown-row total" style="background: linear-gradient(135deg, var(--success-color), #20c997); color: white; margin-top: 1rem; padding: 1rem; border-radius: 10px;">
                                            <span><strong>Grand Total Payout:</strong></span>
                                            <span><strong>₹<?php echo number_format($calculation_result['summary']['total_payout']); ?></strong></span>
                                        </div>
                                    </div>

                                    <!-- Charts -->
                                    <div class="result-card">
                                        <h6>Commission Distribution</h6>
                                        <div class="chart-container">
                                            <canvas id="commissionChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="calculator-section">
                                    <h4 class="mb-4">
                                        <i class="fas fa-chart-line text-primary me-2"></i>Results Preview
                                    </h4>

                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-calculator fa-4x mb-3"></i>
                                        <h5>Ready to Calculate</h5>
                                        <p>Fill out the scenario form and click "Calculate Scenario" to see detailed results</p>
                                    </div>

                                    <div class="scenario-card">
                                        <h6><i class="fas fa-lightbulb text-warning me-2"></i>Pro Tips</h6>
                                        <ul class="mb-0">
                                            <li>Test different associate distributions to optimize payouts</li>
                                            <li>Higher property values generally mean better margins</li>
                                            <li>Balance between associate earnings and company profitability</li>
                                            <li>Use realistic numbers based on your market conditions</li>
                                        </ul>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Chart data
        <?php if ($calculation_result): ?>
        const chartData = {
            labels: [<?php
                $labels = [];
                $data = [];
                foreach ($calculation_result['results'] as $level_name => $level_data) {
                    $labels[] = "'$level_name'";
                    $data[] = $level_data['total_commission'];
                }
                echo implode(', ', $labels);
            ?>],
            datasets: [{
                label: 'Commission by Level',
                data: [<?php echo implode(', ', $data); ?>],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(201, 203, 207, 0.8)'
                ],
                borderWidth: 2
            }]
        };
        <?php endif; ?>

        function loadPlanLevels(planId) {
            if (!planId) {
                document.getElementById('levelInputs').innerHTML = `
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-arrow-down fa-2x mb-2"></i><br>
                        Select a plan above to see level inputs
                    </div>
                `;
                return;
            }

            // In a real implementation, this would make an AJAX call to get plan levels
            // For now, we'll show a generic structure
            document.getElementById('levelInputs').innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Plan levels will be loaded here. In a full implementation, this would show input fields for each level in the selected plan.
                </div>
                <div class="mb-3">
                    <label class="form-label">Associate Level Distribution</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Entry Level Associates</label>
                            <input type="number" class="form-control" name="associates_associate" value="50" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mid Level Associates</label>
                            <input type="number" class="form-control" name="associates_sr_associate" value="30" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Senior Associates</label>
                            <input type="number" class="form-control" name="associates_bdm" value="15" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Top Level Associates</label>
                            <input type="number" class="form-control" name="associates_sr_bdm" value="5" min="0">
                        </div>
                    </div>
                </div>
            `;
        }

        // Initialize chart if data exists
        <?php if ($calculation_result): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('commissionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = <?php echo array_sum($data); ?>;
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ₹' + context.parsed.toLocaleString('en-IN') + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        });
        <?php endif; ?>

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const planId = document.querySelector('select[name="plan_id"]').value;
            const propertyValue = document.querySelector('input[name="property_value"]').value;
            const totalSales = document.querySelector('input[name="total_sales"]').value;

            if (!planId || !propertyValue || !totalSales) {
                e.preventDefault();
                alert('Please fill all required fields');
                return false;
            }

            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Calculating...';
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
