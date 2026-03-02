<?php
/**
 * APS Dream Home - Monitoring Dashboard
 */

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Monitoring/APM.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Monitoring Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .metric-card { transition: all 0.3s ease; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .status-good { border-left: 4px solid #28a745; }
        .status-warning { border-left: 4px solid #ffc107; }
        .status-danger { border-left: 4px solid #dc3545; }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">📊 APS Dream Home - Monitoring Dashboard</span>
            <span class="navbar-text">Last Updated: <span id="lastUpdate">-</span></span>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card metric-card status-good">
                    <div class="card-body">
                        <h5 class="card-title">Requests/min</h5>
                        <h2 class="text-primary" id="requestsPerMinute">-</h2>
                        <small class="text-muted">Total requests in last minute</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card status-good">
                    <div class="card-body">
                        <h5 class="card-title">Avg Response Time</h5>
                        <h2 class="text-success" id="avgResponseTime">-</h2>
                        <small class="text-muted">Milliseconds</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card status-good">
                    <div class="card-body">
                        <h5 class="card-title">Memory Usage</h5>
                        <h2 class="text-info" id="memoryUsage">-</h2>
                        <small class="text-muted">Current usage</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card status-good">
                    <div class="card-body">
                        <h5 class="card-title">Error Rate</h5>
                        <h2 class="text-danger" id="errorRate">-</h2>
                        <small class="text-muted">Errors per minute</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Response Time Trend</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="responseTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>System Resources</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="resourceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize dashboard
        let responseTimeChart, resourceChart;
        
        function initCharts() {
            // Response Time Chart
            const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
            responseTimeChart = new Chart(responseTimeCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
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
            
            // Resource Chart
            const resourceCtx = document.getElementById('resourceChart').getContext('2d');
            resourceChart = new Chart(resourceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['CPU', 'Memory', 'Disk'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function updateDashboard() {
            fetch('/monitoring_data.php')
                .then(response => response.json())
                .then(data => {
                    // Update metrics
                    document.getElementById('requestsPerMinute').textContent = data.requests_per_minute || 0;
                    document.getElementById('avgResponseTime').textContent = data.avg_response_time || 0;
                    document.getElementById('memoryUsage').textContent = data.memory_usage || '0 MB';
                    document.getElementById('errorRate').textContent = data.error_rate || 0;
                    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
                    
                    // Update charts
                    updateCharts(data);
                })
                .catch(error => console.error('Error updating dashboard:', error));
        }
        
        function updateCharts(data) {
            // Update response time chart
            if (responseTimeChart && data.response_time_history) {
                responseTimeChart.data.labels = data.response_time_history.map((_, i) => i + 's ago');
                responseTimeChart.data.datasets[0].data = data.response_time_history;
                responseTimeChart.update();
            }
            
            // Update resource chart
            if (resourceChart && data.system_resources) {
                resourceChart.data.datasets[0].data = [
                    data.system_resources.cpu || 0,
                    data.system_resources.memory || 0,
                    data.system_resources.disk || 0
                ];
                resourceChart.update();
            }
        }
        
        // Initialize and start auto-refresh
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            updateDashboard();
            setInterval(updateDashboard, 5000); // Update every 5 seconds
        });
    </script>
</body>
</html>
