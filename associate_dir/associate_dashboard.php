<?php
/**
 * Ultimate Associate Dashboard - APS Dream Homes
 * Complete MLM Partner Portal with All Essential Features
 */

session_start();
require_once '../includes/config.php';
require_once '../includes/associate_permissions.php';

// Initialize database connection
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Check if associate is logged in
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
$associate_name = $_SESSION['associate_name'];
$associate_level = $_SESSION['associate_level'];
$associate_email = $_SESSION['associate_email'] ?? '';

// Check if associate has dashboard access
if (!canAccessModule($associate_id, 'dashboard')) {
    $_SESSION['error_message'] = "You don't have permission to access the dashboard.";
    header("Location: associate_login.php");
    exit();
}

// Get accessible modules for navigation
$accessible_modules = getAccessibleModules($associate_id);

// Get comprehensive associate data
try {
    $stmt = $conn->prepare("SELECT * FROM mlm_agents WHERE id = ?");
    $stmt->bind_param("i", $associate_id);
    $stmt->execute();
    $associate_data = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    error_log("Error fetching associate data: " . $e->getMessage());
    $associate_data = [];
}

// Get dashboard statistics
$stats = [];

try {
    // Total Business Volume
    $business_query = "SELECT COALESCE(SUM(amount), 0) as total_business FROM bookings WHERE associate_id = ? AND status IN ('confirmed', 'completed')";
    $business_stmt = $conn->prepare($business_query);
    $business_stmt->bind_param("i", $associate_id);
    $business_stmt->execute();
    $stats['total_business'] = $business_stmt->get_result()->fetch_assoc()['total_business'];

    // Total Commissions Earned
    $commission_query = "SELECT COALESCE(SUM(commission_amount), 0) as total_commission FROM mlm_commissions WHERE associate_id = ? AND status = 'paid'";
    $commission_stmt = $conn->prepare($commission_query);
    $commission_stmt->bind_param("i", $associate_id);
    $commission_stmt->execute();
    $stats['total_commission'] = $commission_stmt->get_result()->fetch_assoc()['total_commission'];

    // Direct Team Members
    $direct_team_query = "SELECT COUNT(*) as direct_team FROM mlm_agents WHERE sponsor_id = ? AND status = 'active'";
    $team_stmt = $conn->prepare($direct_team_query);
    $team_stmt->bind_param("i", $associate_id);
    $team_stmt->execute();
    $stats['direct_team'] = $team_stmt->get_result()->fetch_assoc()['direct_team'];

    // Total Team Size (including indirect)
    $total_team_query = "WITH RECURSIVE team_tree AS (
        SELECT id, sponsor_id, 1 as level FROM mlm_agents WHERE sponsor_id = ? AND status = 'active'
        UNION ALL
        SELECT m.id, m.sponsor_id, t.level + 1 FROM mlm_agents m
        JOIN team_tree t ON m.sponsor_id = t.id WHERE m.status = 'active'
    )
    SELECT COUNT(*) as total_team FROM team_tree";
    $total_team_stmt = $conn->prepare($total_team_query);
    $total_team_stmt->bind_param("i", $associate_id);
    $total_team_stmt->execute();
    $stats['total_team'] = $total_team_stmt->get_result()->fetch_assoc()['total_team'];

    // Monthly Business
    $monthly_query = "SELECT COALESCE(SUM(amount), 0) as monthly_business FROM bookings
                      WHERE associate_id = ? AND status IN ('confirmed', 'completed')
                      AND booking_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
    $monthly_stmt = $conn->prepare($monthly_query);
    $monthly_stmt->bind_param("i", $associate_id);
    $monthly_stmt->execute();
    $stats['monthly_business'] = $monthly_stmt->get_result()->fetch_assoc()['monthly_business'];

    // Pending Commissions
    $pending_query = "SELECT COALESCE(SUM(commission_amount), 0) as pending_commission FROM mlm_commissions WHERE associate_id = ? AND status = 'pending'";
    $pending_stmt = $conn->prepare($pending_query);
    $pending_stmt->bind_param("i", $associate_id);
    $pending_stmt->execute();
    $stats['pending_commission'] = $pending_stmt->get_result()->fetch_assoc()['pending_commission'];

} catch (Exception $e) {
    error_log("Error fetching dashboard stats: " . $e->getMessage());
    $stats = array_fill_keys(['total_business', 'total_commission', 'direct_team', 'total_team', 'monthly_business', 'pending_commission'], 0);
}

