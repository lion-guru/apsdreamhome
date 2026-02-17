<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/performance_manager.php';
require_once __DIR__ . '/includes/admin_navigation.php';

// Get database connection and performance manager
$perfManager = getPerformanceManager();
$perfManager->startProfiling();

// Get MLM Statistics
try {
    // Total Associates
    $totalAssociatesData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM associates", 3600);
    $totalAssociates = $totalAssociatesData[0]['cnt'] ?? 0;
    
    // Active Associates (with business)
    $activeAssociatesData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM associates WHERE total_business > 0", 3600);
    $activeAssociates = $activeAssociatesData[0]['cnt'] ?? 0;
    
    // Total Commissions
    $commissionStatsData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt, SUM(commission_amount) as total FROM mlm_commissions", 1800);
    $commissionStats = $commissionStatsData[0] ?? ['cnt' => 0, 'total' => 0];
    
    // Pending Commissions
    $pendingCommissionsData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt, SUM(commission_amount) as total FROM mlm_commissions WHERE status = 'pending'", 1800);
    $pendingCommissions = $pendingCommissionsData[0] ?? ['cnt' => 0, 'total' => 0];
    
    // Paid Commissions
    $paidCommissionsData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt, SUM(commission_amount) as total FROM mlm_commissions WHERE status = 'paid'", 1800);
    $paidCommissions = $paidCommissionsData[0] ?? ['cnt' => 0, 'total' => 0];
    
    // Total Business Volume
    $businessVolumeData = $perfManager->executeCachedQuery("SELECT SUM(direct_business) as direct, SUM(team_business) as team, SUM(total_business) as total FROM associates", 3600);
    $businessVolume = $businessVolumeData[0] ?? ['direct' => 0, 'team' => 0, 'total' => 0];
    
    // Salary Contracts
    $activeSalaryContractsData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM salary_contracts WHERE status = 'active'", 3600);
    $activeSalaryContracts = $activeSalaryContractsData[0]['cnt'] ?? 0;
    
    // Monthly Salary Payout
    $monthlySalaryPayoutData = $perfManager->executeCachedQuery("SELECT SUM(sp.monthly_salary) as total FROM salary_contracts sc JOIN salary_plans sp ON sc.plan_id = sp.id WHERE sc.status = 'active'", 3600);
    $monthlySalaryPayout = $monthlySalaryPayoutData[0]['total'] ?? 0;
    
} catch (Exception $e) {
    $totalAssociates = $activeAssociates = 0;
    $commissionStats = $pendingCommissions = $paidCommissions = ['cnt' => 0, 'total' => 0];
    $businessVolume = ['direct' => 0, 'team' => 0, 'total' => 0];
    $activeSalaryContracts = 0;
    $monthlySalaryPayout = 0;
}

// Recent Commissions (shorter cache for live updates)
try {
    $recentCommissions = $perfManager->executeCachedQuery("
        SELECT mc.*, u.uname as user_name, a.company_name 
        FROM mlm_commissions mc 
        LEFT JOIN user u ON mc.associate_id = u.uid 
        LEFT JOIN associates a ON mc.associate_id = a.id 
        ORDER BY mc.created_at DESC 
        LIMIT 10
    ", 60); // Cache for 1 minute
} catch (Exception $e) {
    $recentCommissions = [];
}

// Top Performers
try {
    $topPerformers = $perfManager->executeCachedQuery("
        SELECT a.*, u.uname as user_name 
        FROM associates a 
        LEFT JOIN user u ON a.user_id = u.uid 
        WHERE a.total_business > 0 
        ORDER BY a.total_business DESC 
        LIMIT 5
    ", 3600); // Cache for 1 hour
} catch (Exception $e) {
    $topPerformers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLM Admin Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-gradient-success { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .bg-gradient-danger { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .bg-gradient-secondary { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
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
                            <a class="nav-link active" href="mlm_dashboard.php">
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
                    <h1 class="h2">MLM Admin Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <div class="stat-icon bg-gradient-primary text-white">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3 class="card-title"><?php echo number_format($totalAssociates); ?></h3>
                                <p class="card-text">Total Associates</p>
                                <small class="text-muted"><?php echo number_format($activeAssociates); ?> Active</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <div class="stat-icon bg-gradient-success text-white">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <h3 class="card-title">₹<?php echo number_format($commissionStats['total'], 0); ?></h3>
                                <p class="card-text">Total Commissions</p>
                                <small class="text-muted"><?php echo $commissionStats['cnt']; ?> Transactions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <div class="stat-icon bg-gradient-warning text-white">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3 class="card-title">₹<?php echo number_format($businessVolume['total'], 0); ?></h3>
                                <p class="card-text">Business Volume</p>
                                <small class="text-muted">Direct: ₹<?php echo number_format($businessVolume['direct'], 0); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <div class="stat-icon bg-gradient-info text-white">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <h3 class="card-title"><?php echo $activeSalaryContracts; ?></h3>
                                <p class="card-text">Salary Contracts</p>
                                <small class="text-muted">₹<?php echo number_format($monthlySalaryPayout, 0); ?>/month</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commission Status -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Pending Commissions</h5>
                                <h3 class="text-warning">₹<?php echo number_format($pendingCommissions['total'], 0); ?></h3>
                                <p class="card-text"><?php echo $pendingCommissions['cnt']; ?> Pending</p>
                                <a href="mlm_commissions.php?status=pending" class="btn btn-warning btn-sm">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Paid Commissions</h5>
                                <h3 class="text-success">₹<?php echo number_format($paidCommissions['total'], 0); ?></h3>
                                <p class="card-text"><?php echo $paidCommissions['cnt']; ?> Paid</p>
                                <a href="mlm_commissions.php?status=paid" class="btn btn-success btn-sm">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Payout</h5>
                                <h3 class="text-info">₹<?php echo number_format($monthlySalaryPayout, 0); ?></h3>
                                <p class="card-text">Salary Payout</p>
                                <a href="mlm_salary.php" class="btn btn-info btn-sm">Manage</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Commissions & Top Performers -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Commissions</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Associate</th>
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recentCommissions)): ?>
                                                <?php foreach ($recentCommissions as $commission): ?>
                                                    <tr>
                                                        <td><?php echo h($commission['company_name'] ?? 'N/A'); ?></td>
                                                        <td>₹<?php echo number_format($commission['commission_amount'], 0); ?></td>
                                                        <td><?php echo h($commission['commission_type']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $commission['status'] == 'paid' ? 'success' : 'warning'; ?>">
                                                                <?php echo ucfirst(h($commission['status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M j, Y', strtotime($commission['created_at'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No commissions found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top Performers</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($topPerformers)): ?>
                                    <?php foreach ($topPerformers as $performer): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <h6 class="mb-0"><?php echo h($performer['company_name']); ?></h6>
                                                <small class="text-muted"><?php echo h($performer['user_name']); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-0">₹<?php echo number_format($performer['total_business'], 0); ?></h6>
                                                <small class="text-muted">Total Business</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">No performers found</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php $perfManager->endProfiling(); ?>
</body>
</html>
