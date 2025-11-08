<?php
/**
 * Associate Commission Dashboard
 * Shows comprehensive commission tracking and analytics
 */

require_once 'includes/config.php';
require_once 'includes/associate_permissions.php';
require_once 'includes/commission_system.php';

// Check authentication
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
$associate_name = $_SESSION['associate_name'];
$associate_level = $_SESSION['associate_level'];

// Check permissions
if (!canAccessModule($associate_id, 'commission_management')) {
    $_SESSION['error_message'] = "You don't have permission to access commission management.";
    header("Location: associate_dashboard.php");
    exit();
}

// Get commission summary
$monthly_commissions = getCommissionSummary($associate_id, 'monthly');
$yearly_commissions = getCommissionSummary($associate_id, 'yearly');

// Get pending commissions
$pending_query = "SELECT SUM(commission_amount) as pending_total FROM mlm_commissions
                  WHERE associate_id = ? AND status = 'pending'";
$stmt = $conn->prepare($pending_query);
$stmt->bind_param("i", $associate_id);
$stmt->execute();
$pending_result = $stmt->get_result()->fetch_assoc();
$pending_commissions = $pending_result['pending_total'] ?? 0;

// Get total earned
$total_earned_query = "SELECT SUM(commission_amount) as total_earned FROM mlm_commissions
                       WHERE associate_id = ? AND status = 'paid'";
$stmt = $conn->prepare($total_earned_query);
$stmt->bind_param("i", $associate_id);
$stmt->execute();
$total_earned_result = $stmt->get_result()->fetch_assoc();
$total_earned = $total_earned_result['total_earned'] ?? 0;

// Get recent payouts
$recent_payouts_query = "SELECT * FROM mlm_payouts
                         WHERE associate_id = ?
                         ORDER BY payout_date DESC LIMIT 5";
$stmt = $conn->prepare($recent_payouts_query);
$stmt->bind_param("i", $associate_id);
$stmt->execute();
$recent_payouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get commission targets
$targets_query = "SELECT * FROM mlm_commission_targets
                  WHERE associate_id = ? AND status = 'active'
                  ORDER BY target_period, end_date";
