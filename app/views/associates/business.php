<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Business Overview</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Business Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format($business_stats['personal']['total_sales_value'] ?? 0) ?>
                            </div>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> +<?= $business_stats['personal']['completed_sales'] ?? 0 ?> Completed
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Team Performance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $business_stats['team']['total_team_members'] ?? 0 ?> Members
                            </div>
                            <small class="text-info">
                                <i class="fas fa-users"></i> <?= $business_stats['team']['direct_members'] ?? 0 ?> Direct
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Commission Earnings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format($commission_summary['total_commissions'] ?? 0) ?>
                            </div>
                            <small class="text-warning">
                                <i class="fas fa-coins"></i> From various levels
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Monthly Growth</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $monthlyData = $business_stats['monthly'] ?? [];
                                $growth = 0;
                                if (count($monthlyData) >= 2) {
                                    $current = end($monthlyData);
                                    $previous = prev($monthlyData);
                                    if ($previous && $previous['sales_value'] > 0) {
                                        $growth = (($current['sales_value'] - $previous['sales_value']) / $previous['sales_value']) * 100;
                                    }
                                }
                                echo number_format($growth, 1) . '%';
                                ?>
                            </div>
                            <small class="text-muted">पिछले महीने से</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Monthly Sales Trend -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">मासिक सेल्स ट्रेंड</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="#" onclick="exportChart('salesTrend')">एक्सपोर्ट PDF</a>
                            <a class="dropdown-item" href="#" onclick="exportChart('salesTrend', 'csv')">एक्सपोर्ट CSV</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Breakdown -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">कमिशन ब्रेकडाउन</h6>
                </div>
                <div class="card-body">
                    <div class="commission-breakdown">
                        <?php if (!empty($commission_summary)): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>लेवल 1 कमिशन</span>
                                    <span class="font-weight-bold">₹<?= number_format($commission_summary['level_1_earnings'] ?? 0) ?></span>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                         style="width: <?= $commission_summary['total_commissions'] > 0 ? (($commission_summary['level_1_earnings'] ?? 0) / $commission_summary['total_commissions']) * 100 : 0 ?>%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>लेवल 2 कमिशन</span>
                                    <span class="font-weight-bold">₹<?= number_format($commission_summary['level_2_earnings'] ?? 0) ?></span>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: <?= $commission_summary['total_commissions'] > 0 ? (($commission_summary['level_2_earnings'] ?? 0) / $commission_summary['total_commissions']) * 100 : 0 ?>%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>लेवल 3 कमिशन</span>
                                    <span class="font-weight-bold">₹<?= number_format($commission_summary['level_3_earnings'] ?? 0) ?></span>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-info" role="progressbar"
                                         style="width: <?= $commission_summary['total_commissions'] > 0 ? (($commission_summary['level_3_earnings'] ?? 0) / $commission_summary['total_commissions']) * 100 : 0 ?>%"></div>
                                </div>
                            </div>

                            <hr>
                            <div class="text-center">
                                <strong>टोटल कमिशन: ₹<?= number_format($commission_summary['total_commissions'] ?? 0) ?></strong>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted">कोई कमिशन डेटा नहीं</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance and Top Performers -->
    <div class="row mb-4">
        <!-- Team Performance Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">टीम परफॉर्मेंस डिस्ट्रीब्यूशन</h6>
                </div>
                <div class="card-body">
                    <canvas id="teamPerformanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy mr-2"></i>टॉप परफॉर्मर्स
                    </h6>
                    <a href="/associate/team" class="btn btn-sm btn-outline-primary">सभी देखें</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($top_performers)): ?>
                        <?php foreach ($top_performers as $index => $performer): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <span class="badge badge-primary badge-pill">
                                        #<?= $index + 1 ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">
                                        <?= htmlspecialchars($performer['name']) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= $performer['total_sales'] ?> सेल्स • लेवल <?= $performer['level'] ?? 1 ?>
                                    </small>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold text-success">
                                        ₹<?= number_format($performer['total_earnings']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">कोई टीम परफॉर्मेंस डेटा नहीं</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Insights -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb mr-2"></i>बिजनेस इनसाइट्स
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="insight-item">
                                <h6 class="text-success">
                                    <i class="fas fa-arrow-up mr-2"></i>स्ट्रेंथ्स
                                </h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success mr-2"></i>कंसिस्टेंट मंथली ग्रोथ</li>
                                    <li><i class="fas fa-check text-success mr-2"></i>स्ट्रॉन्ग टीम बिल्डिंग</li>
                                    <li><i class="fas fa-check text-success mr-2"></i>हाई कन्वर्शन रेट</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="insight-item">
                                <h6 class="text-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>इंप्रूवमेंट एरियाज
                                </h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-circle text-warning mr-2"></i>टीम एक्टिवेशन रेट बढ़ाएं</li>
                                    <li><i class="fas fa-circle text-warning mr-2"></i>डाउनलाइन डेवलपमेंट फोकस करें</li>
                                    <li><i class="fas fa-circle text-warning mr-2"></i>क्रॉस सेलिंग अपॉर्चुनिटीज</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle mr-2"></i>नेक्स्ट मंथ टार्गेट्स</h6>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <strong>पर्सनल सेल्स:</strong> ₹2,00,000
                                    </div>
                                    <div class="col-md-3">
                                        <strong>टीम मेंबर्स:</strong> +5 नए
                                    </div>
                                    <div class="col-md-3">
                                        <strong>कमिशन टार्गेट:</strong> ₹50,000
                                    </div>
                                    <div class="col-md-3">
                                        <strong>ग्रोथ रेट:</strong> 25%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart
    var salesCtx = document.getElementById('salesTrendChart').getContext('2d');
    var monthlyTrends = <?= json_encode($monthly_trends ?? []) ?>;

    var months = [];
    var salesValues = [];

    monthlyTrends.forEach(function(trend) {
        months.push(trend.month);
        salesValues.push(parseFloat(trend.sales_value));
    });

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'मासिक सेल्स (₹)',
                data: salesValues,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });

    // Team Performance Chart
    var teamCtx = document.getElementById('teamPerformanceChart').getContext('2d');

    // Sample data - replace with actual team performance data
    var teamData = {
        labels: ['लेवल 1', 'लेवल 2', 'लेवल 3', 'लेवल 4', 'लेवल 5'],
        datasets: [{
            label: 'टीम मेंबर्स',
            data: [12, 8, 5, 3, 2], // Replace with actual data
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ],
            borderWidth: 1
        }]
    };

    new Chart(teamCtx, {
        type: 'doughnut',
        data: teamData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});

function exportChart(chartId, format = 'pdf') {
    // This would implement export functionality
    alert('एक्सपोर्ट फीचर जल्द आ रहा है!');
}
</script>

<style>
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.chart-area {
    position: relative;
    height: 300px;
}

.commission-breakdown {
    max-height: 300px;
    overflow-y: auto;
}

.insight-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
}

.insight-item ul li {
    margin-bottom: 8px;
}

.progress {
    height: 8px;
    border-radius: 4px;
}

.progress-bar {
    border-radius: 4px;
}

.alert {
    border-radius: 10px;
}

.badge-pill {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8em;
}

.text-gray-800 {
    color: #5a5c69 !important;
}
</style>

<?php require_once 'app/views/layouts/associate_footer.php'; ?>
