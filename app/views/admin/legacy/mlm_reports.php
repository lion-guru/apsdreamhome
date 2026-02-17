<?php
require_once __DIR__ . '/core/init.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setSessionget_flash('error', "Invalid security token. Please refresh the page and try again.");
        header("Location: mlm_reports.php");
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'generate_report') {
        $report_type = $_POST['report_type'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        
        switch ($report_type) {
            case 'commission_report':
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="commission_report_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Date', 'Associate', 'User', 'Commission Type', 'Amount', 'Level', 'Status', 'Sale Amount']);
                
                $rows = $db->fetchAll("
                    SELECT mc.*, a.company_name, u.uname as user_name, u.uemail as email
                    FROM mlm_commissions mc
                    LEFT JOIN associates a ON mc.associate_id = a.id
                    LEFT JOIN user u ON a.user_id = u.uid
                    WHERE DATE(mc.created_at) BETWEEN ? AND ?
                    ORDER BY mc.created_at DESC
                ", [$date_from, $date_to]);
                
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row['created_at'],
                        $row['company_name'],
                        $row['user_name'],
                        $row['commission_type'],
                        $row['commission_amount'],
                        $row['level'],
                        $row['status'],
                        $row['sale_amount']
                    ]);
                }
                fclose($output);
                exit();
                break;
                
            case 'payout_report':
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="payout_report_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Date', 'Associate', 'User', 'Amount', 'Type', 'Status', 'Payment Method', 'Transaction ID']);
                
                $rows = $db->fetchAll("
                    SELECT sp.*, a.company_name, u.uname as user_name, u.uemail as email
                    FROM salary_payouts sp
                    LEFT JOIN associates a ON sp.associate_id = a.id
                    LEFT JOIN user u ON a.user_id = u.uid
                    WHERE DATE(sp.payout_date) BETWEEN ? AND ?
                    ORDER BY sp.payout_date DESC
                ", [$date_from, $date_to]);
                
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row['payout_date'],
                        $row['company_name'],
                        $row['user_name'],
                        $row['amount'],
                        $row['payout_type'],
                        $row['status'],
                        $row['payment_method'],
                        $row['transaction_id']
                    ]);
                }
                fclose($output);
                exit();
                break;
                
            case 'network_report':
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="network_report_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Associate', 'User', 'Email', 'Total Business', 'Direct Business', 'Team Business', 'Team Size', 'Level', 'Status', 'Join Date']);
                
                $rows = $db->fetchAll("
                    SELECT a.*, u.uname as user_name, u.uemail as email
                    FROM associates a
                    LEFT JOIN user u ON a.user_id = u.uid
                    WHERE DATE(a.created_at) BETWEEN ? AND ?
                    ORDER BY a.total_business DESC
                ", [$date_from, $date_to]);
                
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row['company_name'],
                        $row['user_name'],
                        $row['email'],
                        $row['total_business'],
                        $row['direct_business'],
                        $row['team_business'],
                        $row['team_size'] ?? 0,
                        $row['current_level'],
                        $row['status'],
                        $row['created_at']
                    ]);
                }
                fclose($output);
                exit();
                break;
        }
    }
}

