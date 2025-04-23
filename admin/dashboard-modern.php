<?php
/**
 * Modern Admin Dashboard
 * A redesigned dashboard with modern UI and better organization
 */

include __DIR__ . '/../includes/templates/dynamic_header.php';

// Set page specific variables
$page_title = "Admin Dashboard | APS Dream Homes";
$content_title = "Dashboard Overview";
$meta_description = "Admin dashboard for APS Dream Homes property management system.";

// Get database connection
$con = mysqli_connect("localhost", "root", "", "aps");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get counts for dashboard stats
$property_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM property"))["count"];
$customer_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM customer"))["count"];
$booking_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM booking"))["count"];
$contact_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM contact"))["count"];

// Get recent bookings
$recent_bookings_query = "SELECT b.*, c.name as customer_name, p.title as property_title 
                         FROM booking b 
                         LEFT JOIN customer c ON b.customer_id = c.id 
                         LEFT JOIN property p ON b.property_id = p.id 
                         ORDER BY b.booking_date DESC LIMIT 5";
$recent_bookings_result = mysqli_query($con, $recent_bookings_query);

// Get recent transactions
$recent_transactions_query = "SELECT t.*, c.name as customer_name 
                             FROM transaction t 
                             LEFT JOIN customer c ON t.customer_id = c.id 
                             ORDER BY t.transaction_date DESC LIMIT 5";
$recent_transactions_result = mysqli_query($con, $recent_transactions_query);

// Get recent contacts/inquiries
$recent_contacts_query = "SELECT * FROM contact ORDER BY date DESC LIMIT 5";
$recent_contacts_result = mysqli_query($con, $recent_contacts_query);
?>

<!-- Dashboard Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="stats-card primary">
                <div class="stats-icon">
                    <i class="fas fa-home fa-2x text-primary"></i>
                </div>
                <div class="stats-info ms-3">
                    <h5 class="stats-title">Properties</h5>
                    <h2 class="stats-number"><?php echo $property_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="stats-card success">
                <div class="stats-icon">
                    <i class="fas fa-users fa-2x text-success"></i>
                </div>
                <div class="stats-info ms-3">
                    <h5 class="stats-title">Customers</h5>
                    <h2 class="stats-number"><?php echo $customer_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="stats-card warning">
                <div class="stats-icon">
                    <i class="fas fa-calendar-check fa-2x text-warning"></i>
                </div>
                <div class="stats-info ms-3">
                    <h5 class="stats-title">Bookings</h5>
                    <h2 class="stats-number"><?php echo $booking_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card">
            <div class="stats-card info">
                <div class="stats-icon">
                    <i class="fas fa-envelope fa-2x text-info"></i>
                </div>
                <div class="stats-info ms-3">
                    <h5 class="stats-title">Inquiries</h5>
                    <h2 class="stats-number"><?php echo $contact_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Bookings -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Bookings</h5>
                <a href="<?php echo BASE_URL; ?>/admin/add_booking.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($b = mysqli_fetch_assoc($recent_bookings_result)): ?>
                        <tr>
                            <td><?php echo $b['id']; ?></td>
                            <td><?php echo htmlspecialchars($b['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($b['property_title']); ?></td>
                            <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Recent Transactions -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Transactions</h5>
                <a href="<?php echo BASE_URL; ?>/admin/add_transaction.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($t = mysqli_fetch_assoc($recent_transactions_result)): ?>
                        <tr>
                            <td><?php echo $t['id']; ?></td>
                            <td><?php echo htmlspecialchars($t['customer_name']); ?></td>
                            <td>₹<?php echo number_format($t['amount'],2); ?></td>
                            <td><?php echo htmlspecialchars($t['transaction_date']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>