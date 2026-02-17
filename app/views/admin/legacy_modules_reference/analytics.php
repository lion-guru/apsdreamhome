<?php
/**
 * APS Dream Home - Comprehensive Analytics Dashboard
 * Analytics for all systems: Properties, Colonies, MLM, Sales, Performance
 */

require_once 'core/init.php';

// Get admin user data
$admin_id = getAuthUserId();
$admin_username = getAuthUsername();

// Audit Logging
if (function_exists('log_admin_activity')) {
    log_admin_activity($admin_id, 'view_analytics', 'Accessed the comprehensive analytics dashboard');
}

// Real analytics data from database
if (!isset($perfManager)) {
    require_once __DIR__ . '/../includes/performance_manager.php';
    $perfManager = PerformanceManager::getInstance();
}

// Total Revenue
$totalRevenueData = $perfManager->executeCachedQuery("SELECT SUM(amount) as sum FROM bookings WHERE status='confirmed'", 300);
$totalRevenue = $totalRevenueData[0]['sum'] ?? 0;

// Total Properties Sold
$totalPropertiesSoldData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM bookings WHERE status='confirmed'", 300);
$totalPropertiesSold = $totalPropertiesSoldData[0]['cnt'] ?? 0;

// Total Plots Sold
$totalPlotsSoldData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM plots WHERE status='sold'", 300);
$totalPlotsSold = $totalPlotsSoldData[0]['cnt'] ?? 0;

// Total Active Associates
$totalAssociatesData = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM associates WHERE status='active'", 300);
$totalAssociates = $totalAssociatesData[0]['cnt'] ?? 0;

// Yearly Target
$yearlyTarget = 50000000;
$currentAchievement = $totalRevenue;

