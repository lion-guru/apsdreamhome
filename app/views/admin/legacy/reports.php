<?php
/**
 * Admin Reports Dashboard Template
 * Main reports and analytics dashboard for administrators
 */

?>

<!-- Reports Header -->
<section class="reports-header py-4 bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Reports & Analytics
                        </h2>
                        <p class="text-muted mb-0">Comprehensive insights into your real estate business performance</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar me-2"></i>Period: Last 30 Days
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?period=7days">Last 7 Days</a></li>
                                <li><a class="dropdown-item" href="?period=30days">Last 30 Days</a></li>
                                <li><a class="dropdown-item" href="?period=90days">Last 90 Days</a></li>
                                <li><a class="dropdown-item" href="?period=1year">Last Year</a></li>
                            </ul>
                        </div>
                        <a href="<?php echo BASE_URL; ?>admin/reports/export?type=overview&format=csv" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Export Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Overview Cards -->
<section class="overview-cards py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Total Properties -->
            <div class="col-xl-3 col-lg-6">
                <div class="card overview-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Total Properties</h6>
                                <h3 class="mb-0"><?php echo number_format($overview_stats['total_properties'] ?? 0); ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    +12% from last month
                                </small>
                            </div>
                            <div class="overview-icon">
                                <i class="fas fa-home text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="col-xl-3 col-lg-6">
                <div class="card overview-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Total Users</h6>
                                <h3 class="mb-0"><?php echo number_format($overview_stats['total_users'] ?? 0); ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    +8% from last month
                                </small>
                            </div>
                            <div class="overview-icon">
                                <i class="fas fa-users text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Inquiries -->
            <div class="col-xl-3 col-lg-6">
                <div class="card overview-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Total Inquiries</h6>
                                <h3 class="mb-0"><?php echo number_format($overview_stats['total_inquiries'] ?? 0); ?></h3>
                                <small class="text-warning">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    +25% from last month
                                </small>
                            </div>
                            <div class="overview-icon">
                                <i class="fas fa-envelope text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Favorites -->
            <div class="col-xl-3 col-lg-6">
                <div class="card overview-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Total Favorites</h6>
                                <h3 class="mb-0"><?php echo number_format($overview_stats['total_favorites'] ?? 0); ?></h3>
                                <small class="text-info">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    +18% from last month
                                </small>
                            </div>
                            <div class="overview-icon">
                                <i class="fas fa-heart text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Stats Row -->
<section class="quick-stats py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Revenue Card -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-rupee-sign text-success me-2"></i>
                            Monthly Revenue
                        </h6>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-2">₹<?php echo number_format($overview_stats['monthly_revenue'] ?? 0); ?></h4>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Target: ₹2,00,000</small>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                +15%
                            </small>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-success" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversion Rate -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-exchange-alt text-info me-2"></i>
                            Conversion Rate
                        </h6>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-2"><?php echo number_format($overview_stats['conversion_rate'] ?? 0, 1); ?>%</h4>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Industry Avg: 3.2%</small>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                +0.5%
                            </small>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-info" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-user-check text-primary me-2"></i>
                            Active Users (30d)
                        </h6>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-2"><?php echo number_format($user_metrics['active_users'] ?? 0); ?></h4>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Total: <?php echo number_format($user_metrics['new_registrations'] ?? 0); ?> new</small>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                +12%
                            </small>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-primary" style="width: 68%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Charts Section -->
