<?php
// Admin Analytics Dashboard - Scaffold
// TODO: Implement charts and analytics for sales, team growth, payouts
// Use standardized admin header
include __DIR__ . '/../includes/templates/dynamic_header.php';
?>
<html>
<body>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="container mt-4">
    <h2>Analytics Dashboard</h2>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Sales</div>
                <div class="card-body">
                    <h5 class="card-title">₹1,25,000</h5>
                    <p class="card-text">This Month</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Active Associates</div>
                <div class="card-body">
                    <h5 class="card-title">120</h5>
                    <p class="card-text">Currently Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Total Payouts</div>
                <div class="card-body">
                    <h5 class="card-title">₹85,000</h5>
                    <p class="card-text">This Month</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Team Growth</div>
                <div class="card-body">
                    <h5 class="card-title">+18</h5>
                    <p class="card-text">New Associates</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Sales Trend (Last 6 Months)</div>
                <div class="card-body" style="min-height:320px; min-width:100%;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Team Growth (Last 6 Months)</div>
                <div class="card-body">
                    <canvas id="teamChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Onboarding/Offboarding Counts</div>
                <div class="card-body">
                    <h5 class="card-title">Onboardings (30d)</h5>
                    <p class="card-text display-6"><?php 
                    session_start();
                    include 'config.php';
                    require_role('Admin');
                    require_permission('view_analytics_dashboard');
                    $onboard_count = $conn->query("SELECT COUNT(*) as c FROM audit_log WHERE action='Onboarding' AND created_at >= NOW() - INTERVAL 30 DAY")->fetch_assoc()['c'];
                    echo $onboard_count; ?></p>
                    <h5 class="card-title">Offboardings (30d)</h5>
                    <p class="card-text display-6"><?php 
                    $offboard_count = $conn->query("SELECT COUNT(*) as c FROM audit_log WHERE action='Offboarding' AND created_at >= NOW() - INTERVAL 30 DAY")->fetch_assoc()['c'];
                    echo $offboard_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Top Permission Usage</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Usage Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $perm_usage = $conn->query("SELECT p.action, COUNT(al.id) as usage_count FROM audit_log al JOIN permissions p ON al.details LIKE CONCAT('%', p.action, '%') GROUP BY p.action ORDER BY usage_count DESC LIMIT 10");
                            while($row = $perm_usage->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['action']) ?></td>
                                <td><?= $row['usage_count'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">Permission Denials Trend (Last 30 Days)</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Denials</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $denials = $conn->query("SELECT DATE(created_at) as d, COUNT(*) as c FROM audit_log WHERE action='Permission Denied' AND created_at >= NOW() - INTERVAL 30 DAY GROUP BY d ORDER BY d DESC");
                            while($row = $denials->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['d']) ?></td>
                                <td><?= $row['c'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Dummy data for charts
const salesData = [20000, 25000, 22000, 30000, 28000, 35000];
const teamData = [10, 12, 14, 16, 17, 18];
const months = ['Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'];

const salesChart = new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Sales (₹)',
            data: salesData,
            fill: true,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true }
        }
    }
});

const teamChart = new Chart(document.getElementById('teamChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'New Associates',
            data: teamData,
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true }
        }
    }
});
</script>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
