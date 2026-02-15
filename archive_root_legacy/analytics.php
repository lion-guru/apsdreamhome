<?php
/**
 * APS Dream Home - Comprehensive Analytics Dashboard
 * Analytics for all systems: Properties, Colonies, MLM, Sales, Performance
 */

require_once 'includes/db_connection.php';
require_once 'includes/enhanced_universal_template.php';

$template = new EnhancedUniversalTemplate();

// Page metadata
$page_title = 'Analytics Dashboard - APS Dream Home';
$page_description = 'Comprehensive analytics and insights for APS Dream Home business operations';

$template->setTitle($page_title);
$template->setDescription($page_description);

// Add CSS
$css_assets = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://unpkg.com/aos@2.3.1/dist/aos.css'
];

foreach ($css_assets as $css) {
    $template->addCSS($css);
}

// Add JS
$js_assets = [
    ['url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', 'defer' => false, 'async' => true],
    ['url' => 'https://unpkg.com/aos@2.3.1/dist/aos.js', 'defer' => true, 'async' => true],
    ['url' => 'https://cdn.jsdelivr.net/npm/chart.js', 'defer' => true, 'async' => true],
    ['url' => '/assets/js/analytics-dashboard.js', 'defer' => true, 'async' => true]
];

foreach ($js_assets as $js) {
    $template->addJS($js['url'], $js['defer'], $js['async']);
}

