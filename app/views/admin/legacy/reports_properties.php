<?php
/**
 * Property Performance Reports Template
 * Detailed analytics for property performance
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
                        <li class="breadcrumb-item active">Properties</li>
                    </ol>
                </nav>
                <h2 class="mb-0">
                    <i class="fas fa-home text-primary me-2"></i>
                    Property Performance
                </h2>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?php echo BASE_URL; ?>admin/reports/export?type=properties&format=csv" class="btn btn-success">
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
                    <div class="col-md-2">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select">
                            <option value="7days" <?php echo ($filters['period'] ?? '') === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="30days" <?php echo ($filters['period'] ?? '') === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90days" <?php echo ($filters['period'] ?? '') === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="1year" <?php echo ($filters['period'] ?? '') === '1year' ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Property Type</label>
                        <select name="property_type" class="form-select">
                            <option value="all">All Types</option>
                            <option value="apartment" <?php echo ($filters['property_type'] ?? '') === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="villa" <?php echo ($filters['property_type'] ?? '') === 'villa' ? 'selected' : ''; ?>>Villa</option>
                            <option value="plot" <?php echo ($filters['property_type'] ?? '') === 'plot' ? 'selected' : ''; ?>>Plot</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">City</label>
                        <select name="city" class="form-select">
                            <option value="all">All Cities</option>
                            <!-- City options would be dynamic in production -->
                            <option value="lucknow" <?php echo ($filters['city'] ?? '') === 'lucknow' ? 'selected' : ''; ?>>Lucknow</option>
                            <option value="kanpur" <?php echo ($filters['city'] ?? '') === 'kanpur' ? 'selected' : ''; ?>>Kanpur</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="views" <?php echo ($filters['sort'] ?? '') === 'views' ? 'selected' : ''; ?>>Views</option>
                            <option value="favorites" <?php echo ($filters['sort'] ?? '') === 'favorites' ? 'selected' : ''; ?>>Favorites</option>
                            <option value="inquiries" <?php echo ($filters['sort'] ?? '') === 'inquiries' ? 'selected' : ''; ?>>Inquiries</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
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
                    <h6 class="text-muted">Total Views</h6>
                    <h3 class="mb-0 text-primary"><?php echo number_format($property_stats['total_views'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Total Favorites</h6>
                    <h3 class="mb-0 text-danger"><?php echo number_format($property_stats['total_favorites'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Total Inquiries</h6>
                    <h3 class="mb-0 text-warning"><?php echo number_format($property_stats['total_inquiries'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Conversion Rate</h6>
                    <h3 class="mb-0 text-success"><?php echo number_format($property_stats['conversion_rate'] ?? 0, 1); ?>%</h3>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trends and Top Performers -->
<section class="trends-performers py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Property Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="propertyTrendsChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Top Performing Properties</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (!empty($top_performers)): ?>
                                <?php foreach ($top_performers as $property): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($property['title']); ?></h6>
                                            <small class="text-muted"><?php echo $property['views']; ?> views • <?php echo $property['inquiries']; ?> inquiries</small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">#1</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted">No data available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trendsCtx = document.getElementById('propertyTrendsChart').getContext('2d');
    const trendsData = <?php echo json_encode($property_trends ?? []); ?>;
    
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: trendsData.map(d => d.date),
            datasets: [{
                label: 'Views',
                data: trendsData.map(d => d.views),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true
            }, {
                label: 'Inquiries',
                data: trendsData.map(d => d.inquiries),
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
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
});
</script>
