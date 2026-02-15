<?php
/**
 * Financial Reports Template
 * Detailed analytics for revenue, commissions, and expenses
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
                        <li class="breadcrumb-item active">Financial</li>
                    </ol>
                </nav>
                <h2 class="mb-0">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                    Financial Reports
                </h2>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?php echo BASE_URL; ?>admin/reports/export?type=financial&format=csv" class="btn btn-primary">
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
                    <div class="col-md-4">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select">
                            <option value="7days" <?php echo ($filters['period'] ?? '') === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="30days" <?php echo ($filters['period'] ?? '') === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90days" <?php echo ($filters['period'] ?? '') === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="1year" <?php echo ($filters['period'] ?? '') === '1year' ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select">
                            <option value="revenue" <?php echo ($filters['report_type'] ?? '') === 'revenue' ? 'selected' : ''; ?>>Revenue & Commission</option>
                            <option value="expenses" <?php echo ($filters['report_type'] ?? '') === 'expenses' ? 'selected' : ''; ?>>Expenses</option>
                            <option value="profit_loss" <?php echo ($filters['report_type'] ?? '') === 'profit_loss' ? 'selected' : ''; ?>>Profit & Loss</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
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
                    <h6 class="text-muted">Total Revenue</h6>
                    <h3 class="mb-0 text-primary">₹<?php echo number_format($profit_loss['total_revenue'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Total Expenses</h6>
                    <h3 class="mb-0 text-danger">₹<?php echo number_format($profit_loss['total_expenses'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Net Profit</h6>
                    <h3 class="mb-0 text-success">₹<?php echo number_format($profit_loss['net_profit'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Profit Margin</h6>
                    <h3 class="mb-0 text-info"><?php echo number_format($profit_loss['profit_margin'] ?? 0, 1); ?>%</h3>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Charts Section -->
<section class="financial-charts py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Revenue & Commission Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Expense Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="expenseChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Commission Table -->
<section class="commission-table py-4">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Agent Commission Performance</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Agent Name</th>
                                <th class="text-center">Properties Sold</th>
                                <th class="text-end">Total Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($commission_data)): ?>
                                <?php foreach ($commission_data as $agent): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($agent['agent']); ?></td>
                                        <td class="text-center"><?php echo number_format($agent['properties_sold']); ?></td>
                                        <td class="text-end">₹<?php echo number_format($agent['commission']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No commission data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = <?php echo json_encode($revenue_data ?? []); ?>;
    
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: revenueData.map(d => d.month),
            datasets: [{
                label: 'Revenue',
                data: revenueData.map(d => d.revenue),
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: '#0d6efd',
                borderWidth: 1
            }, {
                label: 'Commission',
                data: revenueData.map(d => d.commission),
                type: 'line',
                borderColor: '#ffc107',
                backgroundColor: 'transparent',
                tension: 0.4,
                pointBackgroundColor: '#ffc107'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
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
    const expenseData = <?php echo json_encode($expense_data ?? []); ?>;
    
    new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: expenseData.map(d => d.category),
            datasets: [{
                data: expenseData.map(d => d.amount),
                backgroundColor: [
                    '#0d6efd', '#dc3545', '#ffc107', '#198754', '#6c757d'
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
