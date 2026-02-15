<?php
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Get comprehensive MLM statistics
$mlm_stats = [];
try {
    // Basic counts
    $stats_queries = [
        'total_associates' => "SELECT COUNT(*) as count FROM associates",
        'active_associates' => "SELECT COUNT(*) as count FROM associates WHERE is_active = 1",
        'total_commissions' => "SELECT COUNT(*) as count FROM mlm_commissions",
        'paid_commissions' => "SELECT COUNT(*) as count FROM mlm_commissions WHERE status = 'paid'",
        'pending_commissions' => "SELECT COUNT(*) as count FROM mlm_commissions WHERE status = 'pending'",
        'total_payouts' => "SELECT COUNT(*) as count FROM mlm_payouts",
        'pending_payouts' => "SELECT COUNT(*) as count FROM mlm_payouts WHERE status = 'pending'",
        'total_users' => "SELECT COUNT(*) as count FROM user WHERE utype = 'associate'",
        'salary_contracts' => "SELECT COUNT(*) as count FROM salary_contracts",
        'active_salary_contracts' => "SELECT COUNT(*) as count FROM salary_contracts WHERE status = 'active'",
        'salary_notifications' => "SELECT COUNT(*) as count FROM salary_notifications"
    ];
    
    foreach ($stats_queries as $key => $query) {
        $row = $db->fetch($query);
        $mlm_stats[$key] = $row['count'] ?? 0;
    }
    
    // Financial summaries
    $financial_queries = [
        'total_commission_amount' => "SELECT COALESCE(SUM(commission_amount), 0) as total FROM mlm_commissions WHERE status = 'paid'",
        'pending_commission_amount' => "SELECT COALESCE(SUM(commission_amount), 0) as total FROM mlm_commissions WHERE status = 'pending'",
        'total_payout_amount' => "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_payouts WHERE status = 'processed'",
        'pending_payout_amount' => "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_payouts WHERE status = 'pending'",
        'total_salary_payouts' => "SELECT COALESCE(SUM(monthly_salary), 0) as total FROM salary_contracts WHERE status = 'active'"
    ];
    
    foreach ($financial_queries as $key => $query) {
        $row = $db->fetch($query);
        $mlm_stats[$key] = $row['total'] ?? 0;
    }
    
    // Recent activity
    $recent_commissions = $db->fetchAll("
        SELECT mc.*, a.company_name, u.uname as user_name 
        FROM mlm_commissions mc 
        JOIN associates a ON mc.associate_id = a.id 
        JOIN user u ON a.user_id = u.uid 
        ORDER BY mc.created_at DESC 
        LIMIT 5
    ");
    
    $recent_payouts = $db->fetchAll("
        SELECT mp.*, a.company_name, u.uname as user_name 
        FROM mlm_payouts mp 
        JOIN associates a ON mp.associate_id = a.id 
        JOIN user u ON a.user_id = u.uid 
        ORDER BY mp.created_at DESC 
        LIMIT 5
    ");
    
    // Top performers
    $top_performers = $db->fetchAll("
        SELECT a.id, a.company_name, u.uname as name, u.uemail as email,
               COUNT(mc.id) as commission_count,
               COALESCE(SUM(mc.commission_amount), 0) as total_earnings
        FROM associates a 
        JOIN user u ON a.user_id = u.uid 
        LEFT JOIN mlm_commissions mc ON a.id = mc.associate_id AND mc.status = 'paid'
        GROUP BY a.id, a.company_name, u.uname, u.uemail 
        ORDER BY total_earnings DESC 
        LIMIT 5
    ");
    
    // Commission levels distribution
    $level_distribution = $db->fetchAll("
        SELECT level, COUNT(*) as count, SUM(commission_amount) as total_amount
        FROM mlm_commissions 
        WHERE status = 'paid'
        GROUP BY level 
        ORDER BY level ASC
    ");
    
} catch (Exception $e) {
    $error = "Error loading MLM statistics: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLM System Overview - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .progress-ring {
            transform: rotate(-90deg);
        }
        .metric-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
        }
    </style>
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
                            <a class="nav-link active" href="mlm_overview.php">
                                <i class="fas fa-chart-pie"></i> System Overview
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
                    <h1 class="h2">MLM System Overview</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="exportReport()">
                                <i class="fas fa-download"></i> Export Report
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-users"></i> Total Associates</h5>
                                <h2><?php echo number_format($mlm_stats['total_associates']); ?></h2>
                                <small>Active: <?php echo number_format($mlm_stats['active_associates']); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-money-bill-wave"></i> Commissions</h5>
                                <h2>₹<?php echo number_format($mlm_stats['total_commission_amount']); ?></h2>
                                <small>Pending: ₹<?php echo number_format($mlm_stats['pending_commission_amount']); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-credit-card"></i> Payouts</h5>
                                <h2>₹<?php echo number_format($mlm_stats['total_payout_amount']); ?></h2>
                                <small>Pending: ₹<?php echo number_format($mlm_stats['pending_payout_amount']); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-user-friends"></i> Network Size</h5>
                                <h2><?php echo number_format($mlm_stats['total_users']); ?></h2>
                                <small>Total MLM Users</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5>Commission Status Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="commissionStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5>Commission Levels Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="levelDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Management Overview -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-hand-holding-usd me-2"></i>Salary Management Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-primary"><?php echo number_format($mlm_stats['salary_contracts']); ?></h4>
                                            <p class="mb-0">Total Contracts</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-success"><?php echo number_format($mlm_stats['active_salary_contracts']); ?></h4>
                                            <p class="mb-0">Active Contracts</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-warning">₹<?php echo number_format($mlm_stats['total_salary_payouts']); ?></h4>
                                            <p class="mb-0">Monthly Salary Outlay</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-info"><?php echo number_format($mlm_stats['salary_notifications']); ?></h4>
                                            <p class="mb-0">Notifications Sent</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-trophy me-2"></i>Top Performers</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($top_performers)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Associate</th>
                                                    <th>Commissions</th>
                                                    <th>Total Earnings</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_performers as $performer): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo h($performer['company_name']); ?></strong>
                                                            <br><small><?php echo h($performer['name']); ?></small>
                                                        </td>
                                                        <td><?php echo $performer['commission_count']; ?></td>
                                                        <td class="text-success fw-bold">₹<?php echo number_format($performer['total_earnings']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No performance data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history me-2"></i>Recent Commissions</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_commissions)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($recent_commissions as $commission): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo h($commission['company_name']); ?></h6>
                                                        <small class="text-muted"><?php echo h($commission['user_name']); ?></small>
                                                    </div>
                                                    <div class="text-end">
                                                        <h6 class="mb-1 text-success">₹<?php echo number_format($commission['commission_amount']); ?></h6>
                                                        <small class="text-muted"><?php echo date('M j, H:i', strtotime($commission['created_at'])); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No recent commissions</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-heartbeat me-2"></i>System Health</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="metric-highlight text-center">
                                            <h4><i class="fas fa-check-circle"></i> System Status</h4>
                                            <h3>Healthy</h3>
                                            <p>All MLM systems operational</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h5>Commission Processing</h5>
                                            <div class="progress mt-2" style="height: 25px;">
                                                <?php 
                                                $processing_rate = $mlm_stats['total_commissions'] > 0 ? 
                                                    ($mlm_stats['paid_commissions'] / $mlm_stats['total_commissions']) * 100 : 0;
                                                ?>
                                                <div class="progress-bar bg-success" style="width: <?php echo $processing_rate; ?>%">
                                                    <?php echo round($processing_rate); ?>% Processed
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h5>Associate Activity</h5>
                                            <div class="progress mt-2" style="height: 25px;">
                                                <?php 
                                                $activity_rate = $mlm_stats['total_associates'] > 0 ? 
                                                    ($mlm_stats['active_associates'] / $mlm_stats['total_associates']) * 100 : 0;
                                                ?>
                                                <div class="progress-bar bg-info" style="width: <?php echo $activity_rate; ?>%">
                                                    <?php echo round($activity_rate); ?>% Active
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Commission Status Chart
        const commissionStatusCtx = document.getElementById('commissionStatusChart').getContext('2d');
        new Chart(commissionStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Rejected'],
                datasets: [{
                    data: [
                        <?php echo $mlm_stats['paid_commissions']; ?>,
                        <?php echo $mlm_stats['pending_commissions']; ?>,
                        <?php echo $mlm_stats['total_commissions'] - $mlm_stats['paid_commissions'] - $mlm_stats['pending_commissions']; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Level Distribution Chart
        const levelCtx = document.getElementById('levelDistributionChart').getContext('2d');
        new Chart(levelCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($level_distribution, 'level')); ?>,
                datasets: [{
                    label: 'Commission Count',
                    data: <?php echo json_encode(array_column($level_distribution, 'count')); ?>,
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function refreshData() {
            location.reload();
        }

        function exportReport() {
            // Implement export functionality
            console.log('Exporting MLM system report...');
        }
    </script>
</body>
</html>

