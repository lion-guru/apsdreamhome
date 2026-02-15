<?php
require_once __DIR__ . '/../core/init.php';
if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitor Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .status-badge {
            font-size: 0.8em;
            padding: 0.375rem 0.75rem;
        }
        .metric-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .status-healthy { background-color: #d4edda; color: #155724; }
        .status-warning { background-color: #fff3cd; color: #856404; }
        .status-error { background-color: #f8d7da; color: #721c24; }
        .status-critical { background-color: #f5c6cb; color: #721c24; }
        .refresh-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-tachometer-alt text-primary"></i> System Monitor Dashboard</h1>
                        <p class="text-muted">Real-time system health and performance monitoring</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button class="btn btn-success" onclick="createBackup()">
                            <i class="fas fa-download"></i> Create Backup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="metric-card">
                    <h6><i class="fas fa-server text-primary"></i> System Status</h6>
                    <div id="system-status" class="mt-2">
                        <span class="badge status-badge status-healthy">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <h6><i class="fas fa-database text-success"></i> Database</h6>
                    <div id="database-status" class="mt-2">
                        <span class="badge status-badge status-healthy">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <h6><i class="fas fa-memory text-info"></i> Memory Usage</h6>
                    <div id="memory-usage" class="mt-2">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Loading...</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <h6><i class="fas fa-hdd text-warning"></i> Disk Space</h6>
                    <div id="disk-space" class="mt-2">
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Loading...</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Health Checks -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="metric-card">
                    <h5><i class="fas fa-heartbeat text-danger"></i> Health Checks</h5>
                    <div id="health-checks" class="row mt-3">
                        <!-- Health checks will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="metric-card">
                    <h5><i class="fas fa-chart-line text-success"></i> Performance Metrics</h5>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary" id="php-version">-</div>
                                <small class="text-muted">PHP Version</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info" id="execution-time">-</div>
                                <small class="text-muted">Execution Time</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-warning" id="memory-usage-text">-</div>
                                <small class="text-muted">Memory Usage</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success" id="included-files">-</div>
                                <small class="text-muted">Files Loaded</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Management -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="metric-card">
                    <h5><i class="fas fa-shield-alt text-secondary"></i> Backup Management</h5>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <button class="btn btn-success w-100" onclick="createFullBackup()">
                                <i class="fas fa-database"></i> Create Full Backup
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-info w-100" onclick="createDatabaseBackup()">
                                <i class="fas fa-table"></i> Create Database Backup
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div id="backup-status" class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Backup status will appear here...
                        </div>
                    </div>
                    <div id="backup-list" class="mt-3">
                        <!-- Backup list will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Errors -->
        <div class="row">
            <div class="col-12">
                <div class="metric-card">
                    <h5><i class="fas fa-exclamation-triangle text-danger"></i> Recent Errors</h5>
                    <div id="recent-errors" class="mt-3">
                        <!-- Recent errors will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Refresh dashboard data
        function refreshDashboard() {
            loadSystemStatus();
            loadHealthChecks();
            loadPerformanceMetrics();
            loadBackupList();
            loadRecentErrors();
        }

        // Load system status
        function loadSystemStatus() {
            fetch('/apsdreamhome/index.php?route=api/monitor/status')
                .then(response => response.json())
                .then(data => {
                    const statusBadge = document.getElementById('system-status');
                    statusBadge.innerHTML = `<span class="badge status-badge status-${data.status}">${data.status.toUpperCase()}</span>`;
                    statusBadge.innerHTML += `<br><small class="text-muted">${data.timestamp}</small>`;
                })
                .catch(error => {
                    document.getElementById('system-status').innerHTML =
                        '<span class="badge status-badge status-error">ERROR</span>';
                });
        }

        // Load health checks
        function loadHealthChecks() {
            fetch('/apsdreamhome/index.php?route=api/monitor/health')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('health-checks');
                    container.innerHTML = '';

                    Object.keys(data.health_checks).forEach(check => {
                        const checkData = data.health_checks[check];
                        const badgeClass = `status-${checkData.status}`;

                        container.innerHTML += `
                            <div class="col-md-6 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>${check.charAt(0).toUpperCase() + check.slice(1)}</strong>
                                    <span class="badge status-badge ${badgeClass}">${checkData.status.toUpperCase()}</span>
                                </div>
                                <small class="text-muted">${checkData.message}</small>
                            </div>
                        `;
                    });
                })
                .catch(error => {
                    document.getElementById('health-checks').innerHTML =
                        '<div class="col-12"><div class="alert alert-danger">Error loading health checks</div></div>';
                });
        }

        // Load performance metrics
        function loadPerformanceMetrics() {
            fetch('/apsdreamhome/index.php?route=api/monitor/performance')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('php-version').textContent = data.php_version;
                    document.getElementById('execution-time').textContent = data.execution_time;
                    document.getElementById('memory-usage-text').textContent = data.memory_usage;
                    document.getElementById('included-files').textContent = data.included_files;

                    // Update memory progress bar
                    const memoryUsage = parseFloat(data.memory_usage.split(' ')[0]);
                    const memoryLimit = 128; // Assume 128MB limit
                    const memoryPercent = (memoryUsage / memoryLimit) * 100;
                    document.querySelector('#memory-usage .progress-bar').style.width = memoryPercent + '%';
                })
                .catch(error => {
                    console.error('Error loading performance metrics:', error);
                });
        }

        // Load backup list
        function loadBackupList() {
            fetch('/apsdreamhome/index.php?route=api/backup/list')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('backup-list');
                    if (data.length === 0) {
                        container.innerHTML = '<p class="text-muted">No backups found</p>';
                        return;
                    }

                    let html = '<div class="table-responsive"><table class="table table-sm">';
                    html += '<thead><tr><th>File</th><th>Size</th><th>Created</th><th>Type</th><th>Actions</th></tr></thead>';
                    html += '<tbody>';

                    data.forEach(backup => {
                        html += `
                            <tr>
                                <td>${backup.filename}</td>
                                <td>${formatBytes(backup.size)}</td>
                                <td>${backup.created}</td>
                                <td><span class="badge bg-secondary">${backup.type}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteBackup('${backup.filename}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    html += '</tbody></table></div>';
                    container.innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('backup-list').innerHTML =
                        '<div class="alert alert-warning">Error loading backup list</div>';
                });
        }

        // Load recent errors
        function loadRecentErrors() {
            fetch('/apsdreamhome/index.php?route=api/monitor/errors')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recent-errors');
                    if (data.length === 0) {
                        container.innerHTML = '<p class="text-muted">No recent errors</p>';
                        return;
                    }

                    let html = '<div class="list-group">';
                    data.forEach(error => {
                        html += `
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-muted">${error}</small>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('recent-errors').innerHTML =
                        '<div class="alert alert-warning">Error loading recent errors</div>';
                });
        }

        // Create full backup
        function createFullBackup() {
            const statusDiv = document.getElementById('backup-status');
            statusDiv.className = 'alert alert-info';
            statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating full backup...';

            fetch('/apsdreamhome/index.php?route=api/backup/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ type: 'full' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.className = 'alert alert-success';
                    statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    loadBackupList(); // Refresh backup list
                } else {
                    statusDiv.className = 'alert alert-danger';
                    statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
                }
            })
            .catch(error => {
                statusDiv.className = 'alert alert-danger';
                statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error creating backup';
            });
        }

        // Create database backup
        function createDatabaseBackup() {
            const statusDiv = document.getElementById('backup-status');
            statusDiv.className = 'alert alert-info';
            statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating database backup...';

            fetch('/apsdreamhome/index.php?route=api/backup/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ type: 'database' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.className = 'alert alert-success';
                    statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    loadBackupList(); // Refresh backup list
                } else {
                    statusDiv.className = 'alert alert-danger';
                    statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
                }
            })
            .catch(error => {
                statusDiv.className = 'alert alert-danger';
                statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error creating backup';
            });
        }

        // Delete backup
        function deleteBackup(filename) {
            if (confirm('Are you sure you want to delete this backup?')) {
                fetch('/apsdreamhome/index.php?route=api/backup/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ filename: filename })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadBackupList(); // Refresh backup list
                    } else {
                        alert('Error deleting backup: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting backup');
                });
            }
        }

        // Format bytes helper
        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Auto-refresh every 30 seconds
        setInterval(refreshDashboard, 30000);

        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            refreshDashboard();
        });
    </script>
</body>
</html>
