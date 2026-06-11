<?php
$report = $report ?? [
    'network_growth' => [],
    'top_performers' => [],
    'level_distribution' => [],
    'commission_trends' => [],
    'monthly_comparison' => [],
    'generated_at' => date('Y-m-d H:i:s')
];
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">MLM Network Growth Report</h1>
    
    <!-- Export Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="text-muted">Generated: <?= $report['generated_at'] ?></span>
        </div>
        <div>
            <button onclick="exportToPDF()" class="btn btn-danger me-2">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button onclick="refreshData()" class="btn btn-primary">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($report['network_growth']) > 0 ? array_sum(array_column($report['network_growth'], 'new_associates')) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Commissions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format(array_sum(array_column($report['commission_trends'], 'total_commissions')) ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Earners
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($report['top_performers']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Avg Commission
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format(array_sum(array_column($report['commission_trends'], 'avg_commission')) / (count($report['commission_trends']) ?: 1), 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Network Growth Chart -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Network Growth (Last 12 Months)</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="networkGrowthChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Level Distribution -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">Level Distribution</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="levelDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Trends -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">Commission Trends (Last 12 Months)</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <canvas id="commissionTrendsChart" height="100"></canvas>
        </div>
    </div>

    <!-- Top Performers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">Top 20 Performers (Last 30 Days)</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Referral Code</th>
                            <th>Direct Referrals</th>
                            <th>Commissions (30d)</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($report['top_performers'], 0, 20) as $index => $performer): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($performer['name']) ?></td>
                            <td><?= htmlspecialchars($performer['email']) ?></td>
                            <td><code><?= htmlspecialchars($performer['referral_code']) ?></code></td>
                            <td><?= $performer['direct_referrals'] ?></td>
                            <td>₹<?= number_format($performer['total_commissions'] ?? 0, 2) ?></td>
                            <td><?= date('M Y', strtotime($performer['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Monthly Comparison -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">Month-over-Month Comparison</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>New users</th>
                            <th>New Referrals</th>
                            <th>Total Commissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['monthly_comparison'] as $comparison): ?>
                        <tr>
                            <td><strong><?= $comparison['period'] ?></strong></td>
                            <td><?= $comparison['new_associates'] ?></td>
                            <td><?= $comparison['new_referrals'] ?></td>
                            <td>₹<?= number_format($comparison['total_commissions'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Network Growth Chart
const networkGrowthCtx = document.getElementById('networkGrowthChart').getContext('2d');
const networkGrowthData = <?= json_encode($report['network_growth']) ?>;

new Chart(networkGrowthCtx, {
    type: 'line',
    data: {
        labels: networkGrowthData.map(d => d.month),
        datasets: [{
            label: 'Total users',
            data: networkGrowthData.map(d => d.total_associates),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'New users',
            data: networkGrowthData.map(d => d.new_associates),
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        }
    }
});

// Level Distribution Chart
const levelCtx = document.getElementById('levelDistributionChart').getContext('2d');
const levelData = <?= json_encode($report['level_distribution']) ?>;

new Chart(levelCtx, {
    type: 'doughnut',
    data: {
        labels: levelData.map(d => 'Level ' + d.level),
        datasets: [{
            data: levelData.map(d => d.associate_count),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right' }
        }
    }
});

// Commission Trends Chart
const commissionCtx = document.getElementById('commissionTrendsChart').getContext('2d');
const commissionData = <?= json_encode($report['commission_trends']) ?>;

new Chart(commissionCtx, {
    type: 'bar',
    data: {
        labels: commissionData.map(d => d.month),
        datasets: [{
            label: 'Total Commissions',
            data: commissionData.map(d => d.total_commissions),
            backgroundColor: '#36b9cc'
        }, {
            label: 'Active Earners',
            data: commissionData.map(d => d.active_earners),
            backgroundColor: '#f6c23e',
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true },
            y1: { position: 'right', beginAtZero: true }
        }
    }
});

function exportToPDF() {
    window.open('/admin/reports/mlm-growth/export', '_blank');
}

function refreshData() {
    location.reload();
}
</script>

<?php  ?>