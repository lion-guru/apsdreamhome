<?php
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$page_title = 'Admin Dashboard';
$conn = getDbConnection();
// Stats
$stats = [
    'projects' => 0,
    'bookings' => 0,
    'customers' => 0,
    'revenue' => 0,
    'expenses' => 0,
    'leads' => 0
];
if ($conn) {
    // Use correct table names
    $result = $conn->query("SELECT COUNT(*) FROM projects");
    if ($result) {
        $stats['projects'] = $result->fetch_row()[0];
    }
    $result = $conn->query("SELECT COUNT(*) FROM bookings");
    if ($result) {
        $stats['bookings'] = $result->fetch_row()[0];
    }
    $result = $conn->query("SELECT COUNT(*) FROM users");
    if ($result) {
        $stats['customers'] = $result->fetch_row()[0];
    }
    $result = $conn->query("SELECT SUM(amount) FROM transactions WHERE type='income'");
    if ($result) {
        $stats['revenue'] = $result->fetch_row()[0] ?? 0;
    }
    $result = $conn->query("SELECT SUM(amount) FROM transactions WHERE type='expense'");
    if ($result) {
        $stats['expenses'] = $result->fetch_row()[0] ?? 0;
    }
    $result = $conn->query("SELECT COUNT(*) FROM leads");
    if ($result) {
        $stats['leads'] = $result->fetch_row()[0];
    }
}
// Recent activity
$recent_projects = [];
$recent_bookings = [];
$recent_customers = [];
if ($conn) {
    $result = $conn->query("SELECT id, name, city, status FROM projects ORDER BY id DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) $recent_projects[] = $row;
    }
    $result = $conn->query("SELECT id, customer_id, project_id, booking_date FROM bookings ORDER BY id DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) $recent_bookings[] = $row;
    }
    $result = $conn->query("SELECT id, name, email FROM users ORDER BY id DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) $recent_customers[] = $row;
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
        .dashboard-stats { padding: 20px 0; }
        .stats-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .recent-table { font-size: 0.97rem; }
        .actions-column { width: 120px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Dashboard</h1>
            <a href="add_project.php" class="btn btn-primary">Add New Project</a>
        </div>
        <div class="row dashboard-stats">
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="mb-2"><i class="fas fa-building fa-2x text-primary"></i></div>
                    <div class="fw-bold">Projects</div>
                    <h4><?php echo $stats['projects']; ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="mb-2"><i class="fas fa-calendar-check fa-2x text-success"></i></div>
                    <div class="fw-bold">Bookings</div>
                    <h4><?php echo $stats['bookings']; ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="mb-2"><i class="fas fa-users fa-2x text-info"></i></div>
                    <div class="fw-bold">Customers</div>
                    <h4><?php echo $stats['customers']; ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="mb-2"><i class="fas fa-rupee-sign fa-2x text-warning"></i></div>
                    <div class="fw-bold">Revenue</div>
                    <h4>&#8377;<?php echo number_format($stats['revenue']); ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="mb-2"><i class="fas fa-money-bill-wave fa-2x text-danger"></i></div>
                    <div class="fw-bold">Expenses</div>
                    <h4>&#8377;<?php echo number_format($stats['expenses']); ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="mb-2"><i class="fas fa-bullhorn fa-2x text-secondary"></i></div>
                    <div class="fw-bold">Leads</div>
                    <h4><?php echo $stats['leads']; ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="mb-2"><i class="fas fa-trophy fa-2x text-primary"></i></div>
                    <div class="fw-bold">Total Commission Paid</div>
                    <h4>&#8377;<?php 
                        require_once __DIR__ . '/../includes/config/config.php';
                        $res = $con->query("SELECT SUM(commission_amount) as total FROM mlm_commission_ledger");
                        $row = $res->fetch_assoc();
                        echo number_format($row['total'] ?? 0);
                    ?></h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">Recent Projects</div>
                    <div class="card-body p-0">
                        <table class="table table-sm recent-table mb-0">
                            <thead><tr><th>Name</th><th>City</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach (is_array($recent_projects) ? $recent_projects : [] as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td><?php echo htmlspecialchars($p['city']); ?></td>
                                        <td><span class="badge bg-<?php echo $p['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">Recent Bookings</div>
                    <div class="card-body p-0">
                        <table class="table table-sm recent-table mb-0">
                            <thead><tr><th>Booking ID</th><th>Customer</th><th>Project</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php foreach (is_array($recent_bookings) ? $recent_bookings : [] as $b): ?>
                                    <tr>
                                        <td><?php echo $b['id']; ?></td>
                                        <td><?php echo $b['customer_id']; ?></td>
                                        <td><?php echo $b['project_id']; ?></td>
                                        <td><?php echo $b['booking_date']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">Recent Customers</div>
                    <div class="card-body p-0">
                        <table class="table table-sm recent-table mb-0">
                            <thead><tr><th>Name</th><th>Email</th></tr></thead>
                            <tbody>
                                <?php foreach (is_array($recent_customers) ? $recent_customers : [] as $c): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">Sales Analytics</div>
                    <div class="card-body">
                        <canvas id="salesChart" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header">Reminders</div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">No reminders for today.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Dummy chart for now. Replace with real data as needed.
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Sales',
                data: [12000, 15000, 11000, 18000, 14000, 20000],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    </script>
</body>
</html>
