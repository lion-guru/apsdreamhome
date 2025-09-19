<?php
// Admin Dashboard - Modern UI with Analytics
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// सेशन डीबग जानकारी प्रिंट करें - इसे हटा दें या कमेंट करें
// echo "<pre>DEBUG SESSION: ";
// var_dump($_SESSION);
// echo "</pre>";

// Session timeout check - Must be before any output
if (isset($_SESSION['last_activity']) && isset($sessionConfig['timeout_duration']) && 
    (time() - $_SESSION['last_activity']) > $sessionConfig['timeout_duration']) {
    // Session expired
    session_unset();
    session_destroy();
    if (function_exists('logError')) {
        logError('Session Timeout', [
            'username' => $_SESSION['admin_username'] ?? 'UNKNOWN',
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
    }
    header('Location: index.php?error=session_expired');
    exit();
}

// Periodic session ID regeneration for security
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (isset($sessionConfig['regenerate_interval']) && time() - $_SESSION['created'] > $sessionConfig['regenerate_interval']) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Check if user is logged in - यहां परिवर्तन करें
// दोनों सेशन वेरिएबल्स की जांच करें
if ((!isset($_SESSION['admin_session']) || $_SESSION['admin_session']['is_authenticated'] !== true) && 
    (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)) {
    header('Location: index.php');
    exit();
}

// Role-based dashboard redirection
$role = $_SESSION['admin_role'] ?? $_SESSION['admin_session']['role'] ?? '';
if ($role !== 'admin') {
    $role_dashboard_map = [
        'superadmin' => 'superadmin_dashboard.php',
        'manager' => 'manager_dashboard.php',
        'director' => 'director_dashboard.php',
        'office_admin' => 'office_admin_dashboard.php',
        'sales' => 'sales_dashboard.php',
        'employee' => 'employee_dashboard.php',
        'legal' => 'legal_dashboard.php',
        'marketing' => 'marketing_dashboard.php',
        'finance' => 'finance_dashboard.php',
        'hr' => 'hr_dashboard.php',
        'it' => 'it_dashboard.php',
        'operations' => 'operations_dashboard.php',
        'support' => 'support_dashboard.php',
    ];
    if (isset($role_dashboard_map[$role]) && file_exists(__DIR__ . '/' . $role_dashboard_map[$role])) {
        header('Location: ' . $role_dashboard_map[$role]);
        exit();
    } else if (file_exists(__DIR__ . '/' . $role . '_dashboard.php')) {
        header('Location: ' . $role . '_dashboard.php');
        exit();
    } else {
        header('Location: index.php?error=unauthorized');
        exit();
    }
}
// All session and redirect logic is now at the top. No output before this point.

// Include database connection
require_once __DIR__ . '/../includes/db_connection.php';
$conn = getDbConnection();

// Get dashboard statistics
try {
    // Property statistics
    $property_stats = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM properties")->fetch_assoc()['count'] ?? 0,
        'sold' => $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'sold'")->fetch_assoc()['count'] ?? 0,
        'available' => $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'available'")->fetch_assoc()['count'] ?? 0,
        'under_contract' => $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'under_contract'")->fetch_assoc()['count'] ?? 0,
    ];
    
    // Booking statistics
    $booking_stats = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'] ?? 0,
        'pending' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0,
        'confirmed' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['count'] ?? 0,
        'completed' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'")->fetch_assoc()['count'] ?? 0,
    ];
    
    // Customer statistics
    $customer_stats = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'] ?? 0,
        'new_this_month' => $conn->query("SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')")->fetch_assoc()['count'] ?? 0,
    ];
    
    // Lead statistics
    $lead_stats = [
        'total' => $conn->query("SELECT COUNT(*) as count FROM leads")->fetch_assoc()['count'] ?? 0,
        'new' => $conn->query("SELECT COUNT(*) as count FROM leads WHERE status = 'new'")->fetch_assoc()['count'] ?? 0,
        'contacted' => $conn->query("SELECT COUNT(*) as count FROM leads WHERE status = 'contacted'")->fetch_assoc()['count'] ?? 0,
        'qualified' => $conn->query("SELECT COUNT(*) as count FROM leads WHERE status = 'qualified'")->fetch_assoc()['count'] ?? 0,
    ];
    
    // Recent activities
    $recent_activities = [];
    // Get recent activities from different tables
    $activities_query = $conn->query(
        "(SELECT 'booking' as type, id, customer_name, created_at, 'New booking received' as description 
          FROM bookings 
          ORDER BY created_at DESC LIMIT 5)
         UNION ALL
         (SELECT 'lead' as type, id, name as customer_name, created_at, 'New lead added' as description 
          FROM leads 
          ORDER BY created_at DESC LIMIT 5)
         ORDER BY created_at DESC LIMIT 5"
    );
    
    if ($activities_query) {
        $recent_activities = $activities_query->fetch_all(MYSQLI_ASSOC);
    }
    
    // Recent bookings
    $recent_bookings = $conn->query(
        "SELECT b.*, c.name as customer_name, p.title as property_title 
         FROM bookings b 
         LEFT JOIN customers c ON b.customer_id = c.id 
         LEFT JOIN properties p ON b.property_id = p.id 
         ORDER BY b.created_at DESC LIMIT 5"
    )->fetch_all(MYSQLI_ASSOC);
    
    // Revenue data for chart
    $revenue_data = [];
    $revenue_query = $conn->query(
        "SELECT 
            DATE_FORMAT(created_at, '%b %Y') as month,
            SUM(amount) as total
         FROM transactions
         WHERE status = 'completed'
         GROUP BY DATE_FORMAT(created_at, '%Y-%m')
         ORDER BY created_at DESC
         LIMIT 6"
    );
    
    if ($revenue_query) {
        $revenue_data = array_reverse($revenue_query->fetch_all(MYSQLI_ASSOC));
    }
    
} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}

