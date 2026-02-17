<?php
/**
 * Log Analytics Dashboard
 * View and analyze aggregated logs
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_aggregator/log_aggregator.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';

// Apply rate limiting
$rateLimitMiddleware->handle('admin');

// Initialize aggregator
$aggregator = new LogAggregator();
$db = \App\Core\App::database();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_summary':
            $query = "SELECT 
                        DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00') as hour,
                        source,
                        level,
                        COUNT(*) as count
                     FROM aggregated_logs
                     WHERE timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                     GROUP BY hour, source, level
                     ORDER BY hour DESC";
            
            $summary = $db->fetchAll($query);
            echo json_encode($summary);
            break;
            
        case 'get_alerts':
            $query = "SELECT 
                        a.*,
                        p.name as pattern_name,
                        p.severity,
                        p.description
                     FROM log_alerts a
                     JOIN log_patterns p ON a.pattern_id = p.id
                     WHERE a.last_seen > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                     ORDER BY a.last_seen DESC";
            
            $alerts = $db->fetchAll($query);
            echo json_encode($alerts);
            break;
            
        case 'get_patterns':
            $query = "SELECT * FROM log_patterns ORDER BY name";
            $patterns = $db->fetchAll($query);
            echo json_encode($patterns);
            break;
            
        case 'search_logs':
            $search = $_GET['query'] ?? '';
            $source = $_GET['source'] ?? '';
            $level = $_GET['level'] ?? '';
            $startDate = $_GET['start_date'] ?? '';
            $endDate = $_GET['end_date'] ?? '';
            
            $query = "SELECT * FROM aggregated_logs WHERE 1=1";
            $params = [];
            
            if ($search) {
                $query .= " AND (message LIKE ? OR context LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if ($source) {
                $query .= " AND source = ?";
                $params[] = $source;
            }
            
            if ($level) {
                $query .= " AND level = ?";
                $params[] = $level;
            }
            
            if ($startDate) {
                $query .= " AND timestamp >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $query .= " AND timestamp <= ?";
                $params[] = $endDate;
            }
            
            $query .= " ORDER BY timestamp DESC LIMIT 100";
            $logs = $db->fetchAll($query, $params);
            echo json_encode($logs);
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Analytics - APS Dream Homes</title>
    <link href="<?= get_admin_asset_url('bootstrap.min.css', 'css') ?>" rel="stylesheet">
    <link href="<?= get_admin_asset_url('daterangepicker.css', 'css') ?>" rel="stylesheet">
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .severity-critical { color: #dc3545; }
        .severity-high { color: #fd7e14; }
        .severity-medium { color: #ffc107; }
        .severity-low { color: #20c997; }
        .log-entry {
            font-family: monospace;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .log-entry:nth-child(odd) {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4">Log Analytics Dashboard</h1>
        
        <!-- Summary Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Log Volume by Source</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="sourceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Log Levels Distribution</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="levelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Alerts -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Active Alerts</div>
                    <div class="card-body">
                        <div id="alertsList"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Log Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Search Logs</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" id="searchQuery" class="form-control" placeholder="Search logs...">
                            </div>
                            <div class="col-md-2">
                                <select id="sourceFilter" class="form-select">
                                    <option value="">All Sources</option>
                                    <option value="security">Security</option>
                                    <option value="error">Error</option>
                                    <option value="access">Access</option>
                                    <option value="api">API</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="levelFilter" class="form-select">
                                    <option value="">All Levels</option>
                                    <option value="error">Error</option>
                                    <option value="warning">Warning</option>
                                    <option value="info">Info</option>
                                    <option value="debug">Debug</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" id="dateRange" class="form-control" placeholder="Date Range">
                            </div>
                            <div class="col-md-2">
                                <button id="searchBtn" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>
                        <div id="searchResults"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= get_admin_asset_url('jquery.min.js', 'js') ?>"></script>
    <script src="<?= get_admin_asset_url('bootstrap.min.js', 'js') ?>"></script>
    <script src="<?= get_admin_asset_url('moment.min.js', 'js') ?>"></script>
    <script src="<?= get_admin_asset_url('daterangepicker.min.js', 'js') ?>"></script>
    <script src="<?= get_admin_asset_url('chart.min.js', 'js') ?>"></script>
    <script>
        // Initialize charts
        const sourceChart = new Chart(
            document.getElementById('sourceChart'),
            {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            }
        );

        const levelChart = new Chart(
            document.getElementById('levelChart'),
            {
                type: 'pie',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#dc3545',
                            '#ffc107',
                            '#0dcaf0',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            }
        );

        // Initialize date range picker
        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Load summary data
        function loadSummary() {
            $.get('log_analytics.php', { action: 'get_summary' }, function(data) {
                updateCharts(data);
            });
        }

        // Update charts with data
        function updateCharts(data) {
            // Process data for source chart
            const sourceData = {};
            const hours = [...new Set(data.map(item => item.hour))].sort();
            
            data.forEach(item => {
                if (!sourceData[item.source]) {
                    sourceData[item.source] = new Array(hours.length).fill(0);
                }
                const hourIndex = hours.indexOf(item.hour);
                sourceData[item.source][hourIndex] = item.count;
            });

            sourceChart.data.labels = hours;
            sourceChart.data.datasets = Object.keys(sourceData).map((source, index) => ({
                label: source,
                data: sourceData[source],
                borderColor: getColor(index),
                fill: false
            }));
            sourceChart.update();

            // Process data for level chart
            const levelData = {};
            data.forEach(item => {
                if (!levelData[item.level]) {
                    levelData[item.level] = 0;
                }
                levelData[item.level] += parseInt(item.count);
            });

            levelChart.data.labels = Object.keys(levelData);
            levelChart.data.datasets[0].data = Object.values(levelData);
            levelChart.update();
        }

        // Load active alerts
        function loadAlerts() {
            $.get('log_analytics.php', { action: 'get_alerts' }, function(data) {
                const $alertsList = $('#alertsList').empty();
                
                data.forEach(alert => {
                    $alertsList.append(`
                        <div class="alert alert-warning">
                            <h5 class="severity-${alert.severity.toLowerCase()}">${alert.pattern_name}</h5>
                            <p>${alert.description}</p>
                            <small>
                                Occurred ${alert.occurrence_count} times between 
                                ${formatDate(alert.first_seen)} and ${formatDate(alert.last_seen)}
                            </small>
                        </div>
                    `);
                });
            });
        }

        // Search logs
        function searchLogs() {
            const query = $('#searchQuery').val();
            const source = $('#sourceFilter').val();
            const level = $('#levelFilter').val();
            const dateRange = $('#dateRange').val();
            let startDate = null;
            let endDate = null;

            if (dateRange) {
                const dates = dateRange.// SECURITY: Replaced deprecated function' - ');
                startDate = dates[0];
                endDate = dates[1];
            }

            $.get('log_analytics.php', {
                action: 'search_logs',
                query: query,
                source: source,
                level: level,
                start_date: startDate,
                end_date: endDate
            }, function(data) {
                const $results = $('#searchResults').empty();
                
                data.forEach(log => {
                    $results.append(`
                        <div class="log-entry">
                            <div class="text-muted">${formatDate(log.timestamp)}</div>
                            <div>[${log.source}] [${log.level}] ${log.message}</div>
                            ${log.context ? `<pre>${JSON.stringify(JSON.parse(log.context), null, 2)}</pre>` : ''}
                        </div>
                    `);
                });
            });
        }

        // Utility functions
        function formatDate(date) {
            return moment(date).format('YYYY-MM-DD HH:mm:ss');
        }

        function getColor(index) {
            const colors = [
                '#0d6efd',
                '#dc3545',
                '#ffc107',
                '#198754',
                '#0dcaf0',
                '#6610f2'
            ];
            return colors[index % colors.length];
        }

        // Event handlers
        $('#searchBtn').click(searchLogs);

        // Initial load
        loadSummary();
        loadAlerts();
        setInterval(loadSummary, 60000);  // Refresh every minute
        setInterval(loadAlerts, 30000);   // Refresh every 30 seconds
    </script>
</body>
</html>

