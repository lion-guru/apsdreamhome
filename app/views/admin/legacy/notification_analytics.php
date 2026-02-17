<?php
require_once __DIR__ . '/core/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mlSupport->translate('Notification Analytics'); ?> - APS Dream Home</title>
    <link rel="stylesheet" href="<?= get_admin_asset_url('chart.min.css', 'css') ?>">
</head>
<body class="admin-dashboard">
    <?php include 'admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>

            <main class="page-wrapper">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo $mlSupport->translate('Notification Analytics'); ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshAnalytics()">
                                <i class="fas fa-sync-alt me-1"></i> <?php echo $mlSupport->translate('Refresh'); ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportAnalytics()">
                                <i class="fas fa-download me-1"></i> <?php echo $mlSupport->translate('Export'); ?>
                            </button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="far fa-calendar-alt me-1"></i> <?php echo $mlSupport->translate('Time Range'); ?>
                            </button>
                            <ul class="dropdown-menu shadow-sm border-0">
                                <li><a class="dropdown-item" href="#" onclick="changeTimeRange('24h')"><?php echo $mlSupport->translate('Last 24 Hours'); ?></a></li>
                                <li><a class="dropdown-item" href="#" onclick="changeTimeRange('7d')"><?php echo $mlSupport->translate('Last 7 Days'); ?></a></li>
                                <li><a class="dropdown-item" href="#" onclick="changeTimeRange('30d')"><?php echo $mlSupport->translate('Last 30 Days'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $mlSupport->translate('Total Notifications'); ?></h6>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0 fw-bold" id="totalCount">0</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="totalTrend">0% <?php echo $mlSupport->translate('from last period'); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $mlSupport->translate('Success Rate'); ?></h6>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0 fw-bold" id="successRate">0%</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="successTrend">0% <?php echo $mlSupport->translate('from last period'); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $mlSupport->translate('SMS Delivery'); ?></h6>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0 fw-bold" id="smsRate">0%</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="smsTrend">0% <?php echo $mlSupport->translate('from last period'); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $mlSupport->translate('Queue Health'); ?></h6>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0 fw-bold" id="queueHealth"><?php echo $mlSupport->translate('Healthy'); ?></div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="queuePending">0 <?php echo $mlSupport->translate('pending'); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4 g-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Notification Volume'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px;">
                                    <canvas id="volumeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Notification Types'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px;">
                                    <canvas id="typesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Performance -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Delivery Performance'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="deliveryStats">
                                        <thead class="table-light">
                                            <tr>
                                                <th><?php echo $mlSupport->translate('Type'); ?></th>
                                                <th><?php echo $mlSupport->translate('Total Sent'); ?></th>
                                                <th><?php echo $mlSupport->translate('Success Rate'); ?></th>
                                                <th><?php echo $mlSupport->translate('Avg. Delivery Time'); ?></th>
                                                <th><?php echo $mlSupport->translate('Failure Rate'); ?></th>
                                                <th><?php echo $mlSupport->translate('Top Error'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Delivery stats will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Failures -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Recent Failures'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="recentFailures">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo $mlSupport->translate('Time'); ?></th>
                                        <th><?php echo $mlSupport->translate('Type'); ?></th>
                                        <th><?php echo $mlSupport->translate('Recipient'); ?></th>
                                        <th><?php echo $mlSupport->translate('Error'); ?></th>
                                        <th><?php echo $mlSupport->translate('Attempts'); ?></th>
                                        <th><?php echo $mlSupport->translate('Status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Recent failures will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php require_once __DIR__ . '/admin_footer.php'; ?>
    <script src="<?= get_admin_asset_url('chart.min.js', 'js') ?>"></script>
    <script>
        // Standard escaping function
        function h(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        let currentTimeRange = '24h';
        let volumeChart, typesChart;

        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            loadAnalytics();
        });

        function initCharts() {
            // Volume Chart
            const volumeCtx = document.getElementById('volumeChart').getContext('2d');
            volumeChart = new Chart(volumeCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: '<?php echo $mlSupport->translate('Total Notifications'); ?>',
                        data: [],
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Types Chart
            const typesCtx = document.getElementById('typesChart').getContext('2d');
            typesChart = new Chart(typesCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        function loadAnalytics() {
            fetch(`../api/notification_analytics.php?range=${currentTimeRange}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('API Error:', data.error);
                        return;
                    }
                    updateDashboard(data);
                    updateCharts(data);
                    updateTables(data);
                })
                .catch(error => console.error('Error loading analytics:', error));
        }

        function updateDashboard(data) {
            // Update summary cards
            document.getElementById('totalCount').textContent = data.summary.total;
            document.getElementById('successRate').textContent = data.summary.successRate + '%';
            document.getElementById('smsRate').textContent = data.summary.smsRate + '%';
            
            const queueHealth = document.getElementById('queueHealth');
            queueHealth.textContent = data.summary.queueHealth;
            queueHealth.className = 'h2 mb-0 fw-bold ' + 
                (data.summary.queueHealth === 'Healthy' ? 'text-success' : 
                 (data.summary.queueHealth === 'Critical' ? 'text-danger' : 'text-warning'));
            
            document.getElementById('queuePending').textContent = data.summary.queuePending + ' <?php echo $mlSupport->translate('pending'); ?>';

            // Update trends
            updateTrend('totalTrend', data.trends.total);
            updateTrend('successTrend', data.trends.success);
            updateTrend('smsTrend', data.trends.sms);
        }

        function updateTrend(elementId, trend) {
            const element = document.getElementById(elementId);
            const trendValue = parseFloat(trend);
            const absTrend = Math.abs(trendValue);
            const direction = trendValue >= 0 ? '<?php echo $mlSupport->translate('increase'); ?>' : '<?php echo $mlSupport->translate('decrease'); ?>';
            
            element.textContent = `${absTrend}% ${direction}`;
            element.className = `small fw-bold text-${trendValue >= 0 ? 'success' : 'danger'}`;
        }

        function updateCharts(data) {
            // Update Volume Chart
            volumeChart.data.labels = data.charts.volume.labels;
            volumeChart.data.datasets[0].data = data.charts.volume.data;
            volumeChart.update();

            // Update Types Chart
            typesChart.data.labels = data.charts.types.labels;
            typesChart.data.datasets[0].data = data.charts.types.data;
            typesChart.update();
        }

        function updateTables(data) {
            // Update Delivery Stats Table
            const deliveryStats = document.querySelector('#deliveryStats tbody');
            deliveryStats.innerHTML = data.deliveryStats.map(stat => `
                <tr>
                    <td class="fw-bold text-capitalize">${h(stat.type)}</td>
                    <td>${stat.total}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="me-2">${stat.successRate}%</span>
                            <div class="progress flex-grow-1" style="height: 4px; width: 60px;">
                                <div class="progress-bar bg-success" style="width: ${stat.successRate}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>${stat.avgDeliveryTime}ms</td>
                    <td><span class="text-danger">${stat.failureRate}%</span></td>
                    <td class="small text-muted">${h(stat.topError)}</td>
                </tr>
            `).join('');

            // Update Recent Failures Table
            const recentFailures = document.querySelector('#recentFailures tbody');
            if (data.recentFailures.length === 0) {
                recentFailures.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><?php echo $mlSupport->translate('No recent failures found'); ?></td></tr>';
            } else {
                recentFailures.innerHTML = data.recentFailures.map(failure => `
                    <tr>
                        <td class="small">${h(failure.time)}</td>
                        <td><span class="badge bg-light text-dark text-capitalize border">${h(failure.type)}</span></td>
                        <td class="small">${h(failure.recipient)}</td>
                        <td class="small text-danger">${h(failure.error)}</td>
                        <td>${failure.attempts}</td>
                        <td>
                            <span class="badge bg-${failure.status === 'resolved' ? 'success' : 'danger'} rounded-pill">
                                ${h(failure.status)}
                            </span>
                        </td>
                    </tr>
                `).join('');
            }
        }

        function changeTimeRange(range) {
            currentTimeRange = range;
            refreshAnalytics();
        }

        function refreshAnalytics() {
            loadAnalytics();
        }

        function exportAnalytics() {
            window.location.href = `../api/export_notification_analytics.php?range=${currentTimeRange}`;
        }

        // Refresh every 5 minutes
        setInterval(refreshAnalytics, 300000);
    </script>
</body>
</html>

