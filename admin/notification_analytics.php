<?php
require_once '../includes/auth/auth_session.php';
require_once '../includes/db_settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Analytics - APS Dream Home</title>
    <?php include '../includes/templates/header_links.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
</head>
<body class="admin-dashboard">
    <?php include '../includes/templates/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/templates/admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Notification Analytics</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshAnalytics()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportAnalytics()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="far fa-calendar-alt"></i> Time Range
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="changeTimeRange('24h')">Last 24 Hours</a></li>
                                <li><a class="dropdown-item" href="#" onclick="changeTimeRange('7d')">Last 7 Days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="changeTimeRange('30d')">Last 30 Days</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Total Notifications</h5>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0" id="totalCount">0</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="totalTrend">0% from last period</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Success Rate</h5>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0" id="successRate">0%</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="successTrend">0% from last period</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">SMS Delivery</h5>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0" id="smsRate">0%</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="smsTrend">0% from last period</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Queue Health</h5>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0" id="queueHealth">Healthy</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="queuePending">0 pending</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Notification Volume</h5>
                                <canvas id="volumeChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Notification Types</h5>
                                <canvas id="typesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Performance -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Delivery Performance</h5>
                                <div class="table-responsive">
                                    <table class="table" id="deliveryStats">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Total Sent</th>
                                                <th>Success Rate</th>
                                                <th>Avg. Delivery Time</th>
                                                <th>Failure Rate</th>
                                                <th>Top Error</th>
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
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Recent Failures</h5>
                        <div class="table-responsive">
                            <table class="table" id="recentFailures">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Recipient</th>
                                        <th>Error</th>
                                        <th>Attempts</th>
                                        <th>Status</th>
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

    <?php include '../includes/templates/admin_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
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
                        label: 'Total Notifications',
                        data: [],
                        borderColor: 'rgba(75, 192, 192, 1)',
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
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
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function loadAnalytics() {
            fetch(`/apsdreamhome/api/notification_analytics.php?range=${currentTimeRange}`)
                .then(response => response.json())
                .then(data => {
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
            document.getElementById('queueHealth').textContent = data.summary.queueHealth;
            document.getElementById('queuePending').textContent = data.summary.queuePending + ' pending';

            // Update trends
            updateTrend('totalTrend', data.trends.total);
            updateTrend('successTrend', data.trends.success);
            updateTrend('smsTrend', data.trends.sms);
        }

        function updateTrend(elementId, trend) {
            const element = document.getElementById(elementId);
            const trendValue = parseFloat(trend);
            element.textContent = `${Math.abs(trendValue)}% ${trendValue >= 0 ? 'increase' : 'decrease'}`;
            element.className = `text-${trendValue > 0 ? 'success' : 'danger'}`;
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
                    <td>${stat.type}</td>
                    <td>${stat.total}</td>
                    <td>${stat.successRate}%</td>
                    <td>${stat.avgDeliveryTime}ms</td>
                    <td>${stat.failureRate}%</td>
                    <td>${stat.topError}</td>
                </tr>
            `).join('');

            // Update Recent Failures Table
            const recentFailures = document.querySelector('#recentFailures tbody');
            recentFailures.innerHTML = data.recentFailures.map(failure => `
                <tr>
                    <td>${failure.time}</td>
                    <td>${failure.type}</td>
                    <td>${failure.recipient}</td>
                    <td>${failure.error}</td>
                    <td>${failure.attempts}</td>
                    <td>
                        <span class="badge bg-${failure.status === 'resolved' ? 'success' : 'danger'}">
                            ${failure.status}
                        </span>
                    </td>
                </tr>
            `).join('');
        }

        function changeTimeRange(range) {
            currentTimeRange = range;
            refreshAnalytics();
        }

        function refreshAnalytics() {
            loadAnalytics();
        }

        function exportAnalytics() {
            window.location.href = `/apsdreamhome/api/export_notification_analytics.php?range=${currentTimeRange}`;
        }

        // Refresh every 5 minutes
        setInterval(refreshAnalytics, 300000);
    </script>
</body>
</html>
