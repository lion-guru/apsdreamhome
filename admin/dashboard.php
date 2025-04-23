<?php
session_start();
require_once(__DIR__ . '/../includes/db_config.php');
$conn = getDbConnection();
global $con;
$con = $conn;
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/includes/csrf_protection.php';
require_once __DIR__ . '/includes/dashboard_utils.php';
require("config.php");

require_once(__DIR__ . '/../includes/SessionManager.php');
$sessionManager = new SessionManager();
$sessionManager->requireAdmin();

// Secure authentication check
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: login.php');
//     exit();
// }

// Initialize session with proper security settings
// initAdminSession();

// Validate admin session
// if (!validateAdminSession()) {
//     header("location:login.php");
//     exit();
// }

// Check database connection
if (!$conn) {
    die("Database connection failed");
}

// Create required tables if they don't exist
$sql = "CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    user_id INT,
    status VARCHAR(50),
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    amount DECIMAL(10,2),
    status VARCHAR(50),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    contact VARCHAR(100),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Enhanced analytics functions
function getAnalytics($con) {
    $analytics = array();
    
    // Basic counts
    $analytics['total_projects'] = getTotalProjects($con);
    $analytics['total_properties'] = getTotalProperties($con);
    $analytics['total_bookings'] = getTotalBookings($con);
    $analytics['total_customers'] = getTotalCustomers($con);
    
    // Revenue metrics
    $analytics['total_revenue'] = getTotalRevenue($con);
    $analytics['monthly_revenue'] = getMonthlyRevenue($con);
    
    // Property metrics
    $analytics['property_types'] = getPropertyTypeDistribution($con);
    $analytics['booking_status'] = getBookingStatusDistribution($con);
    
    // Lead metrics
    $analytics['lead_conversion'] = getLeadConversionRate($con);
    $analytics['active_leads'] = getActiveLeadsCount($con);
    
    return $analytics;
}

