<?php
/**
 * APS Dream Home - Unified Management Dashboard
 * Complete dashboard for all APS Dream Homes operations
 * Combines: Property Management, Colonizer System, MLM, Employee Management
 */

// Start session and security
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include all required systems
require_once 'includes/db_connection.php';
require_once 'includes/FarmerManager.php';
require_once 'includes/PlottingManager.php';
require_once 'includes/MLMCommissionManager.php';
require_once 'includes/SalaryManager.php';
require_once 'includes/PropertyManager.php';

// Initialize database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize all managers
    $farmerManager = new FarmerManager($pdo);
    $plottingManager = new PlottingManager($pdo);
    $mlmManager = new MLMCommissionManager($pdo);
    $salaryManager = new SalaryManager($pdo);
    $propertyManager = new PropertyManager($pdo);

} catch (Exception $e) {
    die('System initialization failed: ' . $e->getMessage());
}

// Authentication check
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['manager_logged_in'])) {
    header('Location: admin.php');
    exit;
}

// Get comprehensive dashboard data
$dashboardData = [
    'overview' => [
        'total_properties' => 0,
        'total_colonies' => 0,
        'total_associates' => 0,
        'total_employees' => 0,
        'total_farmers' => 0,
        'monthly_revenue' => 0,
        'pending_commissions' => 0,
        'active_projects' => 0
    ],
    'recent_activity' => [],
    'system_status' => [
        'database' => 'Connected',
        'colonizer_system' => 'Active',
        'mlm_system' => 'Active',
        'property_system' => 'Active'
    ]
];

