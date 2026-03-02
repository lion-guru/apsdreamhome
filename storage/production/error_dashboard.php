<?php
/**
 * APS Dream Home - Error Dashboard
 */

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Monitoring/ErrorTracker.php';
require_once APP_PATH . '/Monitoring/AlertingSystem.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Error Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .error-card { transition: all 0.3s ease; }
        .error-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .severity-critical { border-left: 4px solid #dc3545; }
        .severity-high { border-left: 4px solid #fd7e14; }
        .severity-medium { border-left: 4px solid #ffc107; }
        .severity-low { border-left: 4px solid #28a745; }
        .chart-container { position: relative; height: 300px; }
        .error-details { max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">🚨 APS Dream Home - Error Dashboard</span>
            <span class="navbar-text">Last Updated: <span id="lastUpdate">-</span></span>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card error-card severity-critical">
                    <div class="card-body">
                        <h5 class="card-title">Critical Errors</h5>
                        <h2 class="text-danger" id="criticalErrors">-</h2>
                        <small class="text-muted">Last 24 hours</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card error-card severity-high">
                    <div class="card-body">
                        <h5 class="card-title">High Errors</h5>
                        <h2 class="text-warning" id="highErrors">-</h2>
                        <small class="text-muted">Last 24 hours</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card error-card severity-medium">
                    <div class="card-body">
                        <h5 class="card-title">Medium Errors</h5>
                        <h2 class="text-info" id="mediumErrors">-</h2>
                        <small class="text-muted">Last 24 hours</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card error-card severity-low">
                    <div class="card-body">
                        <h5 class="card-title">Low Errors</h5>
                        <h2 class="text-success" id="lowErrors">-</h2>
                        <small class="text-muted">Last 24 hours</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Error Trends</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="errorTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Error Types</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="errorTypesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Errors</h5>
                    </div>
                    <div class="card-body error-details">
                        <div id="recentErrors">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize dashboard
        let errorTrendsChart, errorTypesChart;
        
        function initCharts() {
            // Error Trends Chart
            const trendsCtx = document.getElementById('errorTrendsChart').getContext('2d');
            errorTrendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Errors per Hour',
                        data: [],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Error Types Chart
            const typesCtx = document.getElementById('errorTypesChart').getContext('2d');
            errorTypesChart = new Chart(typesCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function updateDashboard() {
            fetch('/error_data.php')
                .then(response => response.json())
                .then(data => {
                    // Update metrics
                    document.getElementById('criticalErrors').textContent = data.critical_errors || 0;
                    document.getElementById('highErrors').textContent = data.high_errors || 0;
                    document.getElementById('mediumErrors').textContent = data.medium_errors || 0;
                    document.getElementById('lowErrors').textContent = data.low_errors || 0;
                    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
                    
                    // Update charts
                    updateCharts(data);
                    
                    // Update recent errors
                    updateRecentErrors(data.recent_errors);
                })
                .catch(error => console.error('Error updating dashboard:', error));
        }
        
        function updateCharts(data) {
            // Update error trends chart
            if (errorTrendsChart && data.error_trends) {
                errorTrendsChart.data.labels = Object.keys(data.error_trends);
                errorTrendsChart.data.datasets[0].data = Object.values(data.error_trends);
                errorTrendsChart.update();
            }
            
            // Update error types chart
            if (errorTypesChart && data.error_types) {
                errorTypesChart.data.labels = Object.keys(data.error_types);
                errorTypesChart.data.datasets[0].data = Object.values(data.error_types);
                errorTypesChart.update();
            }
        }
        
        function updateRecentErrors(errors) {
            const container = document.getElementById('recentErrors');
            
            if (!errors || errors.length === 0) {
                container.innerHTML = '<p class="text-muted">No recent errors</p>';
                return;
            }
            
            let html = '<div class="table-responsive"><table class="table table-sm">';
            html += '<thead><tr><th>Time</th><th>Type</th><th>Severity</th><th>Error</th><th>URI</th></tr></thead><tbody>';
            
            errors.forEach(error => {
                const severityClass = 'severity-' + error.severity;
                html += '<tr class="' + severityClass + '">';
                html += '<td>' + error.timestamp + '</td>';
                html += '<td>' + error.type + '</td>';
                html += '<td><span class="badge bg-' + getSeverityColor(error.severity) + '">' + error.severity.toUpperCase() + '</span></td>';
                html += '<td>' + error.error.substring(0, 100) + (error.error.length > 100 ? '...' : '') + '</td>';
                html += '<td>' + error.context.uri + '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        }
        
        function getSeverityColor(severity) {
            switch(severity) {
                case 'critical': return 'danger';
                case 'high': return 'warning';
                case 'medium': return 'info';
                case 'low': return 'success';
                default: return 'secondary';
            }
        }
        
        // Initialize and start auto-refresh
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            updateDashboard();
            setInterval(updateDashboard, 10000); // Update every 10 seconds
        });
    </script>
</body>
</html>