// Sales Performance Trend (Last 6 Months)
$salesTrend = $perfManager->executeCachedQuery("
    SELECT 
        DATE_FORMAT(booking_date, '%b %Y') as month, 
        SUM(CASE WHEN property_id IS NOT NULL THEN amount ELSE 0 END) as property_sales,
        SUM(CASE WHEN plot_id IS NOT NULL THEN amount ELSE 0 END) as plot_sales,
        SUM(amount) as total_sales
    FROM bookings 
    WHERE status='confirmed' 
    GROUP BY DATE_FORMAT(booking_date, '%Y-%m') 
    ORDER BY booking_date ASC 
    LIMIT 6
", 600);

// Project Performance
$projectPerformance = $perfManager->executeCachedQuery("
    SELECT 
        p.name, 
        COUNT(CASE WHEN pl.status = 'sold' THEN 1 END) as sold, 
        COUNT(pl.id) as total, 
        COALESCE(SUM(b.amount), 0) as revenue
    FROM projects p
    LEFT JOIN plots pl ON p.id = pl.project_id
    LEFT JOIN bookings b ON pl.id = b.plot_id AND b.status = 'confirmed'
    GROUP BY p.id, p.name
    ORDER BY revenue DESC
    LIMIT 5
", 600);

// Top Associates
$associatePerformance = $perfManager->executeCachedQuery("
    SELECT 
        a.name, 
        COUNT(b.id) as sales, 
        COALESCE(SUM(c.commission_amount), 0) as commission,
        (SELECT COUNT(*) FROM associates WHERE status = 'active' AND id != a.id LIMIT 10) as team_size
    FROM associates a
    LEFT JOIN commission_transactions c ON a.id = c.associate_id AND c.status = 'paid'
    LEFT JOIN bookings b ON a.id = b.associate_id AND b.status = 'confirmed'
    WHERE a.status = 'active'
    GROUP BY a.id, a.name
    ORDER BY sales DESC
    LIMIT 5
", 600);

// Revenue Breakdown
$revenueBreakdownData = $perfManager->executeCachedQuery("
    SELECT 
        (SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE status='confirmed' AND property_id IS NOT NULL) as property_sales,
        (SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE status='confirmed' AND plot_id IS NOT NULL) as plot_sales,
        (SELECT COALESCE(SUM(commission_amount), 0) FROM commission_transactions WHERE status='paid') as commission_income
", 600);

$rb = $revenueBreakdownData[0] ?? ['property_sales' => 0, 'plot_sales' => 0, 'commission_income' => 0];

$analytics_data = [
    'overview' => [
        'total_revenue' => $totalRevenue,
        'total_properties_sold' => $totalPropertiesSold,
        'total_plots_sold' => $totalPlotsSold,
        'total_associates' => $totalAssociates,
        'yearly_target' => $yearlyTarget,
        'current_achievement' => $currentAchievement,
        'customer_satisfaction' => 4.8
    ],
    'sales_performance' => $salesTrend,
    'project_performance' => $projectPerformance,
    'associate_performance' => $associatePerformance,
    'revenue_breakdown' => $rb
];

$page_title = $mlSupport->translate('Analytics Dashboard');
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<style>
    .metric-card {
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-5px);
    }

    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .metric-primary .metric-icon {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .metric-success .metric-icon {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .metric-warning .metric-icon {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
    }

    .metric-info .metric-icon {
        background: linear-gradient(135deg, #17a2b8, #6610f2);
        color: white;
    }

    .metric-value {
        font-size: 2rem;
        font-weight: 800;
        color: #1a237e;
        margin-bottom: 5px;
    }

    .metric-label {
        color: #666;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .metric-change {
        font-size: 0.8rem;
        font-weight: 600;
    }

    .change-positive {
        color: #28a745;
    }

    .change-negative {
        color: #dc3545;
    }

    .performance-bar {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin: 5px 0;
    }

    .performance-fill {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #20c997);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .revenue-breakdown-container {
        display: flex;
        height: 300px;
        margin-bottom: 20px;
        align-items: flex-end;
    }

    .revenue-segment {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        text-align: center;
        padding: 10px;
        color: white;
        font-weight: 600;
        margin: 0 5px;
        border-radius: 8px 8px 0 0;
    }

    .segment-property {
        background: linear-gradient(135deg, #667eea, #764ba2);
    }

    .segment-plot {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .segment-commission {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
    }

    .segment-value {
        font-size: 1.2rem;
        font-weight: 800;
    }

    .segment-label {
        font-size: 0.75rem;
        opacity: 0.9;
    }

    .associate-avatar {
        width: 35px;
        height: 35px;
        background: #f0f0f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #667eea;
        margin-right: 10px;
    }
</style>

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

        <!-- Date Filter -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-2 mb-md-0">
                        <label class="form-label me-2"><?php echo h($mlSupport->translate('Date Range')); ?>:</label>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm active" data-period="today"><?php echo h($mlSupport->translate('Today')); ?></button>
                            <button class="btn btn-outline-primary btn-sm" data-period="week"><?php echo h($mlSupport->translate('This Week')); ?></button>
                            <button class="btn btn-outline-primary btn-sm" data-period="month"><?php echo h($mlSupport->translate('This Month')); ?></button>
                            <button class="btn btn-outline-primary btn-sm" data-period="year"><?php echo h($mlSupport->translate('This Year')); ?></button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="date" class="form-control form-control-sm me-2" id="startDate">
                        <span class="me-2"><?php echo h($mlSupport->translate('to')); ?></span>
                        <input type="date" class="form-control form-control-sm me-2" id="endDate">
                        <button class="btn btn-primary btn-sm" onclick="applyCustomFilter()"><?php echo h($mlSupport->translate('Apply')); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 metric-card metric-primary">
                    <div class="metric-icon"><i class="fas fa-rupee-sign"></i></div>
                    <div class="metric-value">₹<?php echo h(number_format($analytics_data['overview']['total_revenue']/100000, 1)); ?>L</div>
                    <div class="metric-label"><?php echo h($mlSupport->translate('Total Revenue')); ?></div>
                    <div class="metric-change change-positive"><i class="fas fa-arrow-up me-1"></i>+12.5% <?php echo h($mlSupport->translate('from last month')); ?></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 metric-card metric-success">
                    <div class="metric-icon"><i class="fas fa-home"></i></div>
                    <div class="metric-value"><?php echo h($analytics_data['overview']['total_properties_sold']); ?></div>
                    <div class="metric-label"><?php echo h($mlSupport->translate('Properties Sold')); ?></div>
                    <div class="metric-change change-positive"><i class="fas fa-arrow-up me-1"></i>+8.2% <?php echo h($mlSupport->translate('from last month')); ?></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 metric-card metric-warning">
                    <div class="metric-icon"><i class="fas fa-map-marked-alt"></i></div>
                    <div class="metric-value"><?php echo h($analytics_data['overview']['total_plots_sold']); ?></div>
                    <div class="metric-label"><?php echo h($mlSupport->translate('Plots Sold')); ?></div>
                    <div class="metric-change change-positive"><i class="fas fa-arrow-up me-1"></i>+15.3% <?php echo h($mlSupport->translate('from last month')); ?></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 metric-card metric-info">
                    <div class="metric-icon"><i class="fas fa-users"></i></div>
                    <div class="metric-value"><?php echo h($analytics_data['overview']['total_associates']); ?></div>
                    <div class="metric-label"><?php echo h($mlSupport->translate('Active Associates')); ?></div>
                    <div class="metric-change change-positive"><i class="fas fa-arrow-up me-1"></i>+5.7% <?php echo h($mlSupport->translate('from last month')); ?></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chart-bar me-2"></i><?php echo h($mlSupport->translate('Sales Performance Trend')); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-pie-chart me-2"></i><?php echo h($mlSupport->translate('Revenue Breakdown')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="revenue-breakdown-container">
                            <?php
                            $total_rev_break = $analytics_data['revenue_breakdown']['property_sales'] +
                                             $analytics_data['revenue_breakdown']['plot_sales'] +
                                             $analytics_data['revenue_breakdown']['commission_income'];
                            
                            $prop_p = $total_rev_break > 0 ? ($analytics_data['revenue_breakdown']['property_sales'] / $total_rev_break) * 100 : 0;
                            $plot_p = $total_rev_break > 0 ? ($analytics_data['revenue_breakdown']['plot_sales'] / $total_rev_break) * 100 : 0;
                            $comm_p = $total_rev_break > 0 ? ($analytics_data['revenue_breakdown']['commission_income'] / $total_rev_break) * 100 : 0;
                            ?>
                            <div class="revenue-segment segment-property" style="height: <?php echo $prop_p; ?>%;">
                                <div class="segment-value">₹<?php echo h(number_format($analytics_data['revenue_breakdown']['property_sales']/100000, 1)); ?>L</div>
                                <div class="segment-label"><?php echo h($mlSupport->translate('Properties')); ?></div>
                            </div>
                            <div class="revenue-segment segment-plot" style="height: <?php echo $plot_p; ?>%;">
                                <div class="segment-value">₹<?php echo h(number_format($analytics_data['revenue_breakdown']['plot_sales']/100000, 1)); ?>L</div>
                                <div class="segment-label"><?php echo h($mlSupport->translate('Plots')); ?></div>
                            </div>
                            <div class="revenue-segment segment-commission" style="height: <?php echo $comm_p; ?>%;">
                                <div class="segment-value">₹<?php echo h(number_format($analytics_data['revenue_breakdown']['commission_income']/100000, 1)); ?>L</div>
                                <div class="segment-label"><?php echo h($mlSupport->translate('Commission')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-project-diagram me-2"></i><?php echo h($mlSupport->translate('Project Performance')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Project Name')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Sold')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Total')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Revenue')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Performance')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics_data['project_performance'] as $project): ?>
                                        <?php $percentage = $project['total'] > 0 ? ($project['sold'] / $project['total']) * 100 : 0; ?>
                                        <tr>
                                            <td><?php echo h($project['name']); ?></td>
                                            <td><?php echo h($project['sold']); ?></td>
                                            <td><?php echo h($project['total']); ?></td>
                                            <td>₹<?php echo h(number_format($project['revenue']/100000, 1)); ?>L</td>
                                            <td>
                                                <div class="performance-bar">
                                                    <div class="performance-fill" style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                                <small><?php echo h(round($percentage, 1)); ?>%</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-user-tie me-2"></i><?php echo h($mlSupport->translate('Top Associates')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Associate')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Sales')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Commission')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics_data['associate_performance'] as $associate): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="associate-avatar"><?php echo h(substr($associate['name'], 0, 1)); ?></div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo h($associate['name']); ?></div>
                                                        <small class="text-muted"><?php echo h($mlSupport->translate('Team')); ?>: <?php echo h($associate['team_size']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo h($associate['sales']); ?></td>
                                            <td>₹<?php echo h(number_format($associate['commission']/1000, 0)); ?>K</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-bullseye me-2"></i><?php echo h($mlSupport->translate('Yearly Target Achievement')); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php $achievement_p = $analytics_data['overview']['yearly_target'] > 0 ? ($analytics_data['overview']['current_achievement'] / $analytics_data['overview']['yearly_target']) * 100 : 0; ?>
                        <div class="progress mb-3" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $achievement_p; ?>%" aria-valuenow="<?php echo $achievement_p; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo h(round($achievement_p, 1)); ?>%</div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><?php echo h($mlSupport->translate('Achieved')); ?>: ₹<?php echo h(number_format($analytics_data['overview']['current_achievement']/100000, 1)); ?>L</span>
                            <span class="text-muted"><?php echo h($mlSupport->translate('Target')); ?>: ₹<?php echo h(number_format($analytics_data['overview']['yearly_target']/100000, 1)); ?>L</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-star me-2"></i><?php echo h($mlSupport->translate('Customer Satisfaction')); ?></h5>
                    </div>
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-value me-3"><?php echo h($analytics_data['overview']['customer_satisfaction']); ?>/5</div>
                        <div>
                            <div class="text-warning">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                            <small class="text-muted"><?php echo h($mlSupport->translate('Based on 500+ reviews')); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$chartLabels = json_encode(array_column($analytics_data['sales_performance'], 'month'));
$propertySalesData = json_encode(array_column($analytics_data['sales_performance'], 'property_sales'));
$plotSalesData = json_encode(array_column($analytics_data['sales_performance'], 'plot_sales'));

$page_specific_js = "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
    // Sales Chart
    let salesChart;
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    function initChart(labels, propertyData, plotData) {
        if (salesChart) salesChart.destroy();
        
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '" . $mlSupport->translate('Property Sales') . "',
                    data: propertyData,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: '" . $mlSupport->translate('Plot Sales') . "',
                    data: plotData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
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
    }

    // Initial chart load
    initChart($chartLabels, $propertySalesData, $plotSalesData);

    function updateAnalytics(data) {
        // Update metric cards
        document.querySelector('.metric-primary .metric-value').textContent = '₹' + (data.overview.total_revenue/100000).toFixed(1) + 'L';
        document.querySelector('.metric-success .metric-value').textContent = data.overview.total_properties_sold;
        document.querySelector('.metric-warning .metric-value').textContent = data.overview.total_plots_sold;
        document.querySelector('.metric-info .metric-value').textContent = data.overview.total_associates;

        // Update Chart
        const labels = data.sales_performance.map(item => item.month);
        const propData = data.sales_performance.map(item => item.property_sales);
        const plotData = data.sales_performance.map(item => item.plot_sales);
        initChart(labels, propData, plotData);

        // Update Revenue Breakdown
        const total = parseFloat(data.revenue_breakdown.property_sales) + 
                      parseFloat(data.revenue_breakdown.plot_sales) + 
                      parseFloat(data.revenue_breakdown.commission_income);
        
        if (total > 0) {
            const propP = (data.revenue_breakdown.property_sales / total) * 100;
            const plotP = (data.revenue_breakdown.plot_sales / total) * 100;
            const commP = (data.revenue_breakdown.commission_income / total) * 100;

            document.querySelector('.segment-property').style.height = propP + '%';
            document.querySelector('.segment-property .segment-value').textContent = '₹' + (data.revenue_breakdown.property_sales/100000).toFixed(1) + 'L';
            
            document.querySelector('.segment-plot').style.height = plotP + '%';
            document.querySelector('.segment-plot .segment-value').textContent = '₹' + (data.revenue_breakdown.plot_sales/100000).toFixed(1) + 'L';
            
            document.querySelector('.segment-commission').style.height = commP + '%';
            document.querySelector('.segment-commission .segment-value').textContent = '₹' + (data.revenue_breakdown.commission_income/100000).toFixed(1) + 'L';
        }
    }

    function fetchAnalytics(params) {
        const queryString = new URLSearchParams(params).toString();
        fetch('ajax/get_comprehensive_analytics.php?' + queryString)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateAnalytics(data.data);
                } else {
                    console.error('Failed to fetch analytics:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function applyCustomFilter() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        if (start && end) {
            fetchAnalytics({ start_date: start, end_date: end });
        }
    }

    // Period filter buttons
    document.querySelectorAll('[data-period]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const period = this.getAttribute('data-period');
            fetchAnalytics({ period: period });
        });
    });
</script>
";
include 'admin_footer.php';
?>
