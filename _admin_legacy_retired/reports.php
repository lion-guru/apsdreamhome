<?php
require_once __DIR__ . '/includes/new_header.php';

// Check if user has permission to view reports
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Set page title
$page_title = 'Reports & Analytics';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Reports & Analytics</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ul>
        </div>
    </div>
</div>
<!-- /Page Header -->

<div class="row">
    <!-- Property Stats -->
    <div class="col-md-6 col-lg-3">
        <div class="card dash-card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon bg-primary">
                        <i class="fas fa-home"></i>
                    </span>
                    <div class="dash-count">
                        <h3>42</h3>
                        <h6 class="text-muted">Total Properties</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer Stats -->
    <div class="col-md-6 col-lg-3">
        <div class="card dash-card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon bg-success">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="dash-count">
                        <h3>128</h3>
                        <h6 class="text-muted">Total Customers</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking Stats -->
    <div class="col-md-6 col-lg-3">
        <div class="card dash-card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon bg-warning">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                    <div class="dash-count">
                        <h3>56</h3>
                        <h6 class="text-muted">Total Bookings</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue Stats -->
    <div class="col-md-6 col-lg-3">
        <div class="card dash-card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon bg-danger">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <div class="dash-count">
                        <h3>$2.5M</h3>
                        <h6 class="text-muted">Total Revenue</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Monthly Revenue</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Revenue ($)',
            data: [12000, 19000, 15000, 25000, 22000, 30000],
            borderColor: '#4361ee',
            tension: 0.3,
            fill: true,
            backgroundColor: 'rgba(67, 97, 238, 0.1)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<?php include 'includes/new_footer.php'; ?>
