<?php
/**
 * User Analytics Reports Template
 * Detailed analytics for user behavior and growth
 */
?>

<!-- Reports Header -->
<section class="reports-header py-4 bg-light">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>admin/reports">Reports</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </nav>
                <h2 class="mb-0">
                    <i class="fas fa-users text-success me-2"></i>
                    User Analytics
                </h2>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?php echo BASE_URL; ?>admin/reports/export?type=users&format=csv" class="btn btn-success">
                    <i class="fas fa-file-csv me-2"></i>Export CSV
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Filters -->
<section class="report-filters py-4">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select">
                            <option value="7days" <?php echo ($filters['period'] ?? '') === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="30days" <?php echo ($filters['period'] ?? '') === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90days" <?php echo ($filters['period'] ?? '') === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="1year" <?php echo ($filters['period'] ?? '') === '1year' ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">User Type</label>
                        <select name="user_type" class="form-select">
                            <option value="all">All Users</option>
                            <option value="customer" <?php echo ($filters['user_type'] ?? '') === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="associate" <?php echo ($filters['user_type'] ?? '') === 'associate' ? 'selected' : ''; ?>>Associate</option>
                            <option value="employee" <?php echo ($filters['user_type'] ?? '') === 'employee' ? 'selected' : ''; ?>>Employee</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Registration Source</label>
                        <select name="registration_source" class="form-select">
                            <option value="all">All Sources</option>
                            <option value="website" <?php echo ($filters['registration_source'] ?? '') === 'website' ? 'selected' : ''; ?>>Website</option>
                            <option value="mobile" <?php echo ($filters['registration_source'] ?? '') === 'mobile' ? 'selected' : ''; ?>>Mobile App</option>
                            <option value="admin" <?php echo ($filters['registration_source'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin Panel</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Stats Overview -->
<section class="stats-overview py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Total Users</h6>
                    <h3 class="mb-0 text-primary"><?php echo number_format($user_stats['total_users'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Active Users</h6>
                    <h3 class="mb-0 text-success"><?php echo number_format($user_stats['active_users'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">New Registrations</h6>
                    <h3 class="mb-0 text-info"><?php echo number_format($user_stats['new_users'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Retention Rate</h6>
                    <h3 class="mb-0 text-warning"><?php echo number_format($user_stats['user_retention'] ?? 0, 1); ?>%</h3>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Growth and Activity -->
<section class="growth-activity py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">User Growth Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userGrowthChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">User Activity Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userActivityChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Growth Chart
    const growthCtx = document.getElementById('userGrowthChart').getContext('2d');
    const growthData = <?php echo json_encode($user_growth ?? []); ?>;
    
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: growthData.map(d => d.month),
            datasets: [{
                label: 'New Registrations',
                data: growthData.map(d => d.registrations),
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true
            }, {
                label: 'Active Users',
                data: growthData.map(d => d.active),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Activity Chart
    const activityCtx = document.getElementById('userActivityChart').getContext('2d');
    const activityData = <?php echo json_encode($user_activity ?? []); ?>;
    
    new Chart(activityCtx, {
        type: 'doughnut',
        data: {
            labels: activityData.map(d => d.activity),
            datasets: [{
                data: activityData.map(d => d.count),
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
