<?php
/**
 * API Analytics Dashboard
 * View and analyze API usage patterns and performance metrics
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/analytics/api_analytics.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['auser'])) {
    header('location:index.php');
    exit();
}

// Apply rate limiting
$rateLimitMiddleware->handle('admin');

// Initialize analytics
$analytics = new ApiAnalytics();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'request_volume':
            $interval = $_GET['interval'] ?? 'hour';
            $limit = (int)($_GET['limit'] ?? 24);
            $data = $analytics->getRequestVolume($interval, $limit);
            echo json_encode($data);
            break;
            
        case 'endpoint_metrics':
            $days = (int)($_GET['days'] ?? 7);
            $data = $analytics->getEndpointMetrics($days);
            echo json_encode($data);
            break;
            
        case 'user_metrics':
            $days = (int)($_GET['days'] ?? 7);
            $data = $analytics->getUserMetrics($days);
            echo json_encode($data);
            break;
            
        case 'error_metrics':
            $days = (int)($_GET['days'] ?? 7);
            $data = $analytics->getErrorMetrics($days);
            echo json_encode($data);
            break;
            
        case 'realtime':
            $minutes = (int)($_GET['minutes'] ?? 5);
            $data = $analytics->getRealTimeMetrics($minutes);
            echo json_encode($data);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Analytics - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .metric-card {
            margin-bottom: 20px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .metric-value {
            font-size: 2em;
            font-weight: bold;
        }
        .metric-label {
            font-size: 0.9em;
            color: #6c757d;
        }
        .error-badge {
            font-size: 0.8em;
        }
        .realtime-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .realtime-active {
            background-color: #198754;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <?php include("../includes/templates/dynamic_header.php"); ?>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>API Analytics</h1>
            <div>
                <span class="realtime-indicator realtime-active"></span>
                Real-time Monitoring
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Total Requests (24h)</h6>
                        <div class="metric-value" id="totalRequests">-</div>
                        <div class="metric-label">requests</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Avg Response Time (24h)</h6>
                        <div class="metric-value" id="avgResponseTime">-</div>
                        <div class="metric-label">milliseconds</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Error Rate (24h)</h6>
                        <div class="metric-value" id="errorRate">-</div>
                        <div class="metric-label">percent</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Active Users (24h)</h6>
                        <div class="metric-value" id="activeUsers">-</div>
                        <div class="metric-label">unique users</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Real-time Monitor -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Real-time Request Monitor</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="realtimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Request Volume -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Request Volume</span>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-sm interval-btn active" 
                                        data-interval="hour">Hourly</button>
                                <button class="btn btn-outline-secondary btn-sm interval-btn" 
                                        data-interval="day">Daily</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="volumeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Endpoint Performance -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Endpoint Performance</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="endpointTable">
                                <thead>
                                    <tr>
                                        <th>Endpoint</th>
                                        <th>Requests</th>
                                        <th>Avg Response Time</th>
                                        <th>Error Rate</th>
                                        <th>Unique Users</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Error Analysis -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Error Distribution</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="errorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Top Error Codes</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="errorTable">
                                <thead>
                                    <tr>
                                        <th>Status Code</th>
                                        <th>Count</th>
                                        <th>Affected Users</th>
                                        <th>Affected Endpoints</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("../includes/templates/new_footer.php"); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        // Chart configurations
        const chartConfigs = {
            realtime: {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Requests/Second',
                        data: [],
                        borderColor: '#0d6efd',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    animation: false
                }
            },
            volume: {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Requests',
                        data: [],
                        backgroundColor: '#0d6efd'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            },
            error: {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#dc3545',
                            '#ffc107',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            }
        };

        // Initialize charts
        const charts = {
            realtime: new Chart(
                document.getElementById('realtimeChart').getContext('2d'),
                chartConfigs.realtime
            ),
            volume: new Chart(
                document.getElementById('volumeChart').getContext('2d'),
                chartConfigs.volume
            ),
            error: new Chart(
                document.getElementById('errorChart').getContext('2d'),
                chartConfigs.error
            )
        };

        // Update real-time data
        function updateRealtime() {
            $.get('api_analytics.php', {
                action: 'realtime',
                minutes: 5
            }, function(data) {
                const labels = data.map(d => moment(d.timestamp).format('HH:mm:ss'));
                const values = data.map(d => d.requests);
                
                charts.realtime.data.labels = labels;
                charts.realtime.data.datasets[0].data = values;
                charts.realtime.update();
            });
        }

        // Update request volume
        function updateVolume(interval = 'hour') {
            $.get('api_analytics.php', {
                action: 'request_volume',
                interval: interval,
                limit: interval === 'hour' ? 24 : 30
            }, function(data) {
                const format = interval === 'hour' ? 'HH:mm' : 'MMM D';
                const labels = data.map(d => moment(d.period).format(format));
                const values = data.map(d => parseInt(d.requests));
                
                charts.volume.data.labels = labels;
                charts.volume.data.datasets[0].data = values;
                charts.volume.update();
                
                // Update quick stats
                if (interval === 'hour') {
                    const total = values.reduce((a, b) => a + b, 0);
                    const errors = data.reduce((a, b) => a + parseInt(b.errors), 0);
                    const avgTime = data.reduce((a, b) => a + parseFloat(b.avg_response_time), 0) / data.length;
                    const users = data.reduce((a, b) => a + parseInt(b.unique_users), 0);
                    
                    $('#totalRequests').text(total.toLocaleString());
                    $('#avgResponseTime').text(Math.round(avgTime));
                    $('#errorRate').text(((errors / total) * 100).toFixed(2));
                    $('#activeUsers').text(users.toLocaleString());
                }
            });
        }

        // Update endpoint metrics
        function updateEndpoints() {
            $.get('api_analytics.php', {
                action: 'endpoint_metrics',
                days: 7
            }, function(data) {
                const $tbody = $('#endpointTable tbody').empty();
                
                data.forEach(endpoint => {
                    const errorRate = (endpoint.errors / endpoint.requests * 100).toFixed(2);
                    $tbody.append(`
                        <tr>
                            <td>${endpoint.endpoint}</td>
                            <td>${parseInt(endpoint.requests).toLocaleString()}</td>
                            <td>${Math.round(endpoint.avg_response_time)}ms</td>
                            <td>
                                <span class="badge bg-${errorRate > 5 ? 'danger' : 'success'}">
                                    ${errorRate}%
                                </span>
                            </td>
                            <td>${parseInt(endpoint.unique_users).toLocaleString()}</td>
                        </tr>
                    `);
                });
            });
        }

        // Update error metrics
        function updateErrors() {
            $.get('api_analytics.php', {
                action: 'error_metrics',
                days: 7
            }, function(data) {
                // Update error chart
                const labels = data.map(d => `HTTP ${d.status_code}`);
                const values = data.map(d => parseInt(d.count));
                
                charts.error.data.labels = labels;
                charts.error.data.datasets[0].data = values;
                charts.error.update();
                
                // Update error table
                const $tbody = $('#errorTable tbody').empty();
                
                data.forEach(error => {
                    $tbody.append(`
                        <tr>
                            <td>
                                <span class="badge bg-danger">
                                    ${error.status_code}
                                </span>
                            </td>
                            <td>${parseInt(error.count).toLocaleString()}</td>
                            <td>${parseInt(error.affected_users).toLocaleString()}</td>
                            <td>${parseInt(error.affected_endpoints).toLocaleString()}</td>
                        </tr>
                    `);
                });
            });
        }

        // Handle interval selection
        $('.interval-btn').click(function() {
            $('.interval-btn').removeClass('active');
            $(this).addClass('active');
            updateVolume($(this).data('interval'));
        });

        // Initial load
        updateRealtime();
        updateVolume('hour');
        updateEndpoints();
        updateErrors();

        // Set up auto-refresh
        setInterval(updateRealtime, 5000);
        setInterval(() => updateVolume('hour'), 60000);
        setInterval(updateEndpoints, 300000);
        setInterval(updateErrors, 300000);
    </script>
</body>
</html>