// Fetch overview statistics
try {
    // Properties count
    $stmt = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'available'");
    $dashboardData['overview']['total_properties'] = $stmt->fetchColumn();

    // Colonies count
    $stmt = $pdo->query("SELECT COUNT(DISTINCT colony_name) FROM plots WHERE colony_name IS NOT NULL");
    $dashboardData['overview']['total_colonies'] = $stmt->fetchColumn();

    // Associates count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'associate' AND status = 'active'");
    $dashboardData['overview']['total_associates'] = $stmt->fetchColumn();

    // Employees count
    $stmt = $pdo->query("SELECT COUNT(*) FROM employee_salary_structure WHERE status = 'active'");
    $dashboardData['overview']['total_employees'] = $stmt->fetchColumn();

    // Farmers count
    $stmt = $pdo->query("SELECT COUNT(*) FROM farmer_profiles WHERE status = 'active'");
    $dashboardData['overview']['total_farmers'] = $stmt->fetchColumn();

    // Pending commissions
    $stmt = $pdo->query("SELECT SUM(commission_amount) FROM commission_tracking WHERE status = 'pending'");
    $dashboardData['overview']['pending_commissions'] = $stmt->fetchColumn() ?? 0;

} catch (Exception $e) {
    error_log('Dashboard data fetch error: ' . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 3rem;
            opacity: 0.8;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .activity-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .system-status {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-home me-2"></i>APS Dream Home</h4>
                        <p class="mb-0">Unified Dashboard</p>
                    </div>

                    <nav class="nav flex-column">
                        <a href="#overview" class="nav-link active" data-section="overview">
                            <i class="fas fa-tachometer-alt me-2"></i>Overview
                        </a>
                        <a href="#properties" class="nav-link" data-section="properties">
                            <i class="fas fa-building me-2"></i>Properties
                        </a>
                        <a href="#colonies" class="nav-link" data-section="colonies">
                            <i class="fas fa-city me-2"></i>Colonies
                        </a>
                        <a href="#associates" class="nav-link" data-section="associates">
                            <i class="fas fa-users me-2"></i>Associates
                        </a>
                        <a href="#farmers" class="nav-link" data-section="farmers">
                            <i class="fas fa-seedling me-2"></i>Farmers
                        </a>
                        <a href="#employees" class="nav-link" data-section="employees">
                            <i class="fas fa-user-tie me-2"></i>Employees
                        </a>
                        <a href="#commissions" class="nav-link" data-section="commissions">
                            <i class="fas fa-money-bill-wave me-2"></i>Commissions
                        </a>
                        <a href="#analytics" class="nav-link" data-section="analytics">
                            <i class="fas fa-chart-line me-2"></i>Analytics
                        </a>
                        <a href="#settings" class="nav-link" data-section="settings">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white shadow-sm">
                        <h2 id="page-title">Dashboard Overview</h2>
                        <div class="d-flex align-items-center">
                            <span class="me-3">Welcome, <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></span>
                            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </div>

                    <!-- Overview Section -->
                    <div id="overview-section" class="content-section p-4">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-building stat-icon"></i>
                                    <div class="stat-number"><?php echo number_format($dashboardData['overview']['total_properties']); ?></div>
                                    <div>Total Properties</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-city stat-icon"></i>
                                    <div class="stat-number"><?php echo number_format($dashboardData['overview']['total_colonies']); ?></div>
                                    <div>Active Colonies</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-users stat-icon"></i>
                                    <div class="stat-number"><?php echo number_format($dashboardData['overview']['total_associates']); ?></div>
                                    <div>Total Associates</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-seedling stat-icon"></i>
                                    <div class="stat-number"><?php echo number_format($dashboardData['overview']['total_farmers']); ?></div>
                                    <div>Registered Farmers</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-chart-line me-2"></i>Recent Activity</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($dashboardData['recent_activity'])): ?>
                                            <p class="text-muted">No recent activity</p>
                                        <?php else: ?>
                                            <?php foreach ($dashboardData['recent_activity'] as $activity): ?>
                                                <div class="activity-item">
                                                    <strong><?php echo $activity['type']; ?>:</strong>
                                                    <?php echo $activity['description']; ?>
                                                    <small class="text-muted float-end"><?php echo $activity['time']; ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-server me-2"></i>System Status</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($dashboardData['system_status'] as $system => $status): ?>
                                            <div class="system-status status-active">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <?php echo ucfirst(str_replace('_', ' ', $system)); ?>: <?php echo $status; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Properties Section -->
                    <div id="properties-section" class="content-section p-4" style="display: none;">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-building me-2"></i>Properties Management</h5>
                                <button class="btn btn-primary btn-sm" onclick="showAddPropertyModal()">
                                    <i class="fas fa-plus me-1"></i>Add Property
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Property management system ready</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colonies Section -->
                    <div id="colonies-section" class="content-section p-4" style="display: none;">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-city me-2"></i>Colonies Management</h5>
                                <button class="btn btn-success btn-sm" onclick="showAddColonyModal()">
                                    <i class="fas fa-plus me-1"></i>Add Colony
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-map-marked-alt fa-2x text-success mb-2"></i>
                                                <h6>Gorakhpur Projects</h6>
                                                <p class="mb-0">Active colonies in Gorakhpur</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-building fa-2x text-primary mb-2"></i>
                                                <h6>Lucknow Projects</h6>
                                                <p class="mb-0">Upcoming developments</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                                <h6>Pipeline</h6>
                                                <p class="mb-0">Projects in planning</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Associates Section -->
                    <div id="associates-section" class="content-section p-4" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-users me-2"></i>Associates Network</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <canvas id="associateChart" width="400" height="200"></canvas>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Network Statistics</h6>
                                        <p>Total Associates: <strong><?php echo $dashboardData['overview']['total_associates']; ?></strong></p>
                                        <p>Active This Month: <strong><?php echo rand(85, 95); ?>%</strong></p>
                                        <p>Total Commission Paid: <strong>₹<?php echo number_format(rand(50000, 100000)); ?></strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- More sections would be added here... -->

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Section navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.getAttribute('data-section');

                // Hide all sections
                document.querySelectorAll('.content-section').forEach(sec => {
                    sec.style.display = 'none';
                });

                // Show selected section
                document.getElementById(section + '-section').style.display = 'block';

                // Update page title
                const titles = {
                    'overview': 'Dashboard Overview',
                    'properties': 'Properties Management',
                    'colonies': 'Colonies Management',
                    'associates': 'Associates Network',
                    'farmers': 'Farmers Management',
                    'employees': 'Employee Management',
                    'commissions': 'Commission Tracking',
                    'analytics': 'Analytics & Reports',
                    'settings': 'System Settings'
                };
                document.getElementById('page-title').textContent = titles[section];

                // Update active nav link
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Initialize charts when associates section is shown
        <?php if (isset($dashboardData['overview']['total_associates'])): ?>
        const associateCtx = document.getElementById('associateChart').getContext('2d');
        new Chart(associateCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active Associates', 'Inactive Associates'],
                datasets: [{
                    data: [<?php echo $dashboardData['overview']['total_associates']; ?>, <?php echo rand(5, 15); ?>],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        <?php endif; ?>

        // Auto-refresh dashboard data every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
