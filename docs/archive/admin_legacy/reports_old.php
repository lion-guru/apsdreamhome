<?php include '../app/views/includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar">
                <div class="sidebar-header">
                    <h5><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</h5>
                </div>
                <nav class="nav nav-pills flex-column">
                    <a href="/admin" class="nav-link">Dashboard</a>
                    <a href="/admin/properties" class="nav-link">Properties</a>
                    <a href="/admin/leads" class="nav-link">Leads</a>
                    <a href="/admin/users" class="nav-link">Users</a>
                    <a href="/admin/reports" class="nav-link active">Reports</a>
                    <a href="/admin/settings" class="nav-link">Settings</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="admin-content">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2>Reports & Analytics</h2>
                                <p class="text-muted">Comprehensive business insights and analytics</p>
                            </div>
                            <div>
                                <button class="btn btn-primary" onclick="exportReport()">
                                    <i class="fas fa-download me-2"></i>Export Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="report-filters">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select" name="type">
                                        <option value="overview" <?php echo ($reportType ?? '') === 'overview' ? 'selected' : ''; ?>>Overview</option>
                                        <option value="sales" <?php echo ($reportType ?? '') === 'sales' ? 'selected' : ''; ?>>Sales Report</option>
                                        <option value="leads" <?php echo ($reportType ?? '') === 'leads' ? 'selected' : ''; ?>>Lead Analytics</option>
                                        <option value="properties" <?php echo ($reportType ?? '') === 'properties' ? 'selected' : ''; ?>>Property Performance</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($dateRange['start'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($dateRange['end'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-2"></i>Generate Report
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                        <i class="fas fa-times me-2"></i>Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Report Content -->
                <div class="row">
                    <div class="col-12">
                        <div class="report-content">
                            <?php if (isset($report) && !empty($report)): ?>
                                <!-- Overview Report -->
                                <?php if ($reportType === 'overview'): ?>
                                <div class="overview-report">
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="metric-card">
                                                <div class="metric-icon">
                                                    <i class="fas fa-home"></i>
                                                </div>
                                                <div class="metric-info">
                                                    <h3><?php echo $report['total_properties'] ?? 0; ?></h3>
                                                    <p>Total Properties</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="metric-card">
                                                <div class="metric-icon">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <div class="metric-info">
                                                    <h3><?php echo $report['total_leads'] ?? 0; ?></h3>
                                                    <p>Total Leads</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="metric-card">
                                                <div class="metric-icon">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </div>
                                                <div class="metric-info">
                                                    <h3><?php echo $report['converted_leads'] ?? 0; ?></h3>
                                                    <p>Conversions</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="metric-card">
                                                <div class="metric-icon">
                                                    <i class="fas fa-chart-line"></i>
                                                </div>
                                                <div class="metric-info">
                                                    <h3><?php echo number_format(($report['conversion_rate'] ?? 0) * 100, 1); ?>%</h3>
                                                    <p>Conversion Rate</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="chart-container">
                                                <canvas id="overviewChart"></canvas>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="summary-stats">
                                                <h5>Key Insights</h5>
                                                <div class="insight-item">
                                                    <span class="insight-label">Best Performing Source:</span>
                                                    <span class="insight-value">Website Referrals</span>
                                                </div>
                                                <div class="insight-item">
                                                    <span class="insight-label">Top Property Type:</span>
                                                    <span class="insight-value">Apartments</span>
                                                </div>
                                                <div class="insight-item">
                                                    <span class="insight-label">Peak Month:</span>
                                                    <span class="insight-value">March 2024</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sales Report -->
                                <?php elseif ($reportType === 'sales'): ?>
                                <div class="sales-report">
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="metric-card">
                                                <h4>Total Sales</h4>
                                                <h2>₹<?php echo number_format($report['total_sales'] ?? 0); ?></h2>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="metric-card">
                                                <h4>Properties Sold</h4>
                                                <h2><?php echo $report['properties_sold'] ?? 0; ?></h2>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="metric-card">
                                                <h4>Average Sale Price</h4>
                                                <h2>₹<?php echo number_format($report['avg_sale_price'] ?? 0); ?></h2>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="chart-container">
                                        <canvas id="salesChart"></canvas>
                                    </div>
                                </div>

                                <!-- Lead Analytics -->
                                <?php elseif ($reportType === 'leads'): ?>
                                <div class="leads-report">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="chart-container">
                                                <canvas id="leadStatusChart"></canvas>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="chart-container">
                                                <canvas id="leadSourceChart"></canvas>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="lead-stats-table">
                                        <h5>Lead Statistics</h5>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Metric</th>
                                                        <th>Value</th>
                                                        <th>Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Total Leads</td>
                                                        <td><?php echo $report['total_leads'] ?? 0; ?></td>
                                                        <td><span class="text-success">+12%</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Qualified Leads</td>
                                                        <td><?php echo $report['qualified_leads'] ?? 0; ?></td>
                                                        <td><span class="text-success">+8%</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Conversion Rate</td>
                                                        <td><?php echo number_format(($report['conversion_rate'] ?? 0) * 100, 1); ?>%</td>
                                                        <td><span class="text-danger">-2%</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Property Performance -->
                                <?php elseif ($reportType === 'properties'): ?>
                                <div class="properties-report">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="chart-container">
                                                <canvas id="propertyTypeChart"></canvas>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="chart-container">
                                                <canvas id="propertyStatusChart"></canvas>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="property-performance-table">
                                        <h5>Property Performance</h5>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Property Type</th>
                                                        <th>Total</th>
                                                        <th>Available</th>
                                                        <th>Sold</th>
                                                        <th>Avg. Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (isset($report['property_stats'])): ?>
                                                        <?php foreach ($report['property_stats'] as $stat): ?>
                                                        <tr>
                                                            <td><?php echo ucfirst($stat['type']); ?></td>
                                                            <td><?php echo $stat['total']; ?></td>
                                                            <td><?php echo $stat['available']; ?></td>
                                                            <td><?php echo $stat['sold']; ?></td>
                                                            <td>₹<?php echo number_format($stat['avg_price']); ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-data text-center py-5">
                                    <i class="fas fa-chart-bar fa-4x text-muted mb-4"></i>
                                    <h4>No Report Data</h4>
                                    <p class="text-muted">Select report type and date range to generate reports.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js for reports -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Initialize charts based on report type
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($reportType === 'overview' && isset($report)): ?>
    initializeOverviewChart();
    <?php elseif ($reportType === 'sales' && isset($report)): ?>
    initializeSalesChart();
    <?php elseif ($reportType === 'leads' && isset($report)): ?>
    initializeLeadCharts();
    <?php elseif ($reportType === 'properties' && isset($report)): ?>
    initializePropertyCharts();
    <?php endif; ?>
});

// Overview chart
function initializeOverviewChart() {
    const ctx = document.getElementById('overviewChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Leads Generated',
                data: [65, 78, 90, 81, 95, 102],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }, {
                label: 'Properties Sold',
                data: [12, 15, 18, 14, 20, 25],
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

// Sales chart
function initializeSalesChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            datasets: [{
                label: 'Sales Revenue',
                data: [2500000, 3200000, 2800000, 3500000],
                backgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Lead charts
function initializeLeadCharts() {
    // Status distribution
    const statusCtx = document.getElementById('leadStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['New', 'Contacted', 'Qualified', 'Converted'],
            datasets: [{
                data: [45, 30, 15, 10],
                backgroundColor: ['#ffc107', '#17a2b8', '#fd7e14', '#28a745']
            }]
        }
    });

    // Source distribution
    const sourceCtx = document.getElementById('leadSourceChart').getContext('2d');
    new Chart(sourceCtx, {
        type: 'pie',
        data: {
            labels: ['Website', 'Phone', 'Email', 'Referral'],
            datasets: [{
                data: [60, 25, 10, 5],
                backgroundColor: ['#667eea', '#28a745', '#ffc107', '#dc3545']
            }]
        }
    });
}

// Property charts
function initializePropertyCharts() {
    // Type distribution
    const typeCtx = document.getElementById('propertyTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Apartments', 'Villas', 'Commercial', 'Plots'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: ['#667eea', '#28a745', '#ffc107', '#dc3545']
            }]
        }
    });

    // Status distribution
    const statusCtx = document.getElementById('propertyStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Available', 'Sold', 'Rented'],
            datasets: [{
                data: [70, 25, 5],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107']
            }]
        }
    });
}

// Export report
function exportReport() {
    const reportType = document.querySelector('select[name="type"]').value;
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;

    const url = `/admin/reports/export?type=${reportType}&start_date=${startDate}&end_date=${endDate}`;
    window.open(url, '_blank');
}

// Reset filters
function resetFilters() {
    document.querySelector('select[name="type"]').value = 'overview';
    document.querySelector('input[name="start_date"]').value = '<?php echo date('Y-m-d', strtotime('-30 days')); ?>';
    document.querySelector('input[name="end_date"]').value = '<?php echo date('Y-m-d'); ?>';
    document.querySelector('form').submit();
}
</script>

<style>
.admin-sidebar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.admin-content {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
}

.report-filters {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.metric-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
}

.metric-icon {
    margin-bottom: 10px;
    font-size: 2rem;
}

.chart-container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: 300px;
    margin-bottom: 20px;
}

.summary-stats, .lead-stats-table, .property-performance-table {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.insight-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.insight-item:last-child {
    border-bottom: none;
}

.no-data {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 60px 20px;
}

@media (max-width: 768px) {
    .admin-sidebar {
        margin-bottom: 20px;
    }
}
</style>

<?php include '../app/views/includes/footer.php'; ?>
