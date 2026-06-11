<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-user-tie me-2"></i>Agent/Associate Dashboard</h2>
        </div>
    </div>

    <!-- Agent Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted text-uppercase small">My Commissions</h6>
                    <h3>₹<?php echo number_format(floatval($stats['total_commissions'] ?? 0) ?? 0); ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-wallet me-1"></i>₹<?php echo number_format(floatval($stats['pending_commissions'] ?? 0) ?? 0); ?> Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-info border-4 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted text-uppercase small">My Network Size</h6>
                    <h3><?php echo $network['total_associates'] ?? 0; ?></h3>
                    <p class="text-muted mb-0"><?php echo $network['active_associates'] ?? 0; ?> Active now</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted text-uppercase small">Performance</h6>
                    <h3><?php echo $stats['total_sales'] ?? 0; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-chart-line me-1"></i>Sales this month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Sales Performance (Last 7 Days)</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="performanceChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Network Activity</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <?php if (empty($activities)): ?>
                            <li class="list-group-item text-center py-4 text-muted">No recent activity</li>
                        <?php else: ?>
                            <?php foreach ($activities as $activity): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($activity['activity_type'] ?? $activity['description'] ?? ''); ?></strong>
                                    <div class="text-end mt-1"><small><?php echo date('M d, Y H:i', strtotime($activity['created_at'] ?? 'now')); ?></small></div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const perfCtx = document.getElementById('performanceChart').getContext('2d');
        const perfData = <?php echo json_encode($performance ?? []); ?>;

        new Chart(perfCtx, {
            type: 'bar',
            data: {
                labels: perfData.map(item => item.date).reverse(),
                datasets: [{
                    label: 'Daily Commission (₹)',
                    data: perfData.map(item => item.daily_commission).reverse(),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }, {
                    label: 'Sales Count',
                    data: perfData.map(item => item.sales_count).reverse(),
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true, position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    });
</script>

<?php  ?>