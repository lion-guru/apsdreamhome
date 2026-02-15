<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session']['is_authenticated'] !== true) {
    header('Location: index.php');
    exit();
}

// Include database connection
require_once __DIR__ . '/../includes/config/db_config.php';

// Get analytics data
$total_properties = $conn->query("SELECT COUNT(*) as count FROM properties")->fetch_assoc()['count'] ?? 0;
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'] ?? 0;
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'] ?? 0;
$total_revenue = $conn->query("SELECT SUM(amount) as total FROM payments")->fetch_assoc()['total'] ?? 0;

// Include header
include 'admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Properties</h5>
                            <h2 class="card-text"><?php echo $total_properties; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Bookings</h5>
                            <h2 class="card-text"><?php echo $total_bookings; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Customers</h5>
                            <h2 class="card-text"><?php echo $total_customers; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Revenue</h5>
                            <h2 class="card-text">₹<?php echo number_format($total_revenue); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Bookings</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Property</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent_bookings = $conn->query("SELECT b.*, c.name as customer_name, p.title as property_title 
                                                                       FROM bookings b 
                                                                       LEFT JOIN customers c ON b.customer_id = c.id 
                                                                       LEFT JOIN properties p ON b.property_id = p.id 
                                                                       ORDER BY b.created_at DESC LIMIT 5");
                                        if ($recent_bookings) {
                                            while ($booking = $recent_bookings->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>{$booking['id']}</td>";
                                                echo "<td>{$booking['customer_name']}</td>";
                                                echo "<td>{$booking['property_title']}</td>";
                                                echo "<td>{$booking['created_at']}</td>";
                                                echo "<td><span class='badge bg-" . ($booking['status'] == 'confirmed' ? 'success' : 'warning') . "'>{$booking['status']}</span></td>";
                                                echo "</tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Properties</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Type</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent_properties = $conn->query("SELECT p.*, pt.type as property_type 
                                                                         FROM properties p 
                                                                         LEFT JOIN property_types pt ON p.type_id = pt.id 
                                                                         ORDER BY p.created_at DESC LIMIT 5");
                                        if ($recent_properties) {
                                            while ($property = $recent_properties->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>{$property['id']}</td>";
                                                echo "<td>{$property['title']}</td>";
                                                echo "<td>{$property['property_type']}</td>";
                                                echo "<td>₹" . number_format($property['price']) . "</td>";
                                                echo "<td><span class='badge bg-" . ($property['status'] == 'available' ? 'success' : 'danger') . "'>{$property['status']}</span></td>";
                                                echo "</tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom scripts -->
<script>
// Add any custom JavaScript here
</script>
</body>
</html>