// Level targets and progress
$level_targets = [
    'Associate' => ['min' => 0, 'max' => 1000000, 'commission' => 5, 'reward' => 'Mobile'],
    'Sr. Associate' => ['min' => 1000000, 'max' => 3500000, 'commission' => 7, 'reward' => 'Tablet'],
    'BDM' => ['min' => 3500000, 'max' => 7000000, 'commission' => 10, 'reward' => 'Laptop'],
    'Sr. BDM' => ['min' => 7000000, 'max' => 15000000, 'commission' => 12, 'reward' => 'Tour'],
    'Vice President' => ['min' => 15000000, 'max' => 30000000, 'commission' => 15, 'reward' => 'Bike'],
    'President' => ['min' => 30000000, 'max' => 50000000, 'commission' => 18, 'reward' => 'Bullet'],
    'Site Manager' => ['min' => 50000000, 'max' => 999999999, 'commission' => 20, 'reward' => 'Car']
];

$current_level_info = $level_targets[$associate_level] ?? $level_targets['Associate'];
$progress_percentage = 0;
if ($current_level_info['max'] > $current_level_info['min']) {
    $progress_percentage = min(100, (($stats['total_business'] - $current_level_info['min']) / ($current_level_info['max'] - $current_level_info['min'])) * 100);
}

// Get recent customers
$recent_customers = [];
try {
    $customer_query = "SELECT c.name as customer_name, c.email, c.phone, b.total_amount, b.amount as paid_amount,
                              (b.total_amount - b.amount) as remaining_amount, b.booking_date, b.status
                       FROM bookings b
                       JOIN customers c ON b.customer_id = c.id
                       WHERE b.associate_id = ?
                       ORDER BY b.booking_date DESC LIMIT 5";
    $customer_stmt = $conn->prepare($customer_query);
    $customer_stmt->bind_param("i", $associate_id);
    $customer_stmt->execute();
    $recent_customers = $customer_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching recent customers: " . $e->getMessage());
}

// Get recent team members
$recent_team = [];
try {
    $team_query = "SELECT id, full_name, mobile, current_level, total_business, registration_date, status
                   FROM mlm_agents WHERE sponsor_id = ? ORDER BY registration_date DESC LIMIT 5";
    $team_stmt = $conn->prepare($team_query);
    $team_stmt->bind_param("i", $associate_id);
    $team_stmt->execute();
    $recent_team = $team_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching recent team: " . $e->getMessage());
}