// Include the new header
include 'includes/new_header.php';
?>

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Dashboard</h1>
    <div>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Quick Action
        </button>
    </div>
</div>

<!-- Recent Activities Section -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Details</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Display recent bookings
                            $recent_bookings = $conn->query("SELECT b.*, p.address as property_title FROM bookings b LEFT JOIN properties p ON b.property_id = p.id ORDER BY b.booking_date DESC LIMIT 5");
                            while($booking = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge bg-info">Booking</span></td>
                                <td>New booking for <?php echo htmlspecialchars($booking['property_title'] ?? 'Property #' . $booking['property_id']); ?></td>
                                <td><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></td>
                                <td><span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <?php
                            // Display recent leads
                            $recent_leads = $conn->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
                            while($lead = $recent_leads->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge bg-primary">Lead</span></td>
                                <td>New lead from <?php echo htmlspecialchars($lead['name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($lead['created_at'])); ?></td>
                                <td><span class="badge bg-<?php echo $lead['status'] === 'contacted' ? 'success' : 'warning'; ?>"><?php echo ucfirst($lead['status']); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats and Charts -->
<div class="row g-4 mb-4">
    <!-- Properties Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">Properties</h6>
                        <h2 class="mb-0"><?php echo number_format($property_stats['total']); ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-home text-primary" style="font-size: 1.75rem;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success me-2">
                        <i class="fas fa-check-circle me-1"></i> <?php echo $property_stats['available']; ?> Available
                    </span>
                    <span class="badge bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-file-contract me-1"></i> <?php echo $property_stats['under_contract']; ?> Under Contract
                    </span>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="properties.php" class="text-decoration-none small">View all properties <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Bookings Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">Bookings</h6>
                        <h2 class="mb-0"><?php echo number_format($booking_stats['total']); ?></h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-calendar-check text-info" style="font-size: 1.75rem;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-warning bg-opacity-10 text-warning me-2">
                        <i class="fas fa-clock me-1"></i> <?php echo $booking_stats['pending']; ?> Pending
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check me-1"></i> <?php echo $booking_stats['confirmed']; ?> Confirmed
                    </span>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="bookings.php" class="text-decoration-none small">View all bookings <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Customers Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">Customers</h6>
                        <h2 class="mb-0"><?php echo number_format($customer_stats['total']); ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-users text-success" style="font-size: 1.75rem;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-user-plus me-1"></i> <?php echo $customer_stats['new_this_month']; ?> New this month
                    </span>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="customers.php" class="text-decoration-none small">View all customers <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Leads Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">Leads</h6>
                        <h2 class="mb-0"><?php echo number_format($lead_stats['total']); ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-user-tie text-warning" style="font-size: 1.75rem;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-danger bg-opacity-10 text-danger me-2">
                        <i class="fas fa-exclamation-circle me-1"></i> <?php echo $lead_stats['new']; ?> New
                    </span>
                    <span class="badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-phone me-1"></i> <?php echo $lead_stats['contacted']; ?> Contacted
                    </span>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="leads.php" class="text-decoration-none small">View all leads <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- --- ADVANCED ADMIN DASHBOARD WIDGETS --- -->
<div class="row">
    <!-- Audit Access Log Widget -->
    <div class="col-md-4 mb-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white"><i class="fas fa-history"></i> Audit Access Log</div>
            <div class="card-body">
                <?php
                try {
                    $audit = $conn->query("SELECT COUNT(*) as total, SUM(action='export') as exports, SUM(action='drilldown') as drilldowns FROM audit_access_log")->fetch_assoc();
                    echo "<b>Total Logs:</b> ".($audit['total'] ?? 0)."<br>";
                    echo "<b>Exports:</b> ".($audit['exports'] ?? 0)."<br>";
                    echo "<b>Drilldowns:</b> ".($audit['drilldowns'] ?? 0)."<br>";
                } catch(Exception $e) {
                    echo "No data found.";
                }
                ?>
                <a href="audit_access_log_view.php" class="btn btn-link p-0">View Details &raquo;</a>
            </div>
        </div>
    </div>
    <!-- Compliance Status Widget -->
    <div class="col-md-4 mb-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark"><i class="fas fa-balance-scale"></i> Compliance Status</div>
            <div class="card-body">
                <?php
                try {
                    $compliance = $conn->query("SELECT COUNT(*) as total, SUM(status='passed') as passed, SUM(status='failed') as failed FROM compliance_audit_bot")->fetch_assoc();
                    echo "<b>Total Audits:</b> ".($compliance['total'] ?? 0)."<br>";
                    echo "<b>Passed:</b> ".($compliance['passed'] ?? 0)."<br>";
                    echo "<b>Failed:</b> ".($compliance['failed'] ?? 0)."<br>";
                } catch(Exception $e) {
                    echo "No data found.";
                }
                ?>
                <a href="compliance_dashboard.php" class="btn btn-link p-0">Compliance Dashboard &raquo;</a>
            </div>
        </div>
    </div>
    <!-- Payouts/Commission Widget -->
    <div class="col-md-4 mb-4">
        <div class="card border-success">
            <div class="card-header bg-success text-white"><i class="fas fa-coins"></i> Payouts & Commission</div>
            <div class="card-body">
                <?php
                try {
                    $payouts = $conn->query("SELECT COUNT(*) as total, SUM(status='pending') as pending, SUM(status='paid') as paid FROM payouts")->fetch_assoc();
                    echo "<b>Total Payouts:</b> ".($payouts['total'] ?? 0)."<br>";
                    echo "<b>Pending:</b> ".($payouts['pending'] ?? 0)."<br>";
                    echo "<b>Paid:</b> ".($payouts['paid'] ?? 0)."<br>";
                } catch(Exception $e) {
                    echo "No data found.";
                }
                ?>
                <a href="payouts_report.php" class="btn btn-link p-0">Payouts Report &raquo;</a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <!-- Scheduled Reports Widget -->
    <div class="col-md-4 mb-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white"><i class="fas fa-calendar-alt"></i> Scheduled Reports</div>
            <div class="card-body">
                <?php
                try {
                    $reports = $conn->query("SELECT COUNT(*) as total, SUM(status='scheduled') as scheduled, SUM(status='sent') as sent FROM scheduled_report")->fetch_assoc();
                    echo "<b>Total:</b> ".($reports['total'] ?? 0)."<br>";
                    echo "<b>Scheduled:</b> ".($reports['scheduled'] ?? 0)."<br>";
                    echo "<b>Sent:</b> ".($reports['sent'] ?? 0)."<br>";
                } catch(Exception $e) {
                    echo "No data found.";
                }
                ?>
                <a href="scheduled_report.php" class="btn btn-link p-0">View Scheduled Reports &raquo;</a>
            </div>
        </div>
    </div>
    <!-- Quick Actions Widget -->
    <div class="col-md-8 mb-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white"><i class="fas fa-bolt"></i> Quick Actions</div>
            <div class="card-body">
                <a href="add_employee.php" class="btn btn-outline-primary m-1">Add Employee</a>
                <a href="add_role.php" class="btn btn-outline-secondary m-1">Add Role</a>
                <a href="properties.php" class="btn btn-outline-success m-1">Add Property</a>
                <a href="leads.php" class="btn btn-outline-warning m-1">Add Lead</a>
                <a href="notification_management.php" class="btn btn-outline-danger m-1">Send Notification</a>
                <a href="documents_dashboard.php" class="btn btn-outline-info m-1">Upload Document</a>
            </div>
        </div>
    </div>
</div>
<!-- --- ADVANCED ADMIN DASHBOARD WIDGETS END --- -->

 <!-- Analytics Charts -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="mb-0">Leads by Status</h5></div>
            <div class="card-body"><canvas id="leadsStatusChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="mb-0">Revenue Trend (Last 6 Months)</h5></div>
            <div class="card-body"><canvas id="revenueTrendChart" style="min-height: 350px; height: 350px; max-width: 100%;"></canvas></div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">Revenue Overview</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Leads by Status Chart Data
<?php
$lead_status_data = [];
$lead_status_labels = [];
$res = $conn->query("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
while($row = $res->fetch_assoc()) {
    $lead_status_labels[] = $row['status'] ?: 'Unknown';
    $lead_status_data[] = $row['count'];
}
// Revenue Trend Data (last 6 months)
$revenue_labels = [];
$revenue_data = [];
$res2 = $conn->query("SELECT DATE_FORMAT(date, '%b %Y') as month, SUM(amount) as total FROM transactions WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY MIN(date)");
while($row2 = $res2->fetch_assoc()) {
    $revenue_labels[] = $row2['month'];
    $revenue_data[] = $row2['total'] ?: 0;
}
?>
const leadsStatusCtx = document.getElementById('leadsStatusChart').getContext('2d');
const leadsStatusChart = new Chart(leadsStatusCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($lead_status_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($lead_status_data); ?>,
            backgroundColor: [
                '#0d6efd','#198754','#ffc107','#dc3545','#6c757d','#6610f2','#fd7e14'
            ],
        }]
    },
    options: {responsive: true}
});
const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
const revenueTrendChart = new Chart(revenueTrendCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($revenue_labels); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($revenue_data); ?>,
            backgroundColor: '#0d6efd',
        }]
    },
    options: {responsive: true, plugins: {legend: {display: false}}}
});

// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Monthly Revenue',
            data: [12000, 19000, 15000, 25000, 22000, 30000, 28000, 35000, 30000, 40000, 38000, 45000],
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
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
                beginAtZero: true,
                grid: {
                    display: true,
                    drawBorder: false
                },
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">My Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                // Get current user data
                $user_id = $_SESSION['admin_session']['user_id'] ?? 0;
                $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
                ?>
                <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="currentProfilePicture" value="<?php echo $user['profile_picture'] ?? ''; ?>">
                    <div class="text-center mb-4">
                        <div class="profile-upload-wrapper position-relative d-inline-block <?php echo !empty($user['profile_picture']) ? 'has-image' : ''; ?>" style="cursor: pointer;">
                            <?php 
                            $profile_pic = !empty($user['profile_picture']) 
                                ? 'uploads/profile_pictures/' . $user['profile_picture'] 
                                : 'assets/img/default-avatar.php?name=' . urlencode($user['name'] ?? 'U');
                            ?>
                            <img id="profileImage" src="<?php echo $profile_pic; ?>" 
                                 class="rounded-circle border border-3 border-primary" 
                                 width="140" height="140" 
                                 style="object-fit: cover; width: 140px; height: 140px; cursor: pointer;">
                            
                            <div class="profile-upload-controls">
                                <input type="file" id="profilePicture" name="profile_picture" accept="image/jpeg,image/png,image/gif" class="d-none">
                                <?php if (!empty($user['profile_picture'])): ?>
                                <button type="button" class="profile-remove-btn" title="Remove picture">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                            
                            <div class="profile-upload-hover" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background: rgba(0,0,0,0.5); color: white; border-radius: 50%; opacity: 0; transition: opacity 0.3s; pointer-events: none;">
                                <i class="fas fa-camera mb-1"></i>
                                <small>Change Photo</small>
                            </div>
                        </div>
                        <div class="small text-muted mt-2">JPG, PNG or GIF (Max: 2MB)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Password must be at least 8 characters long</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePasswordVisibility(field) {
    const input = $(field).siblings('input');
    const icon = $(field).find('i');
    const type = input.attr('type') === 'password' ? 'text' : 'password';
    input.attr('type', type);
    icon.toggleClass('fa-eye fa-eye-slash');
}

