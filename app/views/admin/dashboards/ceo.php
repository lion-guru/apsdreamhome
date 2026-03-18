<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-crown me-2"></i>CEO Dashboard</h2>
        </div>
    </div>

    <!-- Business Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Properties</h6>
                    <h3><?php echo number_format($business_stats['total_properties'] ?? 0); ?></h3>
                    <p class="text-muted mb-0"><?php echo number_format($business_stats['available_properties'] ?? 0); ?> Available</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Revenue</h6>
                    <h3>₹<?php echo number_format($revenue_stats['total_revenue'] ?? 0); ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>+₹<?php echo number_format($revenue_stats['pending_revenue'] ?? 0); ?> Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Team Size</h6>
                    <h3><?php echo number_format($team_stats['total_users'] ?? 0); ?></h3>
                    <p class="text-muted mb-0"><?php echo number_format($team_stats['active_users'] ?? 0); ?> Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Commission Paid</h6>
                    <h3>₹<?php echo number_format($commission_stats['total_commissions'] ?? 0); ?></h3>
                    <p class="text-muted mb-0">Avg: ₹<?php echo number_format($commission_stats['avg_commission'] ?? 0); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Revenue Analytics (30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-pie-chart me-2"></i>Property Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="propertyChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users me-2"></i>Team Performance</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo number_format($team_stats['admin_users'] ?? 0); ?></h4>
                                <p class="text-muted small">Admin Users</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo number_format($team_stats['associate_users'] ?? 0); ?></h4>
                                <p class="text-muted small">Associates</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-success"><?php echo number_format($team_stats['customer_users'] ?? 0); ?></h4>
                                <p class="text-muted small">Customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($activities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($activities as $activity): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['activity_type']); ?></h6>
                                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent activities found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?php echo json_encode(array_map(function ($item) {
                                return ['date' => $item['date'], 'revenue' => $item['daily_revenue']];
                            }, array_fill(0, 30, ['date' => date('Y-m-d'), 'revenue' => 0]))); ?>;

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => item.date),
                datasets: [{
                    label: 'Daily Revenue',
                    data: revenueData.map(item => item.revenue),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Property Status Chart
        const propertyCtx = document.getElementById('propertyChart').getContext('2d');
        new Chart(propertyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Sold', 'Reserved'],
                datasets: [{
                    data: [
                        <?php echo $business_stats['available_properties'] ?? 0; ?>,
                        <?php echo $business_stats['sold_properties'] ?? 0; ?>,
                        <?php echo ($business_stats['total_properties'] ?? 0) - ($business_stats['available_properties'] ?? 0) - ($business_stats['sold_properties'] ?? 0); ?>
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>