// Get recent transactions
$recent_transactions = [];
try {
    $transaction_query = "SELECT transaction_type, amount, description, created_at, status
                          FROM mlm_transactions WHERE associate_id = ?
                          ORDER BY created_at DESC LIMIT 5";
    $transaction_stmt = $conn->prepare($transaction_query);
    $transaction_stmt->bind_param("i", $associate_id);
    $transaction_stmt->execute();
    $recent_transactions = $transaction_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching recent transactions: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associate Dashboard - APS Dream Homes</title>

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

        .top-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .level-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
        }

        .quick-action-btn {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
            border: none;
            width: 100%;
            font-weight: 500;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .team-member-card, .customer-card, .transaction-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            margin-bottom: 1rem;
        }

        .team-member-card:hover, .customer-card:hover, .transaction-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d1ecf1; color: #0c5460; }

        .referral-code {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            height: 100vh;
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.show {
            left: 0;
        }

        .sidebar-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem;
            text-align: center;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
        }

        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        .main-content.sidebar-open {
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content.sidebar-open {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <button class="btn btn-outline-light me-2" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-home me-2"></i>APS Dream Homes
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($associate_name); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="associate_crm.php">
                            <i class="fas fa-users me-2"></i>Associate CRM
                        </a></li>
                        <li><a class="dropdown-item" href="associate_portal.php">
                            <i class="fas fa-building me-2"></i>Associate Portal
                        </a></li>
                        <li><a class="dropdown-item" href="associate_notifications.php">
                            <i class="fas fa-bell me-2"></i>Notifications
                        </a></li>
                        <li><a class="dropdown-item" href="associate_self_service.php">
                            <i class="fas fa-cog me-2"></i>Self Service
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h6 class="mb-0">Navigation Menu</h6>
        </div>
        <div class="sidebar-menu">
            <?php if (isset($accessible_modules['dashboard'])): ?>
            <a href="#dashboard" class="active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
            <?php endif; ?>

            <?php if (isset($accessible_modules['customers'])): ?>
            <a href="#customers"><i class="fas fa-user-friends me-2"></i>Customers</a>
            <?php endif; ?>

            <?php if (isset($accessible_modules['crm'])): ?>
            <a href="#crm"><i class="fas fa-crm me-2"></i>CRM System</a>
            <?php endif; ?>

            <?php if (isset($accessible_modules['team_management'])): ?>
            <a href="#team"><i class="fas fa-users me-2"></i>Team Management</a>
            <?php endif; ?>

            <?php if (isset($accessible_modules['commission_management'])): ?>
            <a href="commission_dashboard.php"><i class="fas fa-rupee-sign me-2"></i>Commission Dashboard</a>
            <?php endif; ?>

            <?php if (isset($accessible_modules['reports'])): ?>
            <a href="#reports"><i class="fas fa-chart-bar me-2"></i>Reports</a>
            <?php endif; ?>

            <a href="#profile"><i class="fas fa-user me-2"></i>Profile</a>
            <a href="#support"><i class="fas fa-headset me-2"></i>Support</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid mt-5">
            <div class="dashboard-container">
                <!-- Header Section -->
                <div class="top-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2">Welcome back, <?php echo htmlspecialchars($associate_name); ?>!</h1>
                            <p class="mb-0">
                                <span class="level-badge"><?php echo $associate_level; ?></span>
                                <span class="ms-3">Referral Code: <strong><?php echo $associate_data['referral_code'] ?? 'N/A'; ?></strong></span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-light btn-lg" onclick="shareReferralLink()">
                                <i class="fas fa-share-alt me-2"></i>Share Referral
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Key Performance Stats -->
                    <div class="row mb-4" id="dashboard">
                        <div class="col-xl-3 col-lg-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-chart-line stats-icon"></i>
                                <h2>₹<?php echo number_format($stats['total_business']); ?></h2>
                                <h5>Total Business Volume</h5>
                                <small>This Month: ₹<?php echo number_format($stats['monthly_business']); ?></small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-rupee-sign stats-icon"></i>
                                <h2>₹<?php echo number_format($stats['total_commission']); ?></h2>
                                <h5>Total Commissions</h5>
                                <small>Pending: ₹<?php echo number_format($stats['pending_commission']); ?></small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-users stats-icon"></i>
                                <h2><?php echo $stats['direct_team']; ?></h2>
                                <h5>Direct Team</h5>
                                <small>Total Team: <?php echo $stats['total_team']; ?></small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-percentage stats-icon"></i>
                                <h2><?php echo $current_level_info['commission']; ?>%</h2>
                                <h5>Commission Rate</h5>
                                <small>Next Level: <?php echo array_keys($level_targets)[array_search($associate_level, array_keys($level_targets)) + 1] ?? 'Max Level'; ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Level Progress -->
                    <div class="row mb-4">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Level Progress</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6>Current Level: <span class="text-primary"><?php echo $associate_level; ?></span></h6>
                                        <small class="text-muted">Progress to next level</small>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                             style="width: <?php echo $progress_percentage; ?>%"
                                             aria-valuenow="<?php echo $progress_percentage; ?>"
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?php echo round($progress_percentage, 1); ?>%
                                        </div>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <strong>Current</strong><br>
                                            ₹<?php echo number_format($stats['total_business']); ?>
                                        </div>
                                        <div class="col-4">
                                            <strong>Target</strong><br>
                                            ₹<?php echo number_format($current_level_info['max']); ?>
                                        </div>
                                        <div class="col-4">
                                            <strong>Reward</strong><br>
                                            <?php echo $current_level_info['reward']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-primary quick-action-btn" onclick="shareReferralLink()">
                                        <i class="fas fa-share-alt me-2"></i>Share Referral Link
                                    </button>
                                    <button class="btn btn-success quick-action-btn" onclick="viewProjects()">
                                        <i class="fas fa-building me-2"></i>View Properties
                                    </button>
                                    <button class="btn btn-info quick-action-btn" onclick="downloadBrochure()">
                                        <i class="fas fa-download me-2"></i>Download Materials
                                    </button>
                                    <button class="btn btn-warning quick-action-btn" onclick="viewReports()">
                                        <i class="fas fa-chart-bar me-2"></i>View Reports
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row mb-4">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Business Performance</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="businessChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Team Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="teamChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Tabs -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs" id="activityTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button" role="tab">
                                                <i class="fas fa-user-friends me-1"></i>Recent Customers
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="team-tab" data-bs-toggle="tab" data-bs-target="#team" type="button" role="tab">
                                                <i class="fas fa-users me-1"></i>Team Members
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button" role="tab">
                                                <i class="fas fa-exchange-alt me-1"></i>Transactions
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-3" id="activityTabsContent">
                                        <!-- Recent Customers -->
                                        <div class="tab-pane fade show active" id="customers" role="tabpanel">
                                            <?php if (!empty($recent_customers)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Customer</th>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($recent_customers as $customer): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                                        <i class="fas fa-user"></i>
                                                                    </div>
                                                                    <div>
                                                                        <strong><?php echo htmlspecialchars($customer['customer_name']); ?></strong><br>
                                                                        <small class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <strong>₹<?php echo number_format($customer['total_amount']); ?></strong><br>
                                                                <small class="text-<?php echo $customer['remaining_amount'] > 0 ? 'warning' : 'success'; ?>">
                                                                    <?php echo $customer['remaining_amount'] > 0 ? '₹' . number_format($customer['remaining_amount']) . ' pending' : 'Paid'; ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <span class="status-badge status-<?php echo strtolower($customer['status']); ?>">
                                                                    <?php echo ucfirst($customer['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($customer['booking_date'])); ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No customers yet</h6>
                                                <p class="text-muted">Start earning by sharing your referral link</p>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Team Members -->
                                        <div class="tab-pane fade" id="team" role="tabpanel">
                                            <?php if (!empty($recent_team)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Member</th>
                                                            <th>Level</th>
                                                            <th>Business</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($recent_team as $member): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                                        <i class="fas fa-user"></i>
                                                                    </div>
                                                                    <div>
                                                                        <strong><?php echo htmlspecialchars($member['full_name']); ?></strong><br>
                                                                        <small class="text-muted"><?php echo htmlspecialchars($member['mobile']); ?></small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary"><?php echo $member['current_level']; ?></span>
                                                            </td>
                                                            <td>₹<?php echo number_format($member['total_business']); ?></td>
                                                            <td>
                                                                <span class="status-badge status-<?php echo $member['status'] == 'active' ? 'active' : 'pending'; ?>">
                                                                    <?php echo ucfirst($member['status']); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No team members yet</h6>
                                                <p class="text-muted">Start building your network by sharing your referral link</p>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Transactions -->
                                        <div class="tab-pane fade" id="transactions" role="tabpanel">
                                            <?php if (!empty($recent_transactions)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th>Description</th>
                                                            <th>Amount</th>
                                                            <th>Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($recent_transactions as $transaction): ?>
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-<?php echo $transaction['transaction_type'] == 'credit' ? 'success' : 'warning'; ?>">
                                                                    <?php echo ucfirst($transaction['transaction_type']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                                            <td class="<?php echo $transaction['transaction_type'] == 'credit' ? 'text-success' : 'text-warning'; ?>">
                                                                <?php echo $transaction['transaction_type'] == 'credit' ? '+' : '-'; ?>₹<?php echo number_format($transaction['amount']); ?>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No transactions yet</h6>
                                                <p class="text-muted">Transactions will appear here once you start earning</p>
                                            </div>
                                            <?php endif; ?>
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

    <!-- Referral Modal -->
    <div class="modal fade" id="referralModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Your Referral Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="referral-code">
                                <?php echo $associate_data['referral_code'] ?? 'REF123'; ?>
                            </div>
                            <p class="mt-3 text-center">Share this code with potential associates</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Referral Link</h6>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="referralLink"
                                       value="<?php echo $_SERVER['HTTP_HOST']; ?>/apsdreamhome/associate_registration.php?ref=<?php echo $associate_data['referral_code'] ?? 'REF123'; ?>" readonly>
                                <button class="btn btn-primary" onclick="copyReferralLink()">Copy</button>
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-success" onclick="shareWhatsApp()">
                                    <i class="fab fa-whatsapp me-2"></i>Share on WhatsApp
                                </button>
                                <button class="btn btn-info" onclick="shareTelegram()">
                                    <i class="fab fa-telegram me-2"></i>Share on Telegram
                                </button>
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
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('mainContent').classList.toggle('sidebar-open');
        });

        // Tab navigation
        document.querySelectorAll('.nav-tabs button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.nav-tabs button').forEach(tab => tab.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
            });
        });

        // Chart data
        const businessData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Business Volume',
                data: [1200000, 1900000, 1500000, 2500000, 2200000, <?php echo $stats['monthly_business']; ?>],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        };

        const teamData = {
            labels: ['Direct', 'Indirect', 'Total'],
            datasets: [{
                data: [<?php echo $stats['direct_team']; ?>, <?php echo $stats['total_team'] - $stats['direct_team']; ?>, <?php echo $stats['total_team']; ?>],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)'
                ],
                borderWidth: 2
            }]
        };

        // Initialize charts when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const businessCtx = document.getElementById('businessChart').getContext('2d');
            new Chart(businessCtx, {
                type: 'line',
                data: businessData,
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

            const teamCtx = document.getElementById('teamChart').getContext('2d');
            new Chart(teamCtx, {
                type: 'doughnut',
                data: teamData,
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
        });

        // Utility functions
        function showReferralCode() {
            new bootstrap.Modal(document.getElementById('referralModal')).show();
        }

        function shareReferralLink() {
            new bootstrap.Modal(document.getElementById('referralModal')).show();
        }

        function copyReferralLink() {
            const linkInput = document.getElementById('referralLink');
            linkInput.select();
            document.execCommand('copy');
            alert('Referral link copied to clipboard!');
        }

        function shareWhatsApp() {
            const referralCode = '<?php echo $associate_data['referral_code'] ?? 'REF123'; ?>';
            const message = `🏠 Join APS Dream Homes as Associate!\n\n✅ High Commissions (5-20%)\n✅ Amazing Rewards (Mobile, Laptop, Car)\n✅ Team Building Opportunities\n\nUse my referral code: ${referralCode}\n\nRegister: ${window.location.origin}/apsdreamhome/associate_registration.php?ref=${referralCode}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
        }

        function shareTelegram() {
            const referralCode = '<?php echo $associate_data['referral_code'] ?? 'REF123'; ?>';
            const message = `Join APS Dream Homes as Associate! Use referral code: ${referralCode}`;
            window.open(`https://t.me/share/url?url=${encodeURIComponent(window.location.origin)}/apsdreamhome/associate_registration.php?ref=${referralCode}&text=${encodeURIComponent(message)}`, '_blank');
        }

        function viewProjects() {
            window.open('properties.php', '_blank');
        }

        function downloadBrochure() {
            alert('Marketing materials will be available for download soon!');
        }

        function viewReports() {
            alert('Detailed business reports will be available in the Reports section!');
        }

        // Add smooth scrolling for navigation
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