function getTotalProjects($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM projects");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalProperties($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM property_types");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalBookings($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM bookings");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalCustomers($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM users WHERE type = 'customer'");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalRevenue($con) {
    // No 'status' column in transactions; sum all amounts
    $stmt = $con->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getMonthlyRevenue($con) {
    // No 'status' or 'date' column in transactions, use created_at
    $stmt = $con->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COALESCE(SUM(amount), 0) as revenue 
                         FROM transactions 
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
                         GROUP BY month 
                         ORDER BY month DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getPropertyTypeDistribution($con) {
    $stmt = $con->prepare("SELECT type, COUNT(*) as count 
                         FROM property_types 
                         GROUP BY type");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getBookingStatusDistribution($con) {
    $stmt = $con->prepare("SELECT status, COUNT(*) as count 
                         FROM bookings 
                         GROUP BY status");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getLeadConversionRate($con) {
    $stmt = $con->prepare("SELECT 
                         (SELECT COUNT(*) FROM leads WHERE status = 'converted') as converted,
                         COUNT(*) as total 
                         FROM leads");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return ($result['total'] > 0) ? ($result['converted'] / $result['total'] * 100) : 0;
}

function getActiveLeadsCount($con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM leads WHERE status = 'active'");
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

// Use standardized admin header
include __DIR__ . '/../includes/templates/dynamic_header.php';
// MLM Commission Widget
include __DIR__ . '/dashboard_commission_widget.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>APS DREAM HOMES - Dashboard</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('aps.png', 'images'); ?>">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">

    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">

    <link rel="stylesheet" href="assets/plugins/morris/morris.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <!-- Main Wrapper -->
    <!-- /Header -->

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
					<br>
                        <h3 class="page-title">Welcome Admin!</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <?php $analytics = getAnalytics($conn); ?>
            <div class="row">
                <!-- Monthly Revenue Chart -->
                <div class="col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">मासिक राजस्व विश्लेषण</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Property Distribution Chart -->
                <div class="col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">प्रॉपर्टी प्रकार वितरण</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="propertyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Initialization -->
            <script>
            // Monthly Revenue Chart
            const revenueData = <?php echo json_encode($analytics['monthly_revenue']); ?>;
            const months = revenueData.map(item => item.month);
            const revenues = revenueData.map(item => item.revenue);

            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'मासिक राजस्व',
                        data: revenues,
                        borderColor: '#2196f3',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'मासिक राजस्व ट्रेंड'
                        }
                    }
                }
            });

            // Property Distribution Chart
            const propertyData = <?php echo json_encode($analytics['property_types']); ?>;
            const propertyTypes = propertyData.map(item => item.type);
            const propertyCounts = propertyData.map(item => item.count);

            new Chart(document.getElementById('propertyChart'), {
                type: 'doughnut',
                data: {
                    labels: propertyTypes,
                    datasets: [{
                        data: propertyCounts,
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3', 
                            '#FFC107',
                            '#E91E63',
                            '#9C27B0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: 'प्रॉपर्टी प्रकार वितरण'
                        }
                    }
                }
            });
            </script>

            <div class="row">
                <!-- Revenue Overview -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-success">
                                    <i class="fe fe-money"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3>₹<?php echo number_format($analytics['total_revenue']); ?></h3>
                                <h6 class="text-muted">कुल राजस्व</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success w-75"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Projects -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-info">
                                    <i class="fe fe-home"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo $analytics['total_projects']; ?></h3>
                                <h6 class="text-muted">कुल प्रोजेक्ट्स</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-info w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Leads -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-warning">
                                    <i class="fe fe-user-plus"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo $analytics['active_leads']; ?></h3>
                                <h6 class="text-muted">सक्रिय लीड्स</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-warning w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Conversion Rate -->
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-primary">
                                    <i class="fe fe-chart-bar"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo number_format($analytics['lead_conversion'], 1); ?>%</h3>
                                <h6 class="text-muted">लीड कन्वर्जन दर</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $analytics['lead_conversion']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-success">
                                    <i class="fe fe-users"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countUsersByType($conn, 'agent'); ?></h3>
                                <h6 class="text-muted">Agents</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-danger">
                                    <i class="fe fe-user"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countUsersByType($conn, 'builder'); ?></h3>
                                <h6 class="text-muted">Builders</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-danger w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-info">
                                    <i class="fe fe-home"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, ''); // Count all properties ?></h3>
                                <h6 class="text-muted">Properties</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-info w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Rows for Property Types -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-warning">
                                    <i class="fe fe-table"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, 'apartment'); ?></h3>
                                <h6 class="text-muted">No. of Apartments</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-warning w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-info">
                                    <i class="fe fe-home"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, 'house'); ?></h3>
                                <h6 class="text-muted">No. of Houses</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-info w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-secondary">
                                    <i class="fe fe-building"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, 'building'); ?></h3>
                                <h6 class="text-muted">No. of Buildings</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-secondary w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-primary">
                                    <i class="fe fe-tablet"></i>
                                </span>
                            </div>
                            <div class="dash-widget-info">
                                <h3><?php echo countPropertiesByType($conn, 'flat'); ?></h3>
                                <h6 class="text-muted">No. of Flats</h6>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary w-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			
			

            <!-- Sales Overview and Order Status -->
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <div class="card card-chart">
                        <div class="card-header">
                            <h4 class="card-title">Sales Overview</h4>
                        </div>
                        <div class="card-body">
                            <div id="morrisArea"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-lg-6">
                    <div class="card card-chart">
                        <div class="card-header">
                            <h4 class="card-title">Order Status</h4>
                        </div>
                        <div class="card-body">
                            <div id="morrisLine"></div>
                        </div>
                    </div>
                </div>	
            </div>
        </div>			
    </div>
	
    <!-- /Page Wrapper -->

    <!-- jQuery -->
    <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>

    <!-- Bootstrap Core JS -->
    <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>

    <!-- Slimscroll JS -->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="assets/plugins/raphael/raphael.min.js"></script>    
    <script src="assets/plugins/morris/morris.min.js"></script>  
    <script src="<?php echo get_asset_url('js/chart.morris.js', 'js'); ?>"></script>

    <!-- Custom JS -->
    <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</html>
