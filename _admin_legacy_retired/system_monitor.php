<?php
require_once '../includes/auth/auth_session.php';
require_once '../includes/db_settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitor - APS Dream Home</title>
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
                    <h1>System Monitor</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshStats()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportStats()">
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

                <!-- System Health Overview -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Follow-up System</h5>
                                <div class="d-flex align-items-center">
                                    <div class="system-status" id="followupStatus">
                                        <i class="fas fa-circle text-success"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="h2 mb-0" id="followupCount">0</div>
                                        <small class="text-muted">Processed Today</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Visit Scheduling</h5>
                                <div class="d-flex align-items-center">
                                    <div class="system-status" id="visitStatus">
                                        <i class="fas fa-circle text-success"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="h2 mb-0" id="visitCount">0</div>
                                        <small class="text-muted">Scheduled Today</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">AI Recommendations</h5>
                                <div class="d-flex align-items-center">
                                    <div class="system-status" id="aiStatus">
                                        <i class="fas fa-circle text-success"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="h2 mb-0" id="aiCount">0</div>
                                        <small class="text-muted">Generated Today</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Lead Processing</h5>
                                <div class="d-flex align-items-center">
                                    <div class="system-status" id="leadStatus">
                                        <i class="fas fa-circle text-success"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="h2 mb-0" id="leadCount">0</div>
                                        <small class="text-muted">Active Leads</small>
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
                                <h5 class="card-title">System Activity</h5>
                                <canvas id="activityChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Response Times</h5>
                                <canvas id="responseChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Logs -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">System Logs</h5>
                        <div class="table-responsive">
                            <table class="table table-hover" id="systemLogs">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>System</th>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Logs will be loaded here -->
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
        let activityChart, responseChart;
        let currentTimeRange = '24h';

        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            loadStats();
            loadLogs();
        });

        function initCharts() {
            // Activity Chart
            const activityCtx = document.getElementById('activityChart').getContext('2d');
            activityChart = new Chart(activityCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Follow-ups',
                        data: [],
                        borderColor: 'rgba(75, 192, 192, 1)'
                    }, {
                        label: 'Visits',
                        data: [],
                        borderColor: 'rgba(153, 102, 255, 1)'
                    }, {
                        label: 'AI Recommendations',
                        data: [],
                        borderColor: 'rgba(255, 159, 64, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Response Time Chart
            const responseCtx = document.getElementById('responseChart').getContext('2d');
            responseChart = new Chart(responseCtx, {
                type: 'bar',
                data: {
                    labels: ['Follow-ups', 'Visit Scheduling', 'AI Recommendations', 'Lead Processing'],
                    datasets: [{
                        label: 'Average Response Time (ms)',
                        data: [],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)',
                            'rgba(255, 159, 64, 0.5)',
                            'rgba(255, 99, 132, 0.5)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function loadStats() {
            fetch(`/apsdreamhome/api/system_stats.php?range=${currentTimeRange}`)
                .then(response => response.json())
                .then(data => {
                    updateDashboard(data);
                    updateCharts(data);
                })
                .catch(error => console.error('Error loading stats:', error));
        }

        function loadLogs() {
            fetch(`/apsdreamhome/api/system_logs.php?range=${currentTimeRange}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#systemLogs tbody');
                    tbody.innerHTML = data.logs.map(log => `
                        <tr>
                            <td>${log.timestamp}</td>
                            <td>${log.system}</td>
                            <td>${log.event}</td>
                            <td>
                                <span class="badge bg-${log.status === 'success' ? 'success' : 'danger'}">
                                    ${log.status}
                                </span>
                            </td>
                            <td>${log.details}</td>
                        </tr>
                    `).join('');
                })
                .catch(error => console.error('Error loading logs:', error));
        }

        function updateDashboard(data) {
            // Update counters
            document.getElementById('followupCount').textContent = data.followups.count;
            document.getElementById('visitCount').textContent = data.visits.count;
            document.getElementById('aiCount').textContent = data.ai.count;
            document.getElementById('leadCount').textContent = data.leads.count;

            // Update status indicators
            updateStatus('followupStatus', data.followups.status);
            updateStatus('visitStatus', data.visits.status);
            updateStatus('aiStatus', data.ai.status);
            updateStatus('leadStatus', data.leads.status);
        }

        function updateStatus(elementId, status) {
            const element = document.getElementById(elementId).querySelector('i');
            element.className = `fas fa-circle text-${status === 'healthy' ? 'success' : 'danger'}`;
        }

        function updateCharts(data) {
            // Update Activity Chart
            activityChart.data.labels = data.timeline.labels;
            activityChart.data.datasets[0].data = data.timeline.followups;
            activityChart.data.datasets[1].data = data.timeline.visits;
            activityChart.data.datasets[2].data = data.timeline.ai;
            activityChart.update();

            // Update Response Time Chart
            responseChart.data.datasets[0].data = [
                data.performance.followups,
                data.performance.visits,
                data.performance.ai,
                data.performance.leads
            ];
            responseChart.update();
        }

        function changeTimeRange(range) {
            currentTimeRange = range;
            refreshStats();
        }

        function refreshStats() {
            loadStats();
            loadLogs();
        }

        function exportStats() {
            window.location.href = `/apsdreamhome/api/export_stats.php?range=${currentTimeRange}`;
        }

        // Refresh every 5 minutes
        setInterval(refreshStats, 300000);
    </script>
</body>
</html>