// Handle profile form submission with AJAX
$(document).ready(function() {
    // Toggle password visibility
    $(document).on('click', '.toggle-password', function() {
        togglePasswordVisibility(this);
    });
    
    // Profile picture click handler
    $('.profile-upload-wrapper').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#profilePicture').trigger('click');
    });
    
    // Profile picture preview and validation
    $('#profilePicture').on('change', function(e) {
        const file = e.target.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!file) return;
        
        // Check file type
        if (!validTypes.includes(file.type)) {
            showAlert('Please upload a valid image file (JPEG, PNG, GIF)', 'error');
            $(this).val('');
            return;
        }
        
        // Validate file size
        if (file.size > maxSize) {
            showAlert('File size must be less than 2MB', 'error');
            $(this).val('');
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#profileImage').attr('src', e.target.result);
            // Show the remove button if it's hidden
            $('.profile-remove-btn').removeClass('d-none');
            // Clear any remove profile picture flag
            $('input[name="remove_profile_picture"]').remove();
            // Show success message
            showAlert('Profile picture updated successfully', 'success');
        };
        reader.onerror = function() {
            showAlert('Error reading the file', 'error');
        };
        reader.readAsDataURL(file);
    });
    
    // Show/hide hover effect on profile picture
    $('.profile-upload-wrapper')
        .on('mouseenter', function() {
            $(this).find('.profile-upload-hover').css('opacity', '1');
        })
        .on('mouseleave', function() {
            $(this).find('.profile-upload-hover').css('opacity', '0');
        });
    
    // Handle remove profile picture
    $('.remove-picture-btn').on('click', function(e) {
        e.preventDefault();
        
        // Clear the file input
        $('#profilePicture').val('');
        
        // Set a flag to indicate picture should be removed
        $('<input>').attr({
            type: 'hidden',
            name: 'remove_profile_picture',
            value: '1'
        }).appendTo('#profileForm');
        
        // Reset preview to default avatar
        const defaultAvatar = 'assets/img/avatar.png';
        $('#profileImage').attr('src', defaultAvatar);
        
        // Hide the remove button
        $(this).addClass('d-none');
        
        // Show success message
        showAlert('Profile picture will be removed after saving changes', 'info');
    });
    
    // Remove profile picture
    $(document).on('click', '.profile-remove-btn', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to remove your profile picture?')) {
            return;
        }
        
        const userId = <?php echo $user_id; ?>;
        const removeBtn = $(this);
        removeBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        
        $.ajax({
            url: 'remove_profile_picture.php',
            type: 'POST',
            data: { user_id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Reset file input
                    $('#profilePicture').val('');
                    
                    // Update UI
                    const defaultSrc = 'assets/img/default-avatar.php?name=' + encodeURIComponent($('.user-name').text().trim());
                    $('#profileImage').attr('src', defaultSrc);
                    $('.profile-upload-wrapper').removeClass('has-image');
                    $('.profile-remove-btn').remove();
                    
                    // Also update the header avatar
                    $('.user-avatar img').attr('src', defaultSrc);
                    
                    showAlert('Profile picture removed successfully', 'success');
                } else {
                    showAlert(response.message || 'Failed to remove profile picture', 'error');
                }
            },
            error: function() {
                showAlert('An error occurred. Please try again.', 'error');
            },
            complete: function() {
                removeBtn.prop('disabled', false).html('<i class="fas fa-times"></i>');
            }
        });
    });
    
    // Handle profile form submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(this);
        
        // Add current picture to form data
        formData.append('current_picture', $('#currentProfilePicture').val());
        
        // Show loading state
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        console.log('Submitting form...');
        
        // Submit the form via AJAX
        $.ajax({
            url: 'update_profile.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('Response received:', response);
                if (response.success) {
                    // Update UI with new data
                    if (typeof response.profile_picture !== 'undefined') {
                        if (response.profile_picture) {
                            // New picture was uploaded
                            var newImageUrl = 'uploads/profile_pictures/' + response.profile_picture + '?t=' + new Date().getTime();
                            $('#profileImage').attr('src', newImageUrl);
                            $('#currentProfilePicture').val(response.profile_picture);
                            $('.profile-remove-btn').removeClass('d-none');
                            
                            // Update profile picture in the header if it exists
                            $('.user-avatar, .profile-user-img').attr('src', newImageUrl);
                        } else {
                            // Picture was removed
                            var defaultImage = 'assets/img/default-avatar.php?name=' + encodeURIComponent(response.name || 'U');
                            $('#profileImage').attr('src', defaultImage);
                            $('#currentProfilePicture').val('');
                            $('.profile-remove-btn').addClass('d-none');
                            
                            // Update profile picture in the header if it exists
                            $('.user-avatar, .profile-user-img').attr('src', defaultImage);
                        }
                    }
                    
                    // Update username in header and modal
                    if (response.name) {
                        $('.user-name').text(response.name);
                        document.title = document.title.replace(/^[^-]+/, response.name);
                        $('#name').val(response.name);
                    }
                    
                    // Update phone if changed
                    if (response.phone) {
                        $('#phone').val(response.phone);
                    }
                    
                    showAlert(response.message || 'Profile updated successfully', 'success');
                    
                    // Close modal after delay
                    setTimeout(function() {
                        $('#profileModal').modal('hide');
                    }, 1500);
                } else {
                    showAlert(response.message || 'Failed to update profile. Please try again.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                
                var errorMessage = 'An error occurred while updating your profile';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Error parsing error response:', e);
                }
                
                showAlert(errorMessage, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
    
    // Handle remove profile picture
    $('.profile-remove-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (confirm('Are you sure you want to remove your profile picture?')) {
            // Add a hidden field to indicate picture removal
            $('<input>').attr({
                type: 'hidden',
                name: 'remove_picture',
                value: '1'
            }).appendTo('#profileForm');
            
            // Clear the file input
            $('#profilePicture').val('');
            
            // Submit the form
            $('#profileForm').submit();
        }
    });
    
    // Handle change password form submission
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        
        // Validate passwords match
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();
        
        if (newPassword !== confirmPassword) {
            showAlert('New password and confirm password do not match', 'error');
            return;
        }
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Changing...');
        
        $.ajax({
            url: 'change_password.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    // Reset form and close modal
                    form.trigger('reset');
                    $('#changePasswordModal').modal('hide');
                    
                    // If there's a redirect URL, redirect after a delay
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 3000); // 3 seconds delay
                    }
                } else {
                    showAlert(response.message || 'Error changing password', 'error');
                }
            },
            error: function() {
                showAlert('An error occurred. Please try again.', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
    
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'update_profile.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert('Profile updated successfully', 'success');
                    // Close modal after 1.5 seconds
                    setTimeout(function() {
                        $('#profileModal').modal('hide');
                        // Reload the page to reflect changes in the header
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(response.message || 'Error updating profile', 'error');
                }
            },
            error: function() {
                showAlert('An error occurred. Please try again.', 'error');
            }
        });
    });
});

