<?php
/**
 * Reports & Analytics - Perfect Admin
 */

$reportType = $_GET['type'] ?? 'general';
$period = $_GET['period'] ?? '30days';
$reportData = $adminService->getReportData($reportType, $period);
?>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Reports & Analytics</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm w-auto" id="reportPeriod" onchange="window.location.href='?action=reports&type=<?php echo h($reportType); ?>&period='+this.value">
                            <option value="30days" <?php echo $period === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90days" <?php echo $period === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                </div>

                <ul class="nav nav-pills mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === 'general' ? 'active' : ''; ?>" href="?action=reports&type=general">General Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === 'revenue' ? 'active' : ''; ?>" href="?action=reports&type=revenue">Revenue Analysis</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === 'users' ? 'active' : ''; ?>" href="?action=reports&type=users">User Growth</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $reportType === 'properties' ? 'active' : ''; ?>" href="?action=reports&type=properties">Property Insights</a>
                    </li>
                </ul>

                <?php if ($reportType === 'general'): ?>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="p-3 border rounded text-center">
                                <div class="text-muted small mb-1">Total Properties</div>
                                <h4 class="mb-0"><?php echo h($reportData['stats']['total_properties']); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded text-center">
                                <div class="text-muted small mb-1">Total Users</div>
                                <h4 class="mb-0"><?php echo h($reportData['stats']['total_users']); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded text-center">
                                <div class="text-muted small mb-1">Pending Bookings</div>
                                <h4 class="mb-0"><?php echo h($reportData['stats']['pending_bookings']); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded text-center">
                                <div class="text-muted small mb-1">Monthly Revenue</div>
                                <h4 class="mb-0">₹<?php echo number_format($reportData['stats']['monthly_revenue']); ?></h4>
                            </div>
                        </div>
                    </div>
                <?php elseif ($reportType === 'revenue'): ?>
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6>Revenue Over Time</h6>
                            <div style="height: 300px;">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                <?php elseif ($reportType === 'users'): ?>
                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6>User Registrations</h6>
                                    <div style="height: 300px;">
                                        <canvas id="userGrowthChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6>Role Distribution</h6>
                                    <div style="height: 300px;">
                                        <canvas id="roleDistChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($reportType === 'properties'): ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6>Property Types</h6>
                                    <div style="height: 300px;">
                                        <canvas id="propTypeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6>Property Status</h6>
                                    <div style="height: 300px;">
                                        <canvas id="propStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($reportType === 'revenue'): ?>
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($reportData['revenue_over_time'], 'date')); ?>,
            datasets: [{
                label: 'Revenue (₹)',
                data: <?php echo json_encode(array_column($reportData['revenue_over_time'], 'total')); ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
    <?php elseif ($reportType === 'users'): ?>
    new Chart(document.getElementById('userGrowthChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($reportData['user_registrations'], 'date')); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode(array_column($reportData['user_registrations'], 'count')); ?>,
                backgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    new Chart(document.getElementById('roleDistChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($reportData['role_distribution'], 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($reportData['role_distribution'], 'count')); ?>,
                backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#48bb78']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    <?php elseif ($reportType === 'properties'): ?>
    new Chart(document.getElementById('propTypeChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($reportData['property_types'], 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($reportData['property_types'], 'count')); ?>,
                backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#48bb78', '#4299e1']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    new Chart(document.getElementById('propStatusChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($reportData['property_status'], 'status')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($reportData['property_status'], 'count')); ?>,
                backgroundColor: ['#48bb78', '#f56565', '#ed8936']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    <?php endif; ?>
});
</script>
