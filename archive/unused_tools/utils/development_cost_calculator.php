<?php
/**
 * Development Cost Calculator with Commission Integration
 * Calculates plot rates including all development costs and commission structures
 */

require_once 'includes/config.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once 'includes/associate_permissions.php';
require_once 'includes/hybrid_commission_system.php';

// Initialize database connection
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Check if connection is successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
$associate_name = $_SESSION['associate_name'];

// Initialize hybrid commission system
$hybrid_system = new HybridRealEstateCommission($conn);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['calculate_plot_rate'])) {
        $result = calculatePlotRate($_POST);
        if ($result['success']) {
            $_SESSION['calculation_result'] = $result;
            header("Location: development_cost_calculator.php?calculated=1");
            exit();
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }

    if (isset($_POST['save_cost_breakdown'])) {
        $result = saveCostBreakdown($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

function calculatePlotRate($data) {
    global $hybrid_system, $associate_id;

    try {
        // Check if hybrid system is available
        if (!$hybrid_system) {
            return ['success' => false, 'message' => 'Commission system not available'];
        }

        $land_cost = floatval($data['land_cost']);
        $area_sqft = floatval($data['area_sqft']);
        $profit_margin = floatval($data['profit_margin']);

        // Calculate development costs
        $development_costs = [];
        $total_development_cost = 0;

        if (isset($data['cost_types']) && is_array($data['cost_types'])) {
            foreach ($data['cost_types'] as $index => $cost_type) {
                if (!empty($cost_type) && isset($data['cost_amounts'][$index])) {
                    $amount = floatval($data['cost_amounts'][$index]);
                    $description = $data['cost_descriptions'][$index] ?? '';

                    $development_costs[] = [
                        'type' => $cost_type,
                        'description' => $description,
                        'amount' => $amount
                    ];

                    $total_development_cost += $amount;
                }
            }
        }

        // Calculate plot rate using hybrid system
        $rate_calculation = $hybrid_system->calculatePlotRate($land_cost, $total_development_cost, $area_sqft, $profit_margin);

        return [
            'success' => true,
            'calculation' => $rate_calculation,
            'development_costs' => $development_costs,
            'total_development_cost' => $total_development_cost,
            'land_cost' => $land_cost,
            'area_sqft' => $area_sqft,
            'profit_margin' => $profit_margin
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error calculating plot rate: ' . $e->getMessage()];
    }
}

function saveCostBreakdown($data) {
    global $conn, $hybrid_system;

    // Check if connection is available
    if (!$conn || $conn->connect_error) {
        return ['success' => false, 'message' => 'Database connection not available'];
    }

    try {
        $property_id = intval($data['property_id']);
        $cost_breakdown = [];

        if (isset($data['cost_types']) && is_array($data['cost_types'])) {
            foreach ($data['cost_types'] as $index => $cost_type) {
                if (!empty($cost_type) && isset($data['cost_amounts'][$index])) {
                    $cost_breakdown[] = [
                        'type' => $cost_type,
                        'description' => $data['cost_descriptions'][$index] ?? '',
                        'amount' => floatval($data['cost_amounts'][$index])
                    ];
                }
            }
        }

        return $hybrid_system->saveDevelopmentCosts($property_id, $cost_breakdown);

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error saving cost breakdown: ' . $e->getMessage()];
    }
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
    <title>Development Cost Calculator - APS Dream Homes</title>

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

        .cost-breakdown {
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

        .cost-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .cost-input-group .form-control {
            flex: 1;
        }

        .cost-input-group .btn {
            flex-shrink: 0;
        }

        .summary-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin: 1rem 0;
        }

        .profitability-indicator {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
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
                        <?php echo h($associate_name); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="associate_dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="hybrid_commission_dashboard.php">
                            <i class="fas fa-rupee-sign me-2"></i>Hybrid Commission Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="property_management.php">
                            <i class="fas fa-building me-2"></i>Property Management
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
                                <i class="fas fa-calculator text-primary me-2"></i>Development Cost Calculator
                            </h1>
                            <p class="text-muted">
                                Calculate plot rates including development costs and commission structures
                            </p>
                        </div>

                        <!-- Alerts -->
                        <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); endif; ?>

                        <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); endif; ?>

                        <div class="row">
                            <!-- Calculator Form -->
                            <div class="col-md-6">
                                <div class="calculator-section">
                                    <h4 class="mb-4">
                                        <i class="fas fa-edit text-primary me-2"></i>Project Cost Inputs
                                    </h4>

                                    <form method="post" action="">
                                        <!-- Basic Project Info -->
                                        <div class="mb-4">
                                            <label class="form-label">
                                                <i class="fas fa-tag me-1"></i>Project Name
                                            </label>
                                            <input type="text" class="form-control" name="project_name"
                                                   placeholder="e.g., Green Valley Phase 2" required>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <i class="fas fa-ruler me-1"></i>Area (sq ft)
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="area_sqft"
                                                           value="1000" min="1" step="1" required>
                                                    <span class="input-group-text">sq ft</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <i class="fas fa-percentage me-1"></i>Profit Margin (%)
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="profit_margin"
                                                           value="25" min="0" max="100" step="1" required>
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Land Cost -->
                                        <div class="mb-4">
                                            <label class="form-label">
                                                <i class="fas fa-mountain me-1"></i>Land Cost (₹)
                                            </label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="land_cost"
                                                       value="2000000" min="0" step="1000" required>
                                                <span class="input-group-text">₹</span>
                                            </div>
                                            <small class="text-muted">Cost of acquiring the land</small>
                                        </div>

                                        <!-- Development Costs -->
                                        <div class="mb-4">
                                            <label class="form-label">
                                                <i class="fas fa-tools me-1"></i>Development Costs
                                            </label>

                                            <div id="costBreakdownContainer">
                                                <!-- Construction Cost -->
                                                <div class="cost-input-group">
                                                    <select class="form-control" name="cost_types[]">
                                                        <option value="construction">Construction</option>
                                                        <option value="infrastructure">Infrastructure</option>
                                                        <option value="legal">Legal</option>
                                                        <option value="marketing">Marketing</option>
                                                        <option value="commission">Commission</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                    <input type="number" class="form-control" name="cost_amounts[]" placeholder="Amount" min="0" step="1000">
                                                    <input type="text" class="form-control" name="cost_descriptions[]" placeholder="Description">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeCostRow(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Infrastructure Cost -->
                                                <div class="cost-input-group">
                                                    <select class="form-control" name="cost_types[]">
                                                        <option value="construction">Construction</option>
                                                        <option value="infrastructure">Infrastructure</option>
                                                        <option value="legal">Legal</option>
                                                        <option value="marketing">Marketing</option>
                                                        <option value="commission">Commission</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                    <input type="number" class="form-control" name="cost_amounts[]" placeholder="Amount" min="0" step="1000">
                                                    <input type="text" class="form-control" name="cost_descriptions[]" placeholder="Description">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeCostRow(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-outline-primary" onclick="addCostRow()">
                                                <i class="fas fa-plus me-1"></i>Add Cost Item
                                            </button>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" name="calculate_plot_rate" class="btn btn-calculate">
                                                <i class="fas fa-calculator me-2"></i>Calculate Plot Rate
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

                                    <!-- Summary Card -->
                                    <div class="summary-card">
                                        <h3><?php echo h($calculation_result['calculation']['final_rate_per_sqft']); ?> ₹/sq ft</h3>
                                        <p class="mb-0">Final Plot Rate</p>
                                    </div>

                                    <!-- Cost Breakdown -->
                                    <div class="result-card">
                                        <h6>Cost Breakdown</h6>

                                        <div class="cost-breakdown">
                                            <div class="breakdown-row">
                                                <span>Land Cost:</span>
                                                <span>₹<?php echo number_format($calculation_result['land_cost']); ?></span>
                                            </div>

                                            <div class="breakdown-row">
                                                <span>Development Cost:</span>
                                                <span>₹<?php echo number_format($calculation_result['total_development_cost']); ?></span>
                                            </div>

                                            <div class="breakdown-row">
                                                <span>Commission Cost:</span>
                                                <span>₹<?php echo number_format($calculation_result['calculation']['commission_cost']); ?></span>
                                            </div>

                                            <div class="breakdown-row total">
                                                <span>Total Cost:</span>
                                                <span>₹<?php echo number_format($calculation_result['calculation']['total_cost_with_commission']); ?></span>
                                            </div>
                                        </div>

                                        <div class="cost-breakdown">
                                            <div class="breakdown-row">
                                                <span>Profit (<?php echo $calculation_result['profit_margin']; ?>%):</span>
                                                <span>₹<?php echo number_format($calculation_result['calculation']['profit_amount']); ?></span>
                                            </div>

                                            <div class="breakdown-row total">
                                                <span>Total Value:</span>
                                                <span>₹<?php echo number_format($calculation_result['calculation']['total_value']); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Development Cost Details -->
                                    <div class="result-card">
                                        <h6>Development Cost Details</h6>

                                        <?php foreach ($calculation_result['development_costs'] as $cost): ?>
                                        <div class="cost-breakdown">
                                            <div class="breakdown-row">
                                                <span><?php echo ucfirst($cost['type']); ?>:</span>
                                                <span>₹<?php echo number_format($cost['amount']); ?></span>
                                            </div>
                                            <?php if ($cost['description']): ?>
                                            <small class="text-muted"><?php echo h($cost['description']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Profitability Analysis -->
                                    <div class="result-card">
                                        <h6>Profitability Analysis</h6>

                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="profitability-indicator <?php echo $calculation_result['profit_margin'] >= 25 ? 'profitable' : 'unprofitable'; ?>">
                                                    <?php echo $calculation_result['profit_margin'] >= 25 ? 'Excellent' : 'Good'; ?>
                                                </div>
                                                <small>Margin Rating</small>
                                            </div>
                                            <div class="col-4">
                                                <h4 class="text-success">₹<?php echo number_format($calculation_result['calculation']['profit_amount']); ?></h4>
                                                <small>Total Profit</small>
                                            </div>
                                            <div class="col-4">
                                                <h4 class="text-info">₹<?php echo number_format($calculation_result['calculation']['total_value']); ?></h4>
                                                <small>Project Value</small>
                                            </div>
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
                                        <p>Fill out the cost inputs and click "Calculate Plot Rate" to see detailed results</p>
                                    </div>

                                    <div class="result-card">
                                        <h6><i class="fas fa-lightbulb text-warning me-2"></i>Pro Tips</h6>
                                        <ul class="mb-0">
                                            <li>Include all development costs for accurate calculation</li>
                                            <li>Commission costs are automatically calculated</li>
                                            <li>Adjust profit margin based on market conditions</li>
                                            <li>Save cost breakdowns for future reference</li>
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
        function addCostRow() {
            const container = document.getElementById('costBreakdownContainer');
            const newRow = document.createElement('div');
            newRow.className = 'cost-input-group';
            newRow.innerHTML = `
                <select class="form-control" name="cost_types[]">
                    <option value="construction">Construction</option>
                    <option value="infrastructure">Infrastructure</option>
                    <option value="legal">Legal</option>
                    <option value="marketing">Marketing</option>
                    <option value="commission">Commission</option>
                    <option value="other">Other</option>
                </select>
                <input type="number" class="form-control" name="cost_amounts[]" placeholder="Amount" min="0" step="1000">
                <input type="text" class="form-control" name="cost_descriptions[]" placeholder="Description">
                <button type="button" class="btn btn-outline-danger" onclick="removeCostRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(newRow);
        }

        function removeCostRow(button) {
            button.closest('.cost-input-group').remove();
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const landCost = document.querySelector('input[name="land_cost"]').value;
            const areaSqft = document.querySelector('input[name="area_sqft"]').value;
            const profitMargin = document.querySelector('input[name="profit_margin"]').value;

            if (!landCost || !areaSqft || !profitMargin) {
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

        // Auto-calculate totals
        function updateTotals() {
            const costInputs = document.querySelectorAll('input[name="cost_amounts[]"]');
            let total = 0;

            costInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            // Update total development cost display if exists
            const totalDisplay = document.getElementById('totalDevelopmentCost');
            if (totalDisplay) {
                totalDisplay.textContent = '₹' + total.toLocaleString('en-IN');
            }
        }

        // Add event listeners for auto-calculation
        document.addEventListener('input', function(e) {
            if (e.target.name === 'cost_amounts[]') {
                updateTotals();
            }
        });
    </script>
</body>
</html>