// Function to show alert messages
function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Remove any existing alerts
    $('.alert-dismissible').alert('close');
    
    // Add new alert
    $('.main-content > .container-fluid').prepend(alertHtml);
    
    // Auto-close after 5 seconds
    setTimeout(() => {
        $('.alert-dismissible').alert('close');
    }, 5000);
}
</script>

<!-- Include the footer -->
<?php include 'includes/new_footer.php'; ?>



<?php
function logError($message, $context = []) {
    // Validate and create logs directory
    $logDir = __DIR__ . '/../logs';
    try {
        if (!is_dir($logDir)) {
            // Attempt to create directory with full permissions
            if (!mkdir($logDir, 0777, true)) {
                // Fallback to system temp directory if creation fails
                $logDir = sys_get_temp_dir() . '/apsdreamhome_logs';
                mkdir($logDir, 0777, true);
            }
        }

        $logFile = $logDir . '/dashboard_error.log';
        
        // Prepare timestamp and context
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = '';
        
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                // Advanced context value conversion
                try {
                    if (is_null($value)) {
                        $strValue = 'NULL';
                    } elseif (is_bool($value)) {
                        $strValue = $value ? 'TRUE' : 'FALSE';
                    } elseif (is_scalar($value)) {
                        $strValue = (string)$value;
                    } elseif (is_array($value) || is_object($value)) {
                        $strValue = json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    } else {
                        $strValue = 'UNKNOWN_TYPE';
                    }
                    
                    // Truncate extremely long values
                    $strValue = mb_strlen($strValue) > 500 ? mb_substr($strValue, 0, 500) . '...' : $strValue;
                    
                    $contextStr .= " | $key: $strValue";
                } catch (Exception $e) {
                    $contextStr .= " | $key: SERIALIZATION_ERROR";
                }
            }
        }
        
        // Construct log message
        $logMessage = "[{$timestamp}] {$message}{$contextStr}\n";
        
        // Write to log file with error handling
        if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
            // Fallback error logging
            error_log("CRITICAL: Unable to write to log file. Message: {$logMessage}");
        }
        
        // Additional system error logging
        error_log($logMessage);
    } catch (Exception $e) {
        // Last resort error logging
        error_log("CRITICAL LOGGING FAILURE: " . $e->getMessage());
    }
}

// Validate critical paths and dependencies
function validateSystemPaths() {
    $criticalPaths = [
        'includes_dir' => __DIR__ . '/../includes',
        'config_dir' => __DIR__ . '/../config',
        'db_connection_file' => __DIR__ . '/../includes/db_connection.php',
        'session_manager_file' => __DIR__ . '/../app/Services/SessionManager.php'
    ];

    $missingPaths = [];
    foreach ($criticalPaths as $name => $path) {
        if (!file_exists($path)) {
            $missingPaths[] = $name . ': ' . $path;
        }
    }

    if (!empty($missingPaths)) {
        logError('Critical Path Validation Failed', [
            'missing_paths' => $missingPaths
        ]);
        throw new Exception('System configuration paths are missing: ' . implode(', ', $missingPaths));
    }
}

// Global error and exception handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError('PHP Error', [
        'errno' => $errno,
        'errstr' => $errstr,
        'errfile' => $errfile,
        'errline' => $errline
    ]);
    return false; // Let PHP handle the error
});

set_exception_handler(function($exception) {
    logError('Uncaught Exception', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
});
