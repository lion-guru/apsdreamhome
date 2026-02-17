<?php
/**
 * APS Dream Home - Analytics Dashboard (Standardized)
 */

require_once 'core/init.php';
require_once __DIR__ . '/../includes/performance_manager.php';

// Audit Logging
if (function_exists('log_admin_activity')) {
    log_admin_activity(getAuthUserId(), 'view_analytics_dashboard', 'Accessed the analytics dashboard');
}

// Initialize Performance Manager
$perfManager = getPerformanceManager();

// Fetch real data
$currentMonth = date('Y-m');

// 1. Total Sales (This Month)
$salesData = $perfManager->executeCachedQuery("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$currentMonth' AND status = 'completed'", 300);
$totalSales = $salesData[0]['total'] ?? 0;

// 2. Active Associates
$associatesData = $perfManager->executeCachedQuery("SELECT COUNT(*) as total FROM associates WHERE status = 'active'", 300);
$activeAssociates = $associatesData[0]['total'] ?? 0;

// 3. Total Payouts (This Month)
$payoutsData = $perfManager->executeCachedQuery("SELECT COALESCE(SUM(commission_amount), 0) as total FROM mlm_commission_ledger WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'", 300);
$totalPayouts = $payoutsData[0]['total'] ?? 0;

// 4. Team Growth (This Month)
$growthData = $perfManager->executeCachedQuery("SELECT COUNT(*) as total FROM associates WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'", 300);
$teamGrowth = $growthData[0]['total'] ?? 0;

// Fetch chart data (Last 6 months)
$monthsLabels = [];
$salesTrend = [];
$teamTrend = [];

for ($i = 5; $i >= 0; $i--) {
    $monthDate = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M', strtotime("-$i months"));
    $monthsLabels[] = $monthLabel;
    
    // Sales Trend
    $monthSalesData = $perfManager->executeCachedQuery("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$monthDate' AND status = 'completed'", 3600);
    $salesTrend[] = floatval($monthSalesData[0]['total'] ?? 0);
    
    // Team Trend
    $monthTeamData = $perfManager->executeCachedQuery("SELECT COUNT(*) as total FROM associates WHERE DATE_FORMAT(created_at, '%Y-%m') = '$monthDate'", 3600);
    $teamTrend[] = intval($monthTeamData[0]['total'] ?? 0);
}

// Audit metrics
$onboard_count_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as c FROM audit_log WHERE action='Onboarding' AND created_at >= NOW() - INTERVAL 30 DAY", 300);
$onboard_count = $onboard_count_data[0]['c'] ?? 0;

$offboard_count_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as c FROM audit_log WHERE action='Offboarding' AND created_at >= NOW() - INTERVAL 30 DAY", 300);
$offboard_count = $offboard_count_data[0]['c'] ?? 0;

$perm_usage = $perfManager->executeCachedQuery("SELECT action, COUNT(*) as usage_count FROM audit_log GROUP BY action ORDER BY usage_count DESC LIMIT 10", 600);

$denials = $perfManager->executeCachedQuery("SELECT DATE(created_at) as d, COUNT(*) as c FROM audit_log WHERE action='Permission Denied' AND created_at >= NOW() - INTERVAL 30 DAY GROUP BY d ORDER BY d DESC", 300);

$page_title = $mlSupport->translate('Analytics Dashboard');
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Analytics Dashboard')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Analytics')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card shadow-sm border-0 bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6><?php echo h($mlSupport->translate('Total Sales')); ?></h6>
                                <h4>₹<?php echo h(number_format($totalSales, 2)); ?></h4>
                            </div>
                            <div>
                                <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <p class="mb-0 small opacity-75"><?php echo h($mlSupport->translate('This Month')); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card shadow-sm border-0 bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6><?php echo h($mlSupport->translate('Active Associates')); ?></h6>
                                <h4><?php echo h(number_format($activeAssociates)); ?></h4>
                            </div>
                            <div>
                                <i class="fas fa-users fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <p class="mb-0 small opacity-75"><?php echo h($mlSupport->translate('Currently Active')); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card shadow-sm border-0 bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6><?php echo h($mlSupport->translate('Total Payouts')); ?></h6>
                                <h4>₹<?php echo h(number_format($totalPayouts, 2)); ?></h4>
                            </div>
                            <div>
                                <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <p class="mb-0 small opacity-75"><?php echo h($mlSupport->translate('This Month')); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card shadow-sm border-0 bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6><?php echo h($mlSupport->translate('Team Growth')); ?></h6>
                                <h4>+<?php echo h(number_format($teamGrowth)); ?></h4>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <p class="mb-0 small opacity-75"><?php echo h($mlSupport->translate('New Associates (Month)')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Sales Trend (Last 6 Months)')); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Team Growth (Last 6 Months)')); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="teamChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Onboarding/Offboarding (30d)')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <h6 class="text-muted"><?php echo h($mlSupport->translate('Onboardings')); ?></h6>
                                <p class="h3 text-primary fw-bold"><?php echo h($onboard_count); ?></p>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted"><?php echo h($mlSupport->translate('Offboardings')); ?></h6>
                                <p class="h3 text-danger fw-bold"><?php echo h($offboard_count); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Top Action Log Activity')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Action')); ?></th>
                                        <th class="text-end"><?php echo h($mlSupport->translate('Usage Count')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($perm_usage)): ?>
                                        <?php foreach($perm_usage as $row): ?>
                                        <tr>
                                            <td><?php echo h($row['action']); ?></td>
                                            <td class="text-end"><?php echo h(number_format($row['usage_count'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="2" class="text-center text-muted"><?php echo h($mlSupport->translate('No data available')); ?></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Permission Denials Trend (30d)')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Date')); ?></th>
                                        <th class="text-end"><?php echo h($mlSupport->translate('Denials')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($denials)): ?>
                                        <?php foreach($denials as $row): ?>
                                        <tr>
                                            <td><?php echo h($row['d']); ?></td>
                                            <td class="text-end text-danger fw-bold"><?php echo h(number_format($row['c'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="2" class="text-center text-muted"><?php echo h($mlSupport->translate('No denials recorded')); ?></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_specific_js = "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
    const months = " . json_encode($monthsLabels) . ";
    const salesData = " . json_encode($salesTrend) . ";
    const teamData = " . json_encode($teamTrend) . ";

    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: '" . $mlSupport->translate('Sales') . " (₹)',
                data: salesData,
                fill: true,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
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
                            return '₹' + (value/1000).toFixed(0) + 'K';
                        }
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('teamChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: '" . $mlSupport->translate('New Associates') . "',
                data: teamData,
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>
";
include 'admin_footer.php';
?>
