<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-calculator me-2"></i>CFO Dashboard DEBUG START</h2>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted text-uppercase small">Total Revenue</h6>
                    <h3>₹<?php echo number_format(floatval($financial_overview['total_revenue'] ?? 0) ?? 0); ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>+₹<?php echo number_format(floatval($financial_overview['pending_revenue'] ?? 0) ?? 0); ?> Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted text-uppercase small">Total Expenses</h6>
                    <h3>₹<?php echo number_format(floatval($expense_stats['total_expenses'] ?? 0) ?? 0); ?></h3>
                    <p class="text-muted mb-0">Avg: ₹<?php echo number_format(floatval($expense_stats['avg_expense'] ?? 0) ?? 0); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted text-uppercase small">Commission Paid</h6>
                    <h3>₹<?php echo number_format(floatval($commission_stats['total_commissions'] ?? 0) ?? 0); ?></h3>
                    <p class="text-muted mb-0">Avg: ₹<?php echo number_format(floatval($commission_stats['avg_commission'] ?? 0) ?? 0); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted text-uppercase small">Net Profit</h6>
                    <h3>₹<?php echo number_format(floatval($profit_analysis['net_profit'] ?? 0) ?? 0); ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-chart-line me-1"></i>Profit Margin: <?php echo ($profit_analysis['gross_revenue'] ?? 0) > 0 ? number_format((($profit_analysis['net_profit'] ?? 0) / ($profit_analysis['gross_revenue'] ?? 1)) * 100, 1) : 0; ?>%</p>
                </div>
        </div>
    </div>
</div>

    <!-- Top Performers -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Performers DEBUG: tp=isset</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php
                        $tp = $top_performers ?? [];
                        foreach (['associate' => 'Top Associate', 'agent' => 'Top Agent', 'employee' => 'Top Employee'] as $key => $title) {
                            $p = $tp[$key] ?? ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'];
                            $icon = match($key) { 'associate' => 'fa-users', 'agent' => 'fa-user-tie', 'employee' => 'fa-user-cog' };
                            echo '<div class="col-md-4">';
                            echo '<div class="card h-100 border-start border-primary border-3">';
                            echo '<div class="card-body text-center">';
                            echo '<i class="fas ' . $icon . ' fa-2x text-primary mb-2"></i>';
                            echo '<h6 class="text-muted mb-1">' . $title . '</h6>';
                            echo '<h4 class="mb-1">' . htmlspecialchars($p['name'] ?? 'N/A') . '</h4>';
                            echo '<span class="badge bg-primary mb-2">' . htmlspecialchars($p['level'] ?? 'N/A') . '</span>';
                            echo '<p class="text-muted small mb-0">' . htmlspecialchars($p['metric'] ?? 'N/A') . '</p>';
                            echo '</div></div></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Revenue Analytics (30 Days)</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="revenueChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5><i class="fas fa-pie-chart me-2"></i>Expense Breakdown</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="expenseChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5><i class="fas fa-balance-scale me-2"></i>Financial Summary</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">₹<?php echo number_format(floatval($profit_analysis['gross_revenue'] ?? 0) ?? 0); ?></h4>
                                <p class="text-muted small">Gross Revenue</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger">₹<?php echo number_format(floatval($profit_analysis['total_expenses_paid'] ?? 0) ?? 0); ?></h4>
                                <p class="text-muted small">Total Expenses</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">₹<?php echo number_format(floatval($profit_analysis['total_commissions_paid'] ?? 0) ?? 0); ?></h4>
                                <p class="text-muted small">Commissions Paid</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">₹<?php echo number_format(floatval($profit_analysis['net_profit'] ?? 0) ?? 0); ?></h4>
                                <p class="text-muted small">Net Profit</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Financial Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5><i class="fas fa-history me-2"></i>Recent Financial Activities</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($activities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($activities as $activity): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['activity_type'] ?? ''); ?></h6>
                                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($activity['description'] ?? ''); ?></p>
                                    <small class="text-<?php echo ($activity['amount'] ?? 0) > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo ($activity['amount'] ?? 0) > 0 ? '+' : ''; ?>₹<?php echo number_format(floatval($activity['amount'] ?? 0)); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent financial activities found.</p>
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
        // Revenue Chart - Load via AJAX
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: { labels: [], datasets: [{
                label: 'Daily Revenue', data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]},
            options: {
                responsive: true,
                plugins: { legend: { display: true, position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return '₹' + v.toLocaleString(); } } } }
            }
        });

        fetch('<?php echo BASE_URL; ?>/admin/cfo-dashboard/financial')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    revenueChart.data.labels = data.data.map(item => item.date).reverse();
                    revenueChart.data.datasets[0].data = data.data.map(item => item.daily_revenue).reverse();
                    revenueChart.update();
                }
            }).catch(() => {});

        // Expense Chart - Load via AJAX
        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
        const expenseChart = new Chart(expenseCtx, {
            type: 'doughnut',
            data: { labels: [], datasets: [{ data: [], backgroundColor: [
                'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)', 'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ]}]},
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        fetch('<?php echo BASE_URL; ?>/admin/cfo-dashboard/expenses')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    expenseChart.data.labels = data.data.map(item => item.category);
                    expenseChart.data.datasets[0].data = data.data.map(item => item.total_amount);
                    expenseChart.update();
                }
            }).catch(() => {});
    });
</script>

<?php  ?>