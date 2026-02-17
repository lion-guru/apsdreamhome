<?php
/**
 * APS Dream Home - MLM Commission Analytics
 * Detailed insights into commission distribution, rank performance, and bonuses
 */

require_once 'core/init.php';
require_once __DIR__ . '/../includes/functions/mlm_commission_ledger.php';
require_once __DIR__ . '/../includes/functions/mlm_business.php';
require_once __DIR__ . '/../includes/functions/mlm_commission_bonuses.php';

// Permission check
if (!isset($permission_util)) {
    require_once __DIR__ . '/../includes/functions/permission_util.php';
}
require_permission('view_commission_analytics');

// Audit Logging
if (function_exists('log_admin_activity')) {
    log_admin_activity(getAuthUserId(), 'view_commission_analytics', 'Accessed the commission analytics dashboard');
}

// Performance Manager for caching
if (!isset($perfManager)) {
    require_once __DIR__ . '/../includes/performance_manager.php';
    $perfManager = getPerformanceManager();
}

// Total commission distributed
$totalCommData = $perfManager->executeCachedQuery("SELECT SUM(commission_amount) as total FROM mlm_commission_ledger", 300);
$total_commission = $totalCommData[0]['total'] ?? 0;

// Monthly commission trend (last 12 months)
$monthly_data = $perfManager->executeCachedQuery("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(commission_amount) as total 
    FROM mlm_commission_ledger 
    GROUP BY ym 
    ORDER BY ym DESC 
    LIMIT 12
", 600);
$monthly_data = array_reverse($monthly_data);

// Top earners by rank
$top_earners_raw = $perfManager->executeCachedQuery("
    SELECT a.name, a.id, SUM(l.commission_amount) as earned 
    FROM mlm_commission_ledger l 
    JOIN associates a ON l.associate_id=a.id 
    GROUP BY l.associate_id 
    ORDER BY earned DESC 
    LIMIT 10
", 600);

$top_earners = [];
$db = \App\Core\App::database();
foreach ($top_earners_raw as $row) {
    $rank = getAssociateRank(getTotalTeamBusiness($db, $row['id']));
    $row['rank'] = $rank;
    $top_earners[] = $row;
}

// Bonus payout analytics
$bonus_res = $perfManager->executeCachedQuery("
    SELECT a.id, a.name, SUM(ct.amount) as total_business 
    FROM associates a 
    JOIN commission_transactions ct ON a.id=ct.associate_id 
    GROUP BY a.id 
    LIMIT 10
", 600);

$bonus_distribution = [];
foreach ($bonus_res as $row) {
    $rank = getAssociateRank($row['total_business']);
    $bonus = calculateBonus($row['total_business'], $rank);
    $bonus_distribution[] = [
        'name' => $row['name'],
        'id' => $row['id'],
        'rank' => $rank,
        'total_business' => $row['total_business'],
        'bonus' => $bonus
    ];
}

// Business growth rate (last 6 months)
$growth_data = $perfManager->executeCachedQuery("
    SELECT DATE_FORMAT(transaction_date, '%Y-%m') as ym, SUM(amount) as total 
    FROM commission_transactions 
    GROUP BY ym 
    ORDER BY ym DESC 
    LIMIT 6
", 600);
$growth_data = array_reverse($growth_data);

// Rank distribution
$rank_data_raw = $perfManager->executeCachedQuery("
    SELECT a.id, COALESCE(SUM(ct.amount), 0) as total_business 
    FROM associates a 
    LEFT JOIN commission_transactions ct ON a.id=ct.associate_id 
    GROUP BY a.id
", 3600);

$rank_counts = ['Diamond' => 0, 'Platinum' => 0, 'Gold' => 0, 'Silver' => 0, 'Starter' => 0];
foreach ($rank_data_raw as $row) {
    $rank = getAssociateRank($row['total_business']);
    if (isset($rank_counts[$rank])) {
        $rank_counts[$rank]++;
    } else {
        $rank_counts['Starter']++;
    }
}

$page_title = $mlSupport->translate('Commission Analytics');
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Commission Analytics')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="analytics.php"><?php echo h($mlSupport->translate('Analytics')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Commission')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Overview Widget -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 opacity-75"><?php echo h($mlSupport->translate('Total Commission Distributed')); ?></h6>
                                <h2 class="display-5 fw-bold mb-0">₹<?php echo h(number_format($total_commission, 2)); ?></h2>
                            </div>
                            <div class="opacity-25">
                                <i class="fas fa-hand-holding-usd fa-4x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Monthly Trend -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i><?php echo h($mlSupport->translate('Monthly Commission Trend')); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Rank Distribution -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-pie-chart me-2 text-info"></i><?php echo h($mlSupport->translate('Rank Distribution')); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="rankPie" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Earners -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-trophy me-2 text-warning"></i><?php echo h($mlSupport->translate('Top 10 Earners')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Name')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Rank')); ?></th>
                                        <th class="text-end"><?php echo h($mlSupport->translate('Earned')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_earners as $row): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo h($row['name']); ?></div>
                                                <small class="text-muted">ID: <?php echo h($row['id']); ?></small>
                                            </td>
                                            <td><span class="badge bg-soft-info text-info"><?php echo h($row['rank']); ?></span></td>
                                            <td class="text-end fw-bold">₹<?php echo h(number_format($row['earned'], 2)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bonus Distribution -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-gift me-2 text-danger"></i><?php echo h($mlSupport->translate('Bonus Distribution')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Associate')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Business')); ?></th>
                                        <th class="text-end"><?php echo h($mlSupport->translate('Bonus')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bonus_distribution as $row): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo h($row['name']); ?></div>
                                                <small class="text-muted"><?php echo h($row['rank']); ?></small>
                                            </td>
                                            <td>₹<?php echo h(number_format($row['total_business'], 0)); ?></td>
                                            <td class="text-end text-success fw-bold">+₹<?php echo h(number_format($row['bonus'], 2)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Growth -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-success"></i><?php echo h($mlSupport->translate('Business Growth Rate (Last 6 Months)')); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="growthChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$monthlyLabels = json_encode(array_column($monthly_data, 'ym'));
$monthlyTotals = json_encode(array_map('floatval', array_column($monthly_data, 'total')));
$rankLabels = json_encode(array_keys($rank_counts));
$rankData = json_encode(array_values($rank_counts));
$growthLabels = json_encode(array_column($growth_data, 'ym'));
$growthTotals = json_encode(array_map('floatval', array_column($growth_data, 'total')));

$page_specific_js = "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
    // Monthly Trend Chart
    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: $monthlyLabels,
            datasets: [{
                label: '" . $mlSupport->translate('Commission') . " (₹)',
                data: $monthlyTotals,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4
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

    // Rank Distribution Pie
    new Chart(document.getElementById('rankPie').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: $rankLabels,
            datasets: [{
                data: $rankData,
                backgroundColor: ['#b9f2ff','#e5e4e2','#ffd700','#c0c0c0','#a9a9a9'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%'
        }
    });

    // Growth Chart
    new Chart(document.getElementById('growthChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: $growthLabels,
            datasets: [{
                label: '" . $mlSupport->translate('Business Volume') . " (₹)',
                data: $growthTotals,
                backgroundColor: 'rgba(40, 167, 69, 0.6)',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value/100000).toFixed(1) + 'L';
                        }
                    }
                }
            }
        }
    });
</script>
";

include 'admin_footer.php';
?>

