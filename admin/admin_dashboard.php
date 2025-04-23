<?php
session_start();
include 'config.php';

if (!isset($_SESSION['auser'])) {
    header("Location: login.php");
    exit();
}

// Total Bookings
$totalBookings = $conn->query("SELECT COUNT(*) as cnt FROM bookings")->fetch_assoc()['cnt'];

// Total Sales (Confirmed Bookings)
$totalSales = $conn->query("SELECT SUM(amount) as sum FROM bookings WHERE status='confirmed'")->fetch_assoc()['sum'] ?? 0;

// Current Inventory Status
$inventory = $conn->query("SELECT status, COUNT(*) as cnt FROM plots GROUP BY status");
$inventoryStats = [];
while ($row = $inventory->fetch_assoc()) {
    $inventoryStats[$row['status']] = $row['cnt'];
}

// Total Commission Paid
$totalCommission = $conn->query("SELECT SUM(commission_amount) as sum FROM commission_transactions WHERE status='paid'")->fetch_assoc()['sum'] ?? 0;

// Top Associates
$topAssociates = $conn->query("SELECT a.id, a.name, SUM(c.commission_amount) as total_commission FROM associates a JOIN commission_transactions c ON a.id = c.associate_id WHERE c.status='paid' GROUP BY a.id, a.name ORDER BY total_commission DESC LIMIT 5");

// Recent Activities (last 10 bookings)
$recentBookings = $conn->query("SELECT b.id, c.name as customer, p.id as plot_id, b.amount, b.status, b.booking_date FROM bookings b JOIN customers c ON b.customer_id = c.id JOIN plots p ON b.plot_id = p.id ORDER BY b.booking_date DESC, b.id DESC LIMIT 10");

// Expenses
$totalExpenses = $conn->query("SELECT SUM(amount) as sum FROM expenses")->fetch_assoc()['sum'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">Admin Dashboard</h2>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Bookings</h5>
                    <p class="card-text fs-2"><?= $totalBookings ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <p class="card-text fs-2">₹<?= number_format($totalSales, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Commission Paid</h5>
                    <p class="card-text fs-2">₹<?= number_format($totalCommission, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Expenses</h5>
                    <p class="card-text fs-2">₹<?= number_format($totalExpenses, 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Inventory Status</div>
                <div class="card-body">
                    <canvas id="inventoryChart"></canvas>
                    <script>
                        const inventoryData = {
                            labels: <?= json_encode(array_keys($inventoryStats)) ?>,
                            datasets: [{
                                label: 'Plots',
                                data: <?= json_encode(array_values($inventoryStats)) ?>,
                                backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545']
                            }]
                        };
                        new Chart(document.getElementById('inventoryChart'), {
                            type: 'pie', data: inventoryData
                        });
                    </script>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Top Associates</div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php while($ta = $topAssociates->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= $ta['name'] ?>
                            <span class="badge bg-primary rounded-pill">₹<?= number_format($ta['total_commission'],2) ?></span>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Recent Bookings</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Plot ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <?php while($rb = $recentBookings->fetch_assoc()): ?>
                <tr>
                    <td><?= $rb['id'] ?></td>
                    <td><?= htmlspecialchars($rb['customer']) ?></td>
                    <td><?= $rb['plot_id'] ?></td>
                    <td>₹<?= number_format($rb['amount'],2) ?></td>
                    <td><?= ucfirst($rb['status']) ?></td>
                    <td><?= htmlspecialchars($rb['booking_date']) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>