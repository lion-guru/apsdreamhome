<?php
require_once __DIR__ . '/core/init.php';

// Get analytics data
try {
    $total_properties = $conn->query("SELECT COUNT(*) as count FROM properties")->fetch_assoc()['count'] ?? 0;
    $total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'] ?? 0;
    $total_customers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'] ?? 0;
    $total_revenue = $conn->query("SELECT SUM(amount) as total FROM payments")->fetch_assoc()['total'] ?? 0;

    // Get recent bookings
    $recent_bookings = $conn->query("SELECT b.*, c.name as customer_name, p.title as property_title 
                                   FROM bookings b 
                                   LEFT JOIN customers c ON b.customer_id = c.id 
                                   LEFT JOIN properties p ON b.property_id = p.id 
                                   ORDER BY b.created_at DESC LIMIT 5");

    // Get recent properties
    $recent_properties = $conn->query("SELECT p.*, pt.type as property_type 
                                     FROM properties p 
                                     LEFT JOIN property_types pt ON p.type_id = pt.id 
                                     ORDER BY p.created_at DESC LIMIT 5");
} catch (Exception $e) {
    error_log("Dashboard query error: " . $e->getMessage());
    $error_message = "Error loading dashboard data. Please try again later.";
}

// Include header
include 'admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo h($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportDashboardData()">Export</button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Properties</h5>
                            <h2 class="card-text"><?php echo h($total_properties); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Bookings</h5>
                            <h2 class="card-text"><?php echo h($total_bookings); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Customers</h5>
                            <h2 class="card-text"><?php echo h($total_customers); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Revenue</h5>
                            <h2 class="card-text">₹<?php echo h(number_format($total_revenue)); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Bookings</h5>
                            <a href="bookings.php" class="btn btn-sm btn-primary">View All</a>
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
                                        if ($recent_bookings && $recent_bookings->num_rows > 0) {
                                            while ($booking = $recent_bookings->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . h($booking['id']) . "</td>";
                                                echo "<td>" . h($booking['customer_name']) . "</td>";
                                                echo "<td>" . h($booking['property_title']) . "</td>";
                                                echo "<td>" . h($booking['created_at']) . "</td>";
                                                echo "<td><span class='badge bg-" . 
                                                     ($booking['status'] == 'confirmed' ? 'success' : 'warning') . "'>" . 
                                                     h($booking['status']) . "</span></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>No recent bookings</td></tr>";
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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Properties</h5>
                            <a href="properties.php" class="btn btn-sm btn-primary">View All</a>
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
                                        if ($recent_properties && $recent_properties->num_rows > 0) {
                                            while ($property = $recent_properties->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . h($property['id']) . "</td>";
                                                echo "<td>" . h($property['title']) . "</td>";
                                                echo "<td>" . h($property['property_type']) . "</td>";
                                                echo "<td>₹" . h(number_format($property['price'])) . "</td>";
                                                echo "<td><span class='badge bg-" . 
                                                     ($property['status'] == 'available' ? 'success' : 'danger') . "'>" . 
                                                     h($property['status']) . "</span></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>No recent properties</td></tr>";
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
function exportDashboardData() {
    // Implementation for exporting dashboard data
    alert('Export feature coming soon!');
}

// Add any additional JavaScript here
</script>
</body>
</html>
