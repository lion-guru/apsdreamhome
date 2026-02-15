<?php
/**
 * Perfect Admin - Dashboard Content
 * This file is included by admin.php and enhanced_admin_system.php
 */

if (!isset($dashboardData)) {
    $adminService = new PerfectAdminService();
    $dashboardData = $adminService->getDashboardData();
}

$stats = $dashboardData['stats'];
$recentActivity = $dashboardData['recent_activity'];
$chartData = $dashboardData['chart_data'];
?>

<div class="row g-4 mb-4">
    <!-- Quick Stats -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 bg-primary-subtle text-primary p-3 rounded">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle text-muted mb-1">Total Properties</h6>
                        <h3 class="card-title mb-0"><?php echo h($stats['total_properties']); ?></h3>
                    </div>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                </div>
                <div class="mt-2 small text-muted">
                    <span class="text-success"><i class="fas fa-plus me-1"></i><?php echo h($stats['today_properties']); ?></span> today
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 bg-success-subtle text-success p-3 rounded">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle text-muted mb-1">Total Users</h6>
                        <h3 class="card-title mb-0"><?php echo h($stats['total_users']); ?></h3>
                    </div>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                </div>
                <div class="mt-2 small text-muted">
                    <span class="text-success"><i class="fas fa-plus me-1"></i><?php echo h($stats['today_users']); ?></span> today
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 bg-info-subtle text-info p-3 rounded">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle text-muted mb-1">Total Bookings</h6>
                        <h3 class="card-title mb-0"><?php echo h($stats['confirmed_bookings'] + $stats['pending_bookings']); ?></h3>
                    </div>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                </div>
                <div class="mt-2 small text-muted">
                    <span class="text-success"><i class="fas fa-plus me-1"></i><?php echo h($stats['today_bookings']); ?></span> today
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 bg-warning-subtle text-warning p-3 rounded">
                        <i class="fas fa-rupee-sign fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle text-muted mb-1">Monthly Revenue</h6>
                        <h3 class="card-title mb-0">₹<?php echo number_format($stats['monthly_revenue'], 2); ?></h3>
                    </div>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 100%"></div>
                </div>
                <div class="mt-2 small text-muted">
                    Last 30 days revenue
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Charts -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title mb-0">Analytics Overview</h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Last 12 Months
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Last 6 Months</a></li>
                        <li><a class="dropdown-item" href="#">Last 12 Months</a></li>
                        <li><a class="dropdown-item" href="#">All Time</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="analyticsChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 py-3">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($recentActivity)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No recent activity found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="list-group-item border-0 px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <?php 
                                        $icon = 'info-circle';
                                        $color = 'primary';
                                        switch($activity['type']) {
                                            case 'user': $icon = 'user-plus'; $color = 'success'; break;
                                            case 'property': $icon = 'building'; $color = 'info'; break;
                                            case 'booking': $icon = 'calendar-check'; $color = 'warning'; break;
                                        }
                                        ?>
                                        <div class="bg-<?php echo h($color); ?>-subtle text-<?php echo h($color); ?> p-2 rounded">
                                            <i class="fas fa-<?php echo h($icon); ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 text-dark"><?php echo h($activity['name']); ?></h6>
                                            <small class="text-muted"><?php echo h(date('H:i', strtotime($activity['date']))); ?></small>
                                        </div>
                                        <p class="mb-0 small text-muted"><?php echo h($activity['description']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-center py-3">
                <a href="#" class="btn btn-sm btn-link text-decoration-none">View All Activity</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    
    // Prepare data
    const months = <?php echo json_encode(array_column($chartData['properties'], 'month')); ?>;
    const propertyData = <?php echo json_encode(array_column($chartData['properties'], 'count')); ?>;
    const bookingData = <?php echo json_encode(array_column($chartData['bookings'], 'count')); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Properties Added',
                    data: propertyData,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Bookings Made',
                    data: bookingData,
                    borderColor: '#48bb78',
                    backgroundColor: 'rgba(72, 187, 120, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0,0,0,0.05)'
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
});
</script>