// Get dashboard statistics
try {
    // Commission statistics
    $commissionStats = $db->fetch("
        SELECT 
            COUNT(*) as total_commissions,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_commissions,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_commissions,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN commission_amount ELSE 0 END), 0) as total_paid,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN commission_amount ELSE 0 END), 0) as total_pending,
            COALESCE(AVG(commission_amount), 0) as avg_commission
        FROM mlm_commissions
    ");
    
    // Payout statistics
    $payoutStats = $db->fetch("
        SELECT 
            COUNT(*) as total_payouts,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_payouts,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payouts,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as total_paid_amount,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as total_pending_amount
        FROM salary_payouts
    ");
    
    // Network statistics
    $networkStats = $db->fetch("
        SELECT 
            COUNT(*) as total_associates,
            COUNT(CASE WHEN total_business > 0 THEN 1 END) as active_associates,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as status_active,
            COALESCE(SUM(total_business), 0) as total_business_volume,
            COALESCE(AVG(total_business), 0) as avg_business_per_associate,
            COUNT(CASE WHEN current_level >= 5 THEN 1 END) as senior_level_associates
        FROM associates
    ");
    
    // Top performers
    $topPerformers = $db->fetchAll("
        SELECT a.*, u.uname as user_name, u.uemail as email
        FROM associates a
        LEFT JOIN user u ON a.user_id = u.uid
        WHERE a.total_business > 0
        ORDER BY a.total_business DESC
        LIMIT 10
    ");
    
    // Recent commissions
    $recentCommissions = $db->fetchAll("
        SELECT mc.*, a.company_name, u.uname as user_name
        FROM mlm_commissions mc
        LEFT JOIN associates a ON mc.associate_id = a.id
        LEFT JOIN user u ON a.user_id = u.uid
        ORDER BY mc.created_at DESC
        LIMIT 10
    ");
    
} catch (Exception $e) {
    $commissionStats = $payoutStats = $networkStats = [];
    $topPerformers = $recentCommissions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLM Reports - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <a class="nav-link active" href="mlm_reports.php">
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
                    <h1 class="h2">MLM Reports & Analytics</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Overview Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Associates</h5>
                                <h3><?php echo number_format($networkStats['total_associates'] ?? 0); ?></h3>
                                <small><?php echo number_format($networkStats['active_associates'] ?? 0); ?> active</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Business Volume</h5>
                                <h3>₹<?php echo number_format($networkStats['total_business_volume'] ?? 0, 0); ?></h3>
                                <small>Total network volume</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Commissions</h5>
                                <h3>₹<?php echo number_format($commissionStats['total_paid'] ?? 0, 0); ?></h3>
                                <small><?php echo number_format($commissionStats['paid_commissions'] ?? 0); ?> paid</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Payouts</h5>
                                <h3>₹<?php echo number_format($payoutStats['total_paid_amount'] ?? 0, 0); ?></h3>
                                <small><?php echo number_format($payoutStats['paid_payouts'] ?? 0); ?> processed</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Commission Trends</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="commissionChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Network Growth</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="networkChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Generation -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Generate Reports</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <?php echo getCsrfField(); ?>
                            <input type="hidden" name="action" value="generate_report">
                            <div class="col-md-3">
                                <label for="report_type" class="form-label">Report Type</label>
                                <select class="form-select" id="report_type" name="report_type" required>
                                    <option value="">Select report type</option>
                                    <option value="commission_report">Commission Report</option>
                                    <option value="payout_report">Payout Report</option>
                                    <option value="network_report">Network Report</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" required>
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-download"></i> Generate CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top 10 Performers</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Associate</th>
                                        <th>User</th>
                                        <th>Total Business</th>
                                        <th>Direct Business</th>
                                        <th>Team Size</th>
                                        <th>Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($topPerformers)): ?>
                                        <?php foreach ($topPerformers as $index => $performer): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($index) {
                                                            0 => 'warning', // Gold
                                                            1 => 'secondary', // Silver
                                                            2 => 'danger', // Bronze
                                                            default => 'primary'
                                                        }; 
                                                    ?>">
                                                        #<?php echo $index + 1; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo h($performer['company_name']); ?></strong>
                                                </td>
                                                <td><?php echo h($performer['user_name']); ?></td>
                                                <td>
                                                    <strong class="text-success">₹<?php echo number_format($performer['total_business'], 0); ?></strong>
                                                </td>
                                                <td>₹<?php echo number_format($performer['direct_business'], 0); ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $performer['team_size'] ?? 0; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">Level <?php echo $performer['current_level']; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <p class="text-muted">No performers data available</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Commissions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Commissions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Associate</th>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentCommissions)): ?>
                                        <?php foreach ($recentCommissions as $commission): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y H:i', strtotime($commission['created_at'])); ?></td>
                                                <td>
                                                    <strong><?php echo h($commission['company_name']); ?></strong>
                                                </td>
                                                <td><?php echo h($commission['user_name']); ?></td>
                                                <td>
                                                    <strong class="text-success">₹<?php echo number_format($commission['commission_amount'], 0); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo ucwords(str_replace('_', ' ', $commission['commission_type'])); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">Level <?php echo $commission['level']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($commission['status']) {
                                                            'paid' => 'success',
                                                            'pending' => 'warning',
                                                            'rejected' => 'danger',
                                                            default => 'secondary'
                                                        }; 
                                                    ?>">
                                                        <?php echo ucfirst($commission['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <p class="text-muted">No recent commissions</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Commission Trends Chart
        const commissionCtx = document.getElementById('commissionChart').getContext('2d');
        new Chart(commissionCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Commission Amount',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Network Growth Chart
        const networkCtx = document.getElementById('networkChart').getContext('2d');
        new Chart(networkCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Associates',
                    data: [12, 19, 15, 25, 22, 30],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
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

        // Set default dates for report generation
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            
            document.getElementById('date_from').value = firstDay.toISOString().split('T')[0];
            document.getElementById('date_to').value = today.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
