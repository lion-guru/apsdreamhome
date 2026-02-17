<?php
require_once __DIR__ . '/core/init.php';

// Get analytics data
try {
    $db = \App\Core\App::database();
    $total_properties = $db->fetchOne("SELECT COUNT(*) as count FROM properties")['count'] ?? 0;
    $total_bookings = $db->fetchOne("SELECT COUNT(*) as count FROM bookings")['count'] ?? 0;
    $total_customers = $db->fetchOne("SELECT COUNT(*) as count FROM customers")['count'] ?? 0;
    $total_revenue = $db->fetchOne("SELECT SUM(amount) as total FROM payments")['total'] ?? 0;

    // Get recent bookings
    $recent_bookings = $db->fetchAll("SELECT b.*, c.name as customer_name, p.title as property_title 
                                   FROM bookings b 
                                   LEFT JOIN customers c ON b.customer_id = c.id 
                                   LEFT JOIN properties p ON b.property_id = p.id 
                                   ORDER BY b.created_at DESC LIMIT 5");

    // Get recent properties
    $recent_properties = $db->fetchAll("SELECT p.*, pt.type as property_type 
                                     FROM properties p 
                                     LEFT JOIN property_types pt ON p.type_id = pt.id 
                                     ORDER BY p.created_at DESC LIMIT 5");
} catch (Exception $e) {
    error_log("Dashboard query error: " . $e->getMessage());
    $error_message = $mlSupport->translate("Error loading dashboard data. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mlSupport->translate('Admin Dashboard'); ?> - APS Dream Home</title>
    <link rel="stylesheet" href="<?= get_admin_asset_url('chart.min.css', 'css') ?>">
</head>
<body class="admin-dashboard">
    <?php include 'admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>

            <main class="page-wrapper">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3 border-0 shadow-sm" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo h($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo $mlSupport->translate('Dashboard'); ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportDashboardData()">
                                <i class="fas fa-download me-1"></i> <?php echo $mlSupport->translate('Export'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 opacity-75"><?php echo $mlSupport->translate('Properties'); ?></h6>
                                <h2 class="card-text fw-bold mb-0"><?php echo h($total_properties); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 opacity-75"><?php echo $mlSupport->translate('Bookings'); ?></h6>
                                <h2 class="card-text fw-bold mb-0"><?php echo h($total_bookings); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 opacity-75"><?php echo $mlSupport->translate('Customers'); ?></h6>
                                <h2 class="card-text fw-bold mb-0"><?php echo h($total_customers); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 opacity-75"><?php echo $mlSupport->translate('Revenue'); ?></h6>
                                <h2 class="card-text fw-bold mb-0">₹<?php echo h(number_format($total_revenue)); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row mb-4 g-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Recent Bookings'); ?></h5>
                                <a href="bookings.php" class="btn btn-sm btn-link text-primary text-decoration-none">
                                    <?php echo $mlSupport->translate('View All'); ?> <i class="fas fa-chevron-right ms-1 small"></i>
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3"><?php echo $mlSupport->translate('ID'); ?></th>
                                                <th><?php echo $mlSupport->translate('Customer'); ?></th>
                                                <th><?php echo $mlSupport->translate('Property'); ?></th>
                                                <th><?php echo $mlSupport->translate('Date'); ?></th>
                                                <th class="pe-3"><?php echo $mlSupport->translate('Status'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (!empty($recent_bookings)) {
                                                foreach ($recent_bookings as $booking) {
                                                    echo "<tr>";
                                                    echo "<td class='ps-3'>" . h($booking['id']) . "</td>";
                                                    echo "<td>" . h($booking['customer_name']) . "</td>";
                                                    echo "<td>" . h($booking['property_title']) . "</td>";
                                                    echo "<td><small class='text-muted'>" . h($booking['created_at']) . "</small></td>";
                                                    echo "<td class='pe-3'><span class='badge rounded-pill bg-" . 
                                                         ($booking['status'] == 'confirmed' ? 'success' : 'warning') . "'>" . 
                                                         h($mlSupport->translate(ucfirst($booking['status']))) . "</span></td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>" . $mlSupport->translate('No recent bookings') . "</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Recent Properties'); ?></h5>
                                <a href="properties.php" class="btn btn-sm btn-link text-primary text-decoration-none">
                                    <?php echo $mlSupport->translate('View All'); ?> <i class="fas fa-chevron-right ms-1 small"></i>
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3"><?php echo $mlSupport->translate('ID'); ?></th>
                                                <th><?php echo $mlSupport->translate('Title'); ?></th>
                                                <th><?php echo $mlSupport->translate('Type'); ?></th>
                                                <th><?php echo $mlSupport->translate('Price'); ?></th>
                                                <th class="pe-3"><?php echo $mlSupport->translate('Status'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (!empty($recent_properties)) {
                                                foreach ($recent_properties as $property) {
                                                    echo "<tr>";
                                                    echo "<td class='ps-3'>" . h($property['id']) . "</td>";
                                                    echo "<td>" . h($property['title']) . "</td>";
                                                    echo "<td><span class='badge bg-light text-dark border'>" . h($property['property_type']) . "</span></td>";
                                                    echo "<td>₹" . h(number_format($property['price'])) . "</td>";
                                                    echo "<td class='pe-3'><span class='badge rounded-pill bg-" . 
                                                         ($property['status'] == 'available' ? 'success' : 'danger') . "'>" . 
                                                         h($mlSupport->translate(ucfirst($property['status']))) . "</span></td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>" . $mlSupport->translate('No recent properties') . "</td></tr>";
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

    <?php require_once __DIR__ . '/admin_footer.php'; ?>
    <script src="<?= get_admin_asset_url('chart.min.js', 'js') ?>"></script>
    <script>
    function exportDashboardData() {
        alert('<?php echo $mlSupport->translate("Export feature coming soon!"); ?>');
    }
    </script>
</body>
</html>


