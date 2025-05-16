<?php
require_once '../includes/auth/auth_session.php';
require_once '../includes/db_settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Debugger - APS Dream Home</title>
    <?php include '../includes/templates/header_links.php'; ?>
    <style>
        .log-entry {
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-error { color: #dc3545; }
        .log-warning { color: #ffc107; }
        .log-success { color: #198754; }
        .log-info { color: #0dcaf0; }
        .notification-flow {
            position: relative;
            padding: 20px;
            margin-bottom: 20px;
        }
        .flow-step {
            position: relative;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .flow-arrow {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            color: #6c757d;
        }
        .anomaly-card {
            border-left: 4px solid;
        }
        .anomaly-critical { border-color: #dc3545; }
        .anomaly-warning { border-color: #ffc107; }
        .anomaly-info { border-color: #0dcaf0; }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../includes/templates/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/templates/admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Notification Debugger</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearLogs()">
                                <i class="fas fa-trash"></i> Clear Logs
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportLogs()">
                                <i class="fas fa-download"></i> Export Logs
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAutoRefresh()">
                            <i class="fas fa-sync-alt"></i> <span id="refreshStatus">Auto-refresh On</span>
                        </button>
                    </div>
                </div>

                <!-- Anomaly Detection -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Anomaly Detection</h5>
                                <div id="anomalies"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real-time Monitoring -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Queue Monitor</h5>
                                <div class="notification-flow" id="queueFlow">
                                    <div class="flow-step">
                                        <strong>Pending</strong>
                                        <div id="pendingCount">0</div>
                                    </div>
                                    <div class="flow-arrow">↓</div>
                                    <div class="flow-step">
                                        <strong>Processing</strong>
                                        <div id="processingCount">0</div>
                                    </div>
                                    <div class="flow-arrow">↓</div>
                                    <div class="flow-step">
                                        <strong>Completed/Failed</strong>
                                        <div id="completedCount">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Performance Monitor</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Avg. Processing Time</label>
                                            <h3 id="avgProcessingTime">0ms</h3>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Success Rate</label>
                                            <h3 id="successRate">0%</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" id="successRateBar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Logs -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Live Logs</h5>
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="logFilter" placeholder="Filter logs...">
                                <select class="form-select" id="logLevel">
                                    <option value="all">All Levels</option>
                                    <option value="error">Errors</option>
                                    <option value="warning">Warnings</option>
                                    <option value="info">Info</option>
                                    <option value="success">Success</option>
                                </select>
                                <button class="btn btn-outline-secondary" onclick="applyFilter()">
                                    <i class="fas fa-filter"></i> Apply
                                </button>
                            </div>
                        </div>
                        <div class="log-container" style="height: 400px; overflow-y: auto;">
                            <div id="logEntries"></div>
                        </div>
                    </div>
                </div>

                <!-- Notification Testing -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Test Notification</h5>
                        <form id="testForm" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Notification Type</label>
                                <select class="form-select" id="testType" required>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Recipient</label>
                                <select class="form-select" id="testRecipient" required>
                                    <?php
                                    $users = $conn->query("SELECT id, email, phone FROM users WHERE email IS NOT NULL");
                                    while ($user = $users->fetch_assoc()) {
                                        echo "<option value='{$user['id']}'>{$user['email']}" . 
                                             ($user['phone'] ? " / {$user['phone']}" : "") . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Template</label>
                                <select class="form-select" id="testTemplate" required>
                                    <?php
                                    $templates = $conn->query("SELECT type FROM notification_templates");
                                    while ($template = $templates->fetch_assoc()) {
                                        echo "<option value='{$template['type']}'>{$template['type']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Send Test Notification</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include '../includes/templates/admin_footer.php'; ?>
    <script>
        let autoRefresh = true;
        let refreshInterval;

        document.addEventListener('DOMContentLoaded', function() {
            startAutoRefresh();
            setupTestForm();
            loadInitialData();
        });

        function startAutoRefresh() {
            refreshData();
            refreshInterval = setInterval(refreshData, 5000);
        }

        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            document.getElementById('refreshStatus').textContent = 
                autoRefresh ? 'Auto-refresh On' : 'Auto-refresh Off';
            
            if (autoRefresh) {
                startAutoRefresh();
            } else {
                clearInterval(refreshInterval);
            }
        }

        function refreshData() {
            fetch('/apsdreamhomefinal/api/notification_debug.php')
                .then(response => response.json())
                .then(data => {
                    updateQueueMonitor(data.queue);
                    updatePerformanceMonitor(data.performance);
                    updateLogs(data.logs);
                    updateAnomalies(data.anomalies);
                })
                .catch(error => console.error('Error refreshing data:', error));
        }

        function updateQueueMonitor(queue) {
            document.getElementById('pendingCount').textContent = queue.pending;
            document.getElementById('processingCount').textContent = queue.processing;
            document.getElementById('completedCount').textContent = 
                queue.completed + ' / ' + queue.failed;
        }

        function updatePerformanceMonitor(performance) {
            document.getElementById('avgProcessingTime').textContent = 
                performance.avgProcessingTime + 'ms';
            document.getElementById('successRate').textContent = 
                performance.successRate + '%';
            
            const bar = document.getElementById('successRateBar');
            bar.style.width = performance.successRate + '%';
            bar.className = 'progress-bar ' + 
                (performance.successRate >= 90 ? 'bg-success' : 
                 performance.successRate >= 70 ? 'bg-warning' : 'bg-danger');
        }

        function updateLogs(logs) {
            const container = document.getElementById('logEntries');
            const filter = document.getElementById('logFilter').value.toLowerCase();
            const level = document.getElementById('logLevel').value;

            let html = '';
            logs.forEach(log => {
                if (shouldShowLog(log, filter, level)) {
                    html += `
                        <div class="log-entry log-${log.level}">
                            [${log.timestamp}] ${log.message}
                        </div>
                    `;
                }
            });

            if (!filter && level === 'all') {
                container.innerHTML = html;
            } else {
                const currentLogs = container.innerHTML;
                container.innerHTML = html + currentLogs;
            }
        }

        function updateAnomalies(anomalies) {
            const container = document.getElementById('anomalies');
            let html = '';

            anomalies.forEach(anomaly => {
                html += `
                    <div class="card mb-2 anomaly-card anomaly-${anomaly.severity}">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">
                                ${anomaly.timestamp} - ${anomaly.severity.toUpperCase()}
                            </h6>
                            <p class="card-text">${anomaly.message}</p>
                            ${anomaly.details ? `<small class="text-muted">${anomaly.details}</small>` : ''}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function shouldShowLog(log, filter, level) {
            if (level !== 'all' && log.level !== level) return false;
            if (filter && !log.message.toLowerCase().includes(filter)) return false;
            return true;
        }

        function applyFilter() {
            const container = document.getElementById('logEntries');
            container.innerHTML = '';
            refreshData();
        }

        function clearLogs() {
            if (confirm('Are you sure you want to clear all logs?')) {
                fetch('/apsdreamhomefinal/api/notification_debug.php?action=clear', {
                    method: 'POST'
                })
                .then(() => {
                    document.getElementById('logEntries').innerHTML = '';
                })
                .catch(error => console.error('Error clearing logs:', error));
            }
        }

        function exportLogs() {
            window.location.href = '/apsdreamhomefinal/api/notification_debug.php?action=export';
        }

        function setupTestForm() {
            document.getElementById('testForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const data = {
                    type: document.getElementById('testType').value,
                    recipient: document.getElementById('testRecipient').value,
                    template: document.getElementById('testTemplate').value
                };

                fetch('/apsdreamhomefinal/api/notification_debug.php?action=test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    alert(result.message);
                    refreshData();
                })
                .catch(error => {
                    console.error('Error sending test notification:', error);
                    alert('Error sending test notification');
                });
            });
        }

        function loadInitialData() {
            refreshData();
        }
    </script>
</body>
</html>
