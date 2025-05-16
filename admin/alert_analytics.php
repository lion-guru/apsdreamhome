<?php
require_once '../includes/auth/auth_session.php';
require_once '../includes/db_settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Analytics - APS Dream Home</title>
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
                    <h1>Alert Analytics</h1>
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

                <!-- Alert Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card h-100 border-danger">
                            <div class="card-body">
                                <h5 class="card-title text-danger">Critical Alerts</h5>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0" id="criticalCount">0</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="criticalTrend">0% from last period</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning">Warning Alerts</h5>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0" id="warningCount">0</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="warningTrend">0% from last period</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Resolution Time</h5>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0" id="avgResolutionTime">0m</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="resolutionTrend">0% from last period</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Alert Rate</h5>
                                <div class="d-flex align-items-center">
                                    <div class="h2 mb-0" id="alertRate">0/hr</div>
                                    <div class="ms-3">
                                        <small class="text-muted" id="alertRateTrend">0% from last period</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Trends Chart -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Alert Trends</h5>
                                <canvas id="alertTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Alert Distribution</h5>
                                <canvas id="alertDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Performance -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Response Time by System</h5>
                                <canvas id="responseTimeChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Error Rate by System</h5>
                                <canvas id="errorRateChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Issues Table -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Top Issues</h5>
                        <div class="table-responsive">
                            <table class="table table-hover" id="topIssues">
                                <thead>
                                    <tr>
                                        <th>System</th>
                                        <th>Issue</th>
                                        <th>Occurrences</th>
                                        <th>Avg Resolution Time</th>
                                        <th>Last Occurrence</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Issues will be loaded here -->
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
        let alertTrendsChart, alertDistributionChart, responseTimeChart, errorRateChart;

        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            loadAnalytics();
        });

        function initCharts() {
            // Alert Trends Chart
            const trendsCtx = document.getElementById('alertTrendsChart').getContext('2d');
            alertTrendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Critical',
                        data: [],
                        borderColor: 'rgba(220, 53, 69, 1)',
                        fill: false
                    }, {
                        label: 'Warning',
                        data: [],
                        borderColor: 'rgba(255, 193, 7, 1)',
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Alert Distribution Chart
            const distributionCtx = document.getElementById('alertDistributionChart').getContext('2d');
            alertDistributionChart = new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Critical', 'Warning', 'Info'],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(23, 162, 184, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Response Time Chart
            const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
            responseTimeChart = new Chart(responseCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Average Response Time (ms)',
                        data: [],
                        backgroundColor: 'rgba(75, 192, 192, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Error Rate Chart
            const errorCtx = document.getElementById('errorRateChart').getContext('2d');
            errorRateChart = new Chart(errorCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Error Rate (%)',
                        data: [],
                        backgroundColor: 'rgba(255, 99, 132, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function loadAnalytics() {
            fetch(`/apsdreamhomefinal/api/alert_analytics.php?range=${currentTimeRange}`)
                .then(response => response.json())
                .then(data => {
                    updateDashboard(data);
                    updateCharts(data);
                    updateTopIssues(data.topIssues);
                })
                .catch(error => console.error('Error loading analytics:', error));
        }

        function updateDashboard(data) {
            // Update summary cards
            document.getElementById('criticalCount').textContent = data.summary.critical;
            document.getElementById('warningCount').textContent = data.summary.warning;
            document.getElementById('avgResolutionTime').textContent = data.summary.avgResolutionTime;
            document.getElementById('alertRate').textContent = data.summary.alertRate;

            // Update trends
            updateTrend('criticalTrend', data.trends.critical);
            updateTrend('warningTrend', data.trends.warning);
            updateTrend('resolutionTrend', data.trends.resolution);
            updateTrend('alertRateTrend', data.trends.alertRate);
        }

        function updateTrend(elementId, trend) {
            const element = document.getElementById(elementId);
            const trendValue = parseFloat(trend);
            element.textContent = `${Math.abs(trendValue)}% ${trendValue >= 0 ? 'increase' : 'decrease'}`;
            element.className = `text-${trendValue > 0 ? 'danger' : 'success'}`;
        }

        function updateCharts(data) {
            // Update Alert Trends Chart
            alertTrendsChart.data.labels = data.charts.trends.labels;
            alertTrendsChart.data.datasets[0].data = data.charts.trends.critical;
            alertTrendsChart.data.datasets[1].data = data.charts.trends.warning;
            alertTrendsChart.update();

            // Update Alert Distribution Chart
            alertDistributionChart.data.datasets[0].data = [
                data.charts.distribution.critical,
                data.charts.distribution.warning,
                data.charts.distribution.info
            ];
            alertDistributionChart.update();

            // Update Response Time Chart
            responseTimeChart.data.labels = data.charts.responseTimes.labels;
            responseTimeChart.data.datasets[0].data = data.charts.responseTimes.values;
            responseTimeChart.update();

            // Update Error Rate Chart
            errorRateChart.data.labels = data.charts.errorRates.labels;
            errorRateChart.data.datasets[0].data = data.charts.errorRates.values;
            errorRateChart.update();
        }

        function updateTopIssues(issues) {
            const tbody = document.querySelector('#topIssues tbody');
            tbody.innerHTML = issues.map(issue => `
                <tr>
                    <td>${issue.system}</td>
                    <td>${issue.issue}</td>
                    <td>${issue.occurrences}</td>
                    <td>${issue.avgResolutionTime}</td>
                    <td>${issue.lastOccurrence}</td>
                    <td>
                        <span class="text-${issue.trend > 0 ? 'danger' : 'success'}">
                            ${Math.abs(issue.trend)}%
                            <i class="fas fa-arrow-${issue.trend > 0 ? 'up' : 'down'}"></i>
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
            window.location.href = `/apsdreamhomefinal/api/export_alert_analytics.php?range=${currentTimeRange}`;
        }

        // Refresh every 5 minutes
        setInterval(refreshAnalytics, 300000);
    </script>
</body>
</html>