<section class="charts-section py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- User Growth Chart -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            User Growth Trend
                        </h6>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active">7D</button>
                            <button class="btn btn-outline-primary">30D</button>
                            <button class="btn btn-outline-primary">90D</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="userGrowthChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Properties -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-trophy text-warning me-2"></i>
                            Top Performing Properties
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (!empty($top_properties)): ?>
                                <?php foreach ($top_properties as $index => $property): ?>
                                    <div class="list-group-item d-flex align-items-center">
                                        <div class="me-3">
                                            <span class="badge bg-primary rounded-circle">#<?php echo $index + 1; ?></span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($property['title']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo $property['views'] ?? 0; ?> views • <?php echo $property['favorites'] ?? 0; ?> favorites
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-success fw-bold">+<?php echo $property['inquiries'] ?? 0; ?></small>
                                            <br>
                                            <small class="text-muted">inquiries</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-center py-4">
                                    <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                                    <p class="mb-0 text-muted">No data available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <!-- Property Performance -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-home text-info me-2"></i>
                            Property Performance
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="propertyPerformanceChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Inquiry Trends -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-envelope text-warning me-2"></i>
                            Inquiry Trends
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="inquiryTrendsChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Activities -->
<section class="recent-activities py-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-clock text-secondary me-2"></i>
                            Recent Activities
                        </h6>
                        <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="timeline">
                            <?php if (!empty($recent_activities)): ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-<?php echo $this->getActivityColor($activity['type']); ?>"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                            <p class="text-muted mb-1">by <?php echo htmlspecialchars($activity['user']); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo htmlspecialchars($activity['time']); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="mb-0 text-muted">No recent activities</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Actions -->
<section class="quick-actions py-4 bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h5 class="mb-3">
                    <i class="fas fa-bolt text-primary me-2"></i>
                    Quick Actions
                </h5>
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a href="<?php echo BASE_URL; ?>admin/reports/properties" class="card quick-action-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-home fa-2x text-primary mb-2"></i>
                                <h6>Property Reports</h6>
                                <small class="text-muted">Detailed property performance analytics</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="<?php echo BASE_URL; ?>admin/reports/users" class="card quick-action-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-success mb-2"></i>
                                <h6>User Analytics</h6>
                                <small class="text-muted">User behavior and engagement metrics</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="<?php echo BASE_URL; ?>admin/reports/financial" class="card quick-action-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-rupee-sign fa-2x text-warning mb-2"></i>
                                <h6>Financial Reports</h6>
                                <small class="text-muted">Revenue, commissions, and profit analysis</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="<?php echo BASE_URL; ?>admin/reports/inquiries" class="card quick-action-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-2x text-info mb-2"></i>
                                <h6>Inquiry Analytics</h6>
                                <small class="text-muted">Inquiry trends and response performance</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Chart.js initialization (would be loaded from CDN in production)
document.addEventListener('DOMContentLoaded', function() {
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart');
    if (userGrowthCtx) {
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Users',
                    data: [65, 78, 90, 81, 95, 102],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Active Users',
                    data: [45, 52, 68, 72, 78, 85],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Property Performance Chart
    const propertyCtx = document.getElementById('propertyPerformanceChart');
    if (propertyCtx) {
        new Chart(propertyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Sold', 'Rented', 'Pending'],
                datasets: [{
                    data: [45, 12, 8, 5],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Inquiry Trends Chart
    const inquiryCtx = document.getElementById('inquiryTrendsChart');
    if (inquiryCtx) {
        new Chart(inquiryCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Inquiries',
                    data: [12, 19, 15, 25, 22, 18, 14],
                    backgroundColor: 'rgba(255, 193, 7, 0.8)'
                }, {
                    label: 'Responses',
                    data: [10, 16, 12, 20, 18, 15, 12],
                    backgroundColor: 'rgba(40, 167, 69, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});

// Helper function for activity colors
function getActivityColor(type) {
    const colors = {
        'inquiry': 'warning',
        'favorite': 'danger',
        'property': 'info',
        'user': 'success'
    };
    return colors[type] || 'secondary';
}
</script>

<style>
.overview-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.overview-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.overview-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.quick-action-card {
    border: 2px solid transparent;
    transition: all 0.3s ease;
    text-decoration: none !important;
}

.quick-action-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
}

.timeline {
    position: relative;
    padding: 1rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding: 1rem 0 1rem 60px;
    border-bottom: 1px solid #e9ecef;
}

.timeline-item:last-child {
    border-bottom: none;
}

.timeline-marker {
    position: absolute;
    left: 24px;
    top: 1.2rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid white;
    z-index: 1;
}

.timeline-content {
    background: white;
    padding: 0.5rem 0;
}

@media (max-width: 768px) {
    .reports-header .d-flex {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch !important;
    }

    .overview-cards .col-xl-3 {
        margin-bottom: 1rem;
    }
}
</style>