$stmt = $conn->prepare($targets_query);
$stmt->bind_param("i", $associate_id);
$stmt->execute();
$active_targets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$structure = getCommissionStructure();
$current_level_info = $structure[$associate_level];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Dashboard - APS Dream Homes</title>

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

        .commission-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .commission-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15);
        }

        .progress-circle {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
        }

        .progress-ring {
            transform: rotate(-90deg);
        }

        .target-progress {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }

        .target-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--success-color), var(--info-color));
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .commission-type-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            margin-bottom: 1rem;
        }

        .commission-type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .payout-card {
            border-left: 4px solid var(--success-color);
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d1ecf1; color: #0c5460; }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }

        .level-indicator {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .target-achievement {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin: 1rem 0;
        }

        .next-level-info {
            background: linear-gradient(135deg, var(--warning-color), #fd7e14);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin: 1rem 0;
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
                        <li><a class="dropdown-item" href="associate_profile.php">
                            <i class="fas fa-user me-2"></i>Profile
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
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="dashboard-container">
                    <div class="p-4">
                        <h5 class="mb-4">Commission Menu</h5>
                        <div class="list-group">
                            <a href="#overview" class="list-group-item list-group-item-action active">
                                <i class="fas fa-chart-line me-2"></i>Overview
                            </a>
                            <a href="#earnings" class="list-group-item list-group-item-action">
                                <i class="fas fa-rupee-sign me-2"></i>Earnings
                            </a>
                            <a href="#targets" class="list-group-item list-group-item-action">
                                <i class="fas fa-bullseye me-2"></i>Targets
                            </a>
                            <a href="#payouts" class="list-group-item list-group-item-action">
                                <i class="fas fa-credit-card me-2"></i>Payouts
                            </a>
                            <a href="#analytics" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-bar me-2"></i>Analytics
                            </a>
                            <?php if (canManageCommissions($associate_id)): ?>
                            <a href="#withdrawal" class="list-group-item list-group-item-action">
                                <i class="fas fa-wallet me-2"></i>Withdraw
                            </a>
                            <?php endif; ?>
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
                                <h1 class="mb-2">Commission Dashboard</h1>
                                <p class="mb-0 text-muted">Track your earnings and performance</p>
                            </div>
                            <div class="level-indicator">
                                <i class="fas fa-crown me-1"></i><?php echo $associate_level; ?>
                            </div>
                        </div>

                        <!-- Overview Section -->
                        <div id="overview" class="mb-5">
                            <h3 class="mb-4">Commission Overview</h3>

                            <!-- Key Metrics -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="commission-card">
                                        <i class="fas fa-rupee-sign fa-2x mb-3"></i>
                                        <h2>₹<?php echo number_format($total_earned); ?></h2>
                                        <h5>Total Earned</h5>
                                        <small>All time earnings</small>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="commission-card">
                                        <i class="fas fa-clock fa-2x mb-3"></i>
                                        <h2>₹<?php echo number_format($pending_commissions); ?></h2>
                                        <h5>Pending</h5>
                                        <small>Awaiting payout</small>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="commission-card">
                                        <i class="fas fa-percentage fa-2x mb-3"></i>
                                        <h2><?php echo $current_level_info['direct_commission']; ?>%</h2>
                                        <h5>Direct Rate</h5>
                                        <small>Your commission rate</small>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="commission-card">
                                        <i class="fas fa-trophy fa-2x mb-3"></i>
                                        <h2><?php echo $current_level_info['matching_bonus']; ?>%</h2>
                                        <h5>Matching Bonus</h5>
                                        <small>Team matching rate</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Level Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="target-achievement">
                                        <h5>Current Level Benefits</h5>
                                        <div class="row text-center mt-3">
                                            <div class="col-4">
                                                <strong>Direct</strong><br>
                                                <?php echo $current_level_info['direct_commission']; ?>%
                                            </div>
                                            <div class="col-4">
                                                <strong>Team</strong><br>
                                                <?php echo $current_level_info['team_commission']; ?>%
                                            </div>
                                            <div class="col-4">
                                                <strong>Matching</strong><br>
                                                <?php echo $current_level_info['matching_bonus']; ?>%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="next-level-info">
                                        <h5>Next Level: <?php echo array_keys($structure)[array_search($associate_level, array_keys($structure)) + 1] ?? 'Maximum'; ?></h5>
                                        <p>Target: ₹<?php echo number_format($current_level_info['target']); ?></p>
                                        <small>Keep growing to unlock higher commissions!</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings Section -->
                        <div id="earnings" class="mb-5">
                            <h3 class="mb-4">Earnings Breakdown</h3>

                            <!-- Monthly Earnings -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">This Month's Earnings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($monthly_commissions as $commission): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="commission-type-card card h-100">
                                                <div class="card-body text-center">
                                                    <h6><?php echo ucwords(str_replace('_', ' ', $commission['commission_type'])); ?></h6>
                                                    <h3 class="text-success">₹<?php echo number_format($commission['total_amount']); ?></h3>
                                                    <small class="text-muted"><?php echo $commission['count']; ?> transactions</small>
                                                    <span class="status-badge status-<?php echo $commission['status']; ?> float-end">
                                                        <?php echo ucfirst($commission['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Yearly Earnings Chart -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Yearly Commission Trend</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="yearlyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Targets Section -->
                        <div id="targets" class="mb-5">
                            <h3 class="mb-4">Performance Targets</h3>

                            <?php foreach ($active_targets as $target): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6><?php echo ucfirst($target['target_period']); ?> Target</h6>
                                            <small class="text-muted">
                                                <?php echo ucfirst(str_replace('_', ' ', $target['target_type'])); ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <h5>₹<?php echo number_format($target['achieved_amount']); ?></h5>
                                            <small class="text-muted">of ₹<?php echo number_format($target['target_amount']); ?></small>
                                        </div>
                                    </div>

                                    <div class="target-progress">
                                        <div class="target-progress-bar" style="width: <?php
                                            echo min(100, ($target['achieved_amount'] / $target['target_amount']) * 100);
                                        ?>%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted">
                                            Progress: <?php echo round(($target['achieved_amount'] / $target['target_amount']) * 100, 1); ?>%
                                        </small>
                                        <small class="text-success">
                                            Reward: ₹<?php echo number_format($target['reward_amount']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Payouts Section -->
                        <div id="payouts" class="mb-5">
                            <h3 class="mb-4">Recent Payouts</h3>

                            <?php if (!empty($recent_payouts)): ?>
                            <?php foreach ($recent_payouts as $payout): ?>
                            <div class="payout-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Payout #<?php echo $payout['id']; ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($payout['payout_date'])); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="text-success">₹<?php echo number_format($payout['amount']); ?></h5>
                                        <span class="status-badge status-<?php echo $payout['status']; ?>">
                                            <?php echo ucfirst($payout['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No payouts yet</h6>
                                <p class="text-muted">Your commission payouts will appear here</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Withdrawal Section -->
                        <?php if (canManageCommissions($associate_id)): ?>
                        <div id="withdrawal" class="mb-5">
                            <h3 class="mb-4">Withdrawal Request</h3>

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Available Balance</h5>
                                            <h3 class="text-success">₹<?php echo number_format($total_earned); ?></h3>
                                            <small class="text-muted">Total earnings available for withdrawal</small>
                                        </div>
                                        <div class="col-md-6">
                                            <form method="post" action="process_withdrawal.php">
                                                <div class="mb-3">
                                                    <label class="form-label">Withdrawal Amount</label>
                                                    <input type="number" class="form-control" name="amount"
                                                           max="<?php echo $total_earned; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Method</label>
                                                    <select class="form-select" name="payment_method" required>
                                                        <option value="bank_transfer">Bank Transfer</option>
                                                        <option value="cheque">Cheque</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="online">Online Transfer</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane me-2"></i>Request Withdrawal
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Analytics Section -->
                        <div id="analytics" class="mb-5">
                            <h3 class="mb-4">Commission Analytics</h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Monthly Performance</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="monthlyChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Commission Types</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="commissionTypeChart"></canvas>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Chart data
        const monthlyData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Direct Commission',
                data: [12000, 15000, 18000, 22000, 25000, <?php echo array_sum(array_column(array_filter($monthly_commissions, function($c) { return $c['commission_type'] == 'direct'; }), 'total_amount')); ?>],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Team Commission',
                data: [8000, 10000, 12000, 14000, 16000, <?php echo array_sum(array_column(array_filter($monthly_commissions, function($c) { return $c['commission_type'] == 'team'; }), 'total_amount')); ?>],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        };

        const commissionTypeData = {
            labels: ['Direct', 'Team', 'Level Bonus', 'Matching', 'Leadership', 'Performance'],
            datasets: [{
                data: [
                    <?php echo array_sum(array_column(array_filter($yearly_commissions, function($c) { return $c['commission_type'] == 'direct'; }), 'total_amount')); ?>,
                    <?php echo array_sum(array_column(array_filter($yearly_commissions, function($c) { return $c['commission_type'] == 'team'; }), 'total_amount')); ?>,
                    <?php echo array_sum(array_column(array_filter($yearly_commissions, function($c) { return $c['commission_type'] == 'level_bonus'; }), 'total_amount')); ?>,
                    <?php echo array_sum(array_column(array_filter($yearly_commissions, function($c) { return $c['commission_type'] == 'matching_bonus'; }), 'total_amount')); ?>,
                    <?php echo array_sum(array_column(array_filter($yearly_commissions, function($c) { return $c['commission_type'] == 'leadership_bonus'; }), 'total_amount')); ?>,
                    <?php echo array_sum(array_column(array_filter($yearly_commissions, function($c) { return $c['commission_type'] == 'performance_bonus'; }), 'total_amount')); ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ],
                borderWidth: 2
            }]
        };

        // Initialize charts when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            new Chart(monthlyCtx, {
                type: 'line',
                data: monthlyData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₹' + context.parsed.y.toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });

            const commissionTypeCtx = document.getElementById('commissionTypeChart').getContext('2d');
            new Chart(commissionTypeCtx, {
                type: 'doughnut',
                data: commissionTypeData,
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
                                    return context.label + ': ₹' + context.parsed.toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        });

        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