// Sample analytics data (replace with real database queries)
$analytics_data = [
    'overview' => [
        'total_revenue' => 25000000,
        'total_properties_sold' => 150,
        'total_plots_sold' => 280,
        'total_associates' => 450,
        'active_projects' => 8,
        'customer_satisfaction' => 4.7,
        'monthly_growth' => 12.5,
        'yearly_target' => 50000000,
        'current_achievement' => 25000000
    ],
    'sales_performance' => [
        'this_month' => 4500000,
        'last_month' => 3800000,
        'this_year' => 25000000,
        'last_year' => 18000000,
        'growth_rate' => 38.9
    ],
    'project_performance' => [
        ['name' => 'Dream City Gorakhpur', 'sold' => 85, 'total' => 120, 'revenue' => 8500000],
        ['name' => 'Royal Residency', 'sold' => 45, 'total' => 80, 'revenue' => 7200000],
        ['name' => 'Green Valley', 'sold' => 32, 'total' => 60, 'revenue' => 3200000],
        ['name' => 'Premium Heights', 'sold' => 18, 'total' => 40, 'revenue' => 2800000]
    ],
    'associate_performance' => [
        ['name' => 'Rajesh Kumar', 'sales' => 12, 'commission' => 180000, 'team_size' => 25],
        ['name' => 'Priya Sharma', 'sales' => 8, 'commission' => 120000, 'team_size' => 18],
        ['name' => 'Amit Singh', 'sales' => 6, 'commission' => 90000, 'team_size' => 15],
        ['name' => 'Sneha Patel', 'sales' => 5, 'commission' => 75000, 'team_size' => 12]
    ],
    'revenue_breakdown' => [
        'property_sales' => 15000000,
        'plot_sales' => 8500000,
        'commission_income' => 1500000
    ]
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <style>
        .analytics-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }

        .metric-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
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

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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

        .associate-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            margin-right: 10px;
        }

        .revenue-breakdown {
            display: flex;
            height: 300px;
            margin-bottom: 20px;
        }

        .revenue-segment {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            text-align: center;
            padding: 10px;
            color: white;
            font-weight: 600;
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
            font-size: 1.5rem;
            font-weight: 800;
        }

        .segment-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .date-filter {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .btn-filter {
            border: 2px solid #667eea;
            background: transparent;
            color: #667eea;
            padding: 8px 20px;
            border-radius: 20px;
            margin: 2px;
            transition: all 0.3s ease;
        }

        .btn-filter.active,
        .btn-filter:hover {
            background: #667eea;
            color: white;
        }

        .target-progress {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
        }

        .progress-bar {
            background: linear-gradient(90deg, #28a745, #20c997);
        }

        .target-info {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .target-achieved {
            color: #28a745;
            font-weight: 700;
        }

        .target-remaining {
            color: #666;
        }

        @media (max-width: 768px) {
            .metric-card {
                margin-bottom: 15px;
            }

            .revenue-breakdown {
                height: 200px;
            }

            .segment-value {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="analytics-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-5 fw-bold mb-3" data-aos="fade-up">
                        <i class="fas fa-chart-line me-3"></i>Analytics Dashboard
                    </h1>
                    <p class="lead" data-aos="fade-up" data-aos-delay="100">
                        Comprehensive insights into APS Dream Home business performance
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Date Filter -->
    <section class="py-3">
        <div class="container">
            <div class="date-filter">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <label class="form-label mb-0">Date Range:</label>
                        <div class="btn-group">
                            <button class="btn btn-filter active" data-period="today">Today</button>
                            <button class="btn btn-filter" data-period="week">This Week</button>
                            <button class="btn btn-filter" data-period="month">This Month</button>
                            <button class="btn btn-filter" data-period="year">This Year</button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="date" class="form-control me-2" id="startDate">
                        <span>to</span>
                        <input type="date" class="form-control ms-2" id="endDate">
                        <button class="btn btn-primary ms-3" onclick="applyCustomFilter()">Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Metrics -->
    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card metric-primary">
                        <div class="metric-icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="metric-value">₹<?php echo number_format($analytics_data['overview']['total_revenue']/100000, 1); ?>L</div>
                        <div class="metric-label">Total Revenue</div>
                        <div class="metric-change change-positive">
                            <i class="fas fa-arrow-up me-1"></i>+12.5% from last month
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card metric-success">
                        <div class="metric-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="metric-value"><?php echo $analytics_data['overview']['total_properties_sold']; ?></div>
                        <div class="metric-label">Properties Sold</div>
                        <div class="metric-change change-positive">
                            <i class="fas fa-arrow-up me-1"></i>+8.2% from last month
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card metric-warning">
                        <div class="metric-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="metric-value"><?php echo $analytics_data['overview']['total_plots_sold']; ?></div>
                        <div class="metric-label">Plots Sold</div>
                        <div class="metric-change change-positive">
                            <i class="fas fa-arrow-up me-1"></i>+15.3% from last month
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card metric-info">
                        <div class="metric-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="metric-value"><?php echo $analytics_data['overview']['total_associates']; ?></div>
                        <div class="metric-label">Active Associates</div>
                        <div class="metric-change change-positive">
                            <i class="fas fa-arrow-up me-1"></i>+5.7% from last month
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sales Performance & Revenue Breakdown -->
    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="chart-container">
                        <h5><i class="fas fa-chart-bar me-2"></i>Sales Performance Trend</h5>
                        <canvas id="salesChart" width="400" height="200"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chart-container">
                        <h5><i class="fas fa-pie-chart me-2"></i>Revenue Breakdown</h5>
                        <div class="revenue-breakdown">
                            <?php
                            $total_revenue = $analytics_data['revenue_breakdown']['property_sales'] +
                                           $analytics_data['revenue_breakdown']['plot_sales'] +
                                           $analytics_data['revenue_breakdown']['commission_income'];

                            $property_percent = ($analytics_data['revenue_breakdown']['property_sales'] / $total_revenue) * 100;
                            $plot_percent = ($analytics_data['revenue_breakdown']['plot_sales'] / $total_revenue) * 100;
                            $commission_percent = ($analytics_data['revenue_breakdown']['commission_income'] / $total_revenue) * 100;
                            ?>
                            <div class="revenue-segment segment-property" style="height: <?php echo $property_percent; ?>%;">
                                <div class="segment-value">₹<?php echo number_format($analytics_data['revenue_breakdown']['property_sales']/100000, 1); ?>L</div>
                                <div class="segment-label">Properties</div>
                            </div>
                            <div class="revenue-segment segment-plot" style="height: <?php echo $plot_percent; ?>%;">
                                <div class="segment-value">₹<?php echo number_format($analytics_data['revenue_breakdown']['plot_sales']/100000, 1); ?>L</div>
                                <div class="segment-label">Plots</div>
                            </div>
                            <div class="revenue-segment segment-commission" style="height: <?php echo $commission_percent; ?>%;">
                                <div class="segment-value">₹<?php echo number_format($analytics_data['revenue_breakdown']['commission_income']/100000, 1); ?>L</div>
                                <div class="segment-label">Commission</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Performance & Associate Performance -->
    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="table-container">
                        <h5><i class="fas fa-project-diagram me-2"></i>Project Performance</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Sold</th>
                                        <th>Total</th>
                                        <th>Revenue</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics_data['project_performance'] as $project): ?>
                                        <?php $percentage = ($project['sold'] / $project['total']) * 100; ?>
                                        <tr>
                                            <td><?php echo $project['name']; ?></td>
                                            <td><?php echo $project['sold']; ?></td>
                                            <td><?php echo $project['total']; ?></td>
                                            <td>₹<?php echo number_format($project['revenue']/100000, 1); ?>L</td>
                                            <td>
                                                <div class="performance-bar">
                                                    <div class="performance-fill" style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                                <small><?php echo round($percentage, 1); ?>%</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="table-container">
                        <h5><i class="fas fa-user-tie me-2"></i>Top Associates</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Associate</th>
                                        <th>Sales</th>
                                        <th>Commission</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics_data['associate_performance'] as $associate): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="associate-avatar">
                                                        <?php echo substr($associate['name'], 0, 1); ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo $associate['name']; ?></div>
                                                        <small class="text-muted">Team: <?php echo $associate['team_size']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo $associate['sales']; ?></td>
                                            <td>₹<?php echo number_format($associate['commission']/1000, 0); ?>K</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Target Achievement -->
    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="target-progress">
                        <h5><i class="fas fa-bullseye me-2"></i>Yearly Target Achievement</h5>
                        <?php
                        $achievement_percentage = ($analytics_data['overview']['current_achievement'] / $analytics_data['overview']['yearly_target']) * 100;
                        ?>
                        <div class="progress mb-3">
                            <div class="progress-bar" style="width: <?php echo $achievement_percentage; ?>%"></div>
                        </div>
                        <div class="target-info">
                            <span class="target-achieved">Achieved: ₹<?php echo number_format($analytics_data['overview']['current_achievement']/100000, 1); ?>L</span>
                            <span class="target-remaining">Target: ₹<?php echo number_format($analytics_data['overview']['yearly_target']/100000, 1); ?>L</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="metric-value"><?php echo $analytics_data['overview']['customer_satisfaction']; ?>/5</div>
                        <div class="metric-label">Customer Satisfaction</div>
                        <div class="metric-change change-positive">
                            <i class="fas fa-arrow-up me-1"></i>Based on 500+ reviews
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Initialize AOS
        AOS.init();

        // Sample chart data (replace with real data)
        const salesData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Property Sales',
                data: [12, 19, 15, 25, 22, 30],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true
            }, {
                label: 'Plot Sales',
                data: [8, 12, 18, 20, 25, 28],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true
            }]
        };

        // Initialize sales chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Date filter functionality
        function applyCustomFilter() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate) {
                // Here you would fetch filtered data from the server
                alert(`Filtering data from ${startDate} to ${endDate}`);
            } else {
                alert('Please select both start and end dates');
            }
        }

        // Period filter buttons
        document.querySelectorAll('.btn-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const period = this.getAttribute('data-period');
                // Here you would fetch data for the selected period
                console.log('Selected period:', period);
            });
        });

        // Auto-refresh every 5 minutes
        setInterval(function() {
            console.log('Refreshing dashboard data...');
            // Here you would refresh the data
        }, 300000);
    </script>
</body>
</html>
