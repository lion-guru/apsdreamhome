<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

// Fixed authentication check to match the current login system
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Check if database connection exists
if (!isset($con) || !$con) {
    die('Database connection failed. Please check your configuration.');
}

// Use the correct connection variable from config.php
$conn = $con;

// Total Bookings
try {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM bookings");
    $totalBookings = $result ? $result->fetch(PDO::FETCH_ASSOC)['cnt'] : 0;
} catch (Exception $e) {
    $totalBookings = 0;
}

// Total Sales (Confirmed Bookings)
try {
    $result = $conn->query("SELECT SUM(amount) as sum FROM bookings WHERE status='confirmed'");
    $totalSales = $result ? ($result->fetch(PDO::FETCH_ASSOC)['sum'] ?? 0) : 0;
} catch (Exception $e) {
    $totalSales = 0;
}

// Current Inventory Status
try {
    $inventory = $conn->query("SELECT status, COUNT(*) as cnt FROM plots GROUP BY status");
    $inventoryStats = [];
    if ($inventory) {
        while ($row = $inventory->fetch(PDO::FETCH_ASSOC)) {
            $inventoryStats[$row['status']] = $row['cnt'];
        }
    }
} catch (Exception $e) {
    $inventoryStats = ['available' => 0, 'sold' => 0, 'booked' => 0];
}

// Total Commission Paid
try {
    $result = $conn->query("SELECT SUM(commission_amount) as sum FROM commission_transactions WHERE status='paid'");
    $totalCommission = $result ? ($result->fetch(PDO::FETCH_ASSOC)['sum'] ?? 0) : 0;
} catch (Exception $e) {
    $totalCommission = 0;
}

// Total Expenses
try {
    $result = $conn->query("SELECT SUM(amount) as sum FROM expenses");
    $totalExpenses = $result ? ($result->fetch(PDO::FETCH_ASSOC)['sum'] ?? 0) : 0;
} catch (Exception $e) {
    $totalExpenses = 0;
}

// Recent Bookings for activities
try {
    $recentBookings = $conn->query("SELECT b.id, COALESCE(c.name, 'Unknown Customer') as customer, COALESCE(b.plot_id, b.property_id) as plot_id, COALESCE(b.amount, 0) as amount, b.status, b.booking_date FROM bookings b LEFT JOIN customers c ON b.customer_id = c.id ORDER BY b.booking_date DESC, b.id DESC LIMIT 5");
} catch (Exception $e) {
    $recentBookings = null;
}

// Statistics for dashboard
$stats = [
    [
        'icon' => 'fas fa-calendar-check',
        'value' => $totalBookings,
        'label' => 'Total Bookings',
        'change' => '+15 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-rupee-sign',
        'value' => '₹' . number_format($totalSales, 0),
        'label' => 'Total Sales',
        'change' => '+22% from last month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-hand-holding-usd',
        'value' => '₹' . number_format($totalCommission, 0),
        'label' => 'Commission Paid',
        'change' => '+8% this quarter',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-receipt',
        'value' => '₹' . number_format($totalExpenses, 0),
        'label' => 'Total Expenses',
        'change' => '-5% this month',
        'change_type' => 'positive'
    ]
];

// Quick actions for admin
$quick_actions = [
    [
        'title' => 'Add Booking',
        'icon' => 'fas fa-plus',
        'url' => 'bookings.php?action=add',
        'color' => 'primary'
    ],
    [
        'title' => 'Manage Properties',
        'icon' => 'fas fa-building',
        'url' => 'properties.php',
        'color' => 'success'
    ],
    [
        'title' => 'View Reports',
        'icon' => 'fas fa-chart-bar',
        'url' => 'reports.php',
        'color' => 'info'
    ],
    [
        'title' => 'System Settings',
        'icon' => 'fas fa-cog',
        'url' => 'settings.php',
        'color' => 'warning'
    ]
];

// Recent activities
$recent_activities = [];
if ($recentBookings) {
    while($booking = $recentBookings->fetch(PDO::FETCH_ASSOC)) {
        $recent_activities[] = [
            'title' => 'New Booking - ' . ucfirst($booking['status']),
            'description' => $booking['customer'] . ' - Plot #' . $booking['plot_id'] . ' (₹' . number_format($booking['amount']) . ')',
            'time' => date('M j, Y', strtotime($booking['booking_date'])),
            'icon' => $booking['status'] == 'confirmed' ? 'fas fa-check-circle text-success' : 'fas fa-clock text-warning'
        ];
    }
}

echo generateUniversalDashboard('admin', $stats, $quick_actions, $recent_activities); 
                if ($recentBookings) {
                    while($rb = $recentBookings->fetch(PDO::FETCH_ASSOC)): 
                ?>
                <tr>
                    <td><?= $rb['id'] ?></td>
                    <td><?= htmlspecialchars($rb['customer']) ?></td>
                    <td><?= $rb['plot_id'] ?? 'N/A' ?></td>
                    <td>₹<?= number_format($rb['amount'],2) ?></td>
                    <td><?= ucfirst($rb['status']) ?></td>
                    <td><?= htmlspecialchars($rb['booking_date']) ?></td>
                </tr>
                <?php 
                    endwhile;
                } else {
                    echo '<tr><td colspan="6" class="text-center text-muted">No booking data available</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>