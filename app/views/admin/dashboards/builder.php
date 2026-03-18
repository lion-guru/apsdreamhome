<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-hard-hat me-2"></i>Builder Dashboard</h2>
        </div>
    </div>

    <!-- Construction Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Projects</h6>
                    <h3><?php echo number_format($construction_stats['total_projects'] ?? 0); ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-check-circle me-1"></i><?php echo number_format($construction_stats['completed_projects'] ?? 0); ?> Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Ongoing Projects</h6>
                    <h3><?php echo number_format($construction_stats['ongoing_projects'] ?? 0); ?></h3>
                    <p class="text-muted mb-0">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Material Cost</h6>
                    <h3>₹<?php echo number_format($material_stats['total_material_cost'] ?? 0); ?></h3>
                    <p class="text-muted mb-0"><?php echo number_format($material_stats['total_materials'] ?? 0); ?> Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Workforce</h6>
                    <h3><?php echo number_format($workforce_stats['active_workers'] ?? 0); ?></h3>
                    <p class="text-muted mb-0">Active Workers</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Construction Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Construction Analytics (30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="constructionChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users me-2"></i>Workforce Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="workforceChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Material Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-boxes me-2"></i>Material Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo number_format($material_stats['total_materials'] ?? 0); ?></h4>
                                <p class="text-muted small">Total Materials</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger"><?php echo number_format($material_stats['low_stock_materials'] ?? 0); ?></h4>
                                <p class="text-muted small">Low Stock Items</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo number_format($workforce_stats['masons'] ?? 0); ?></h4>
                                <p class="text-muted small">Masons</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success"><?php echo number_format($workforce_stats['carpenters'] ?? 0); ?></h4>
                                <p class="text-muted small">Carpenters</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Construction Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i>Recent Construction Activities</h5>
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
                        <p class="text-muted">No recent construction activities found.</p>
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
        // Construction Chart
        const constructionCtx = document.getElementById('constructionChart').getContext('2d');
        const constructionData = <?php echo json_encode(array_map(function ($item) {
                                        return ['date' => $item['date'], 'projects' => $item['projects_started'], 'completed' => $item['projects_completed']];
                                    }, array_fill(0, 30, ['date' => date('Y-m-d'), 'projects' => 0, 'completed' => 0]))); ?>;

        new Chart(constructionCtx, {
            type: 'line',
            data: {
                labels: constructionData.map(item => item.date),
                datasets: [{
                    label: 'Projects Started',
                    data: constructionData.map(item => item.projects),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Projects Completed',
                    data: constructionData.map(item => item.completed),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
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
                        beginAtZero: true
                    }
                }
            }
        });

        // Workforce Chart
        const workforceCtx = document.getElementById('workforceChart').getContext('2d');
        new Chart(workforceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Masons', 'Carpenters', 'Electricians', 'Others'],
                datasets: [{
                    data: [
                        <?php echo $workforce_stats['masons'] ?? 0; ?>,
                        <?php echo $workforce_stats['carpenters'] ?? 0; ?>,
                        <?php echo $workforce_stats['electricians'] ?? 0; ?>,
                        <?php echo ($workforce_stats['total_workers'] ?? 0) - ($workforce_stats['masons'] ?? 0) - ($workforce_stats['carpenters'] ?? 0) - ($workforce_stats['electricians'] ?? 0); ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
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