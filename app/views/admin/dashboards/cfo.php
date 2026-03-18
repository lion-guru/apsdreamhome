<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-calculator me-2"></i>CFO Dashboard</h2>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Revenue</h6>
                    <h3>₹<?php echo number_format($financial_overview['total_revenue'] ?? 0); ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>+₹<?php echo number_format($financial_overview['pending_revenue'] ?? 0); ?> Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Expenses</h6>
                    <h3>₹<?php echo number_format($expense_stats['total_expenses'] ?? 0); ?></h3>
                    <p class="text-muted mb-0">Avg: ₹<?php echo number_format($expense_stats['avg_expense'] ?? 0); ?></p>
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
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Net Profit</h6>
                    <h3>₹<?php echo number_format($profit_analysis['net_profit'] ?? 0); ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-chart-line me-1"></i>Profit Margin: <?php echo $profit_analysis['gross_revenue'] > 0 ? number_format(($profit_analysis['net_profit'] / $profit_analysis['gross_revenue']) * 100, 1) : 0; ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Charts -->
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
                    <h5><i class="fas fa-pie-chart me-2"></i>Expense Breakdown</h5>
                </div>
                <div class="card-body">
                    <canvas id="expenseChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-balance-scale me-2"></i>Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">₹<?php echo number_format($profit_analysis['gross_revenue'] ?? 0); ?></h4>
                                <p class="text-muted small">Gross Revenue</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger">₹<?php echo number_format($profit_analysis['total_expenses_paid'] ?? 0); ?></h4>
                                <p class="text-muted small">Total Expenses</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">₹<?php echo number_format($profit_analysis['total_commissions_paid'] ?? 0); ?></h4>
                                <p class="text-muted small">Commissions Paid</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">₹<?php echo number_format($profit_analysis['net_profit'] ?? 0); ?></h4>
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
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i>Recent Financial Activities</h5>
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
                                    <small class="text-<?php echo $activity['amount'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $activity['amount'] > 0 ? '+' : ''; ?>₹<?php echo number_format($activity['amount']); ?>
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

        // Expense Chart
        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
        const expenseData = <?php echo json_encode([
                                ['category' => 'Operations', 'amount' => 0],
                                ['category' => 'Marketing', 'amount' => 0],
                                ['category' => 'Salaries', 'amount' => 0],
                                ['category' => 'Utilities', 'amount' => 0],
                                ['category' => 'Other', 'amount' => 0]
                            ]); ?>;

        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: expenseData.map(item => item.category),
                datasets: [{
                    data: expenseData.map(item => item.amount),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
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