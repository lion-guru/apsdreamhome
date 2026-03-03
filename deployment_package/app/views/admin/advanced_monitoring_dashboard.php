<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Advanced Monitoring Dashboard</h1>
            <p class="text-muted">Real-time system monitoring and analytics</p>
        </div>
    </div>
    
    <!-- System Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">System Health</h5>
                    <h2 id="systemHealth">98%</h2>
                    <small>Overall system health</small>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" style="width: 98%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <h2 id="activeUsers">1,234</h2>
                    <small>Currently online</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Response Time</h5>
                    <h2 id="responseTime">245ms</h2>
                    <small>Average response time</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Error Rate</h5>
                    <h2 id="errorRate">0.2%</h2>
                    <small>Last 24 hours</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Real-time Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Real-time Performance</h5>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshCharts()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resource Usage</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>CPU Usage</label>
                        <div class="progress">
                            <div class="progress-bar bg-primary" id="cpuUsage" style="width: 45%">45%</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Memory Usage</label>
                        <div class="progress">
                            <div class="progress-bar bg-info" id="memoryUsage" style="width: 67%">67%</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Disk Usage</label>
                        <div class="progress">
                            <div class="progress-bar bg-warning" id="diskUsage" style="width: 23%">23%</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Network I/O</label>
                        <div class="progress">
                            <div class="progress-bar bg-success" id="networkUsage" style="width: 12%">12%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Service Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Service Status</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Response Time</th>
                                    <th>Uptime</th>
                                    <th>Last Check</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="serviceStatusTable">
                                <!-- Services will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alert History -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Alerts</h5>
                </div>
                <div class="card-body">
                    <div id="recentAlerts">
                        <!-- Alerts will be populated here -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Logs</h5>
                </div>
                <div class="card-body">
                    <div id="systemLogs" style="max-height: 300px; overflow-y: auto;">
                        <!-- Logs will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Advanced Monitoring Dashboard JavaScript
let performanceChart;
let updateInterval;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    startRealTimeUpdates();
    loadServiceStatus();
    loadRecentAlerts();
    loadSystemLogs();
});

// Initialize charts
function initializeCharts() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Response Time (ms)',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'CPU Usage (%)',
                    data: [],
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Memory Usage (%)',
                    data: [],
                    borderColor: 'rgb(255, 205, 86)',
                    backgroundColor: 'rgba(255, 205, 86, 0.2)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Value'
                    },
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });
}

// Start real-time updates
function startRealTimeUpdates() {
    updateInterval = setInterval(function() {
        updateMetrics();
        updateCharts();
        updateServiceStatus();
    }, 5000); // Update every 5 seconds
}

// Update metrics
function updateMetrics() {
    fetch('/admin/monitoring/metrics')
        .then(response => response.json())
        .then(data => {
            document.getElementById('systemHealth').textContent = data.system_health + '%';
            document.getElementById('activeUsers').textContent = data.active_users.toLocaleString();
            document.getElementById('responseTime').textContent = data.response_time + 'ms';
            document.getElementById('errorRate').textContent = data.error_rate + '%';
            
            // Update resource usage
            document.getElementById('cpuUsage').style.width = data.cpu_usage + '%';
            document.getElementById('cpuUsage').textContent = data.cpu_usage + '%';
            
            document.getElementById('memoryUsage').style.width = data.memory_usage + '%';
            document.getElementById('memoryUsage').textContent = data.memory_usage + '%';
            
            document.getElementById('diskUsage').style.width = data.disk_usage + '%';
            document.getElementById('diskUsage').textContent = data.disk_usage + '%';
            
            document.getElementById('networkUsage').style.width = data.network_usage + '%';
            document.getElementById('networkUsage').textContent = data.network_usage + '%';
        })
        .catch(error => {
            console.error('Error updating metrics:', error);
        });
}

// Update charts
function updateCharts() {
    fetch('/admin/monitoring/chart-data')
        .then(response => response.json())
        .then(data => {
            // Update performance chart
            performanceChart.data.labels = data.labels;
            performanceChart.data.datasets[0].data = data.response_time;
            performanceChart.data.datasets[1].data = data.cpu_usage;
            performanceChart.data.datasets[2].data = data.memory_usage;
            performanceChart.update();
        })
        .catch(error => {
            console.error('Error updating charts:', error);
        });
}

// Load service status
function loadServiceStatus() {
    fetch('/admin/monitoring/service-status')
        .then(response => response.json())
        .then(services => {
            const tbody = document.getElementById('serviceStatusTable');
            tbody.innerHTML = '';
            
            services.forEach(service => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${service.name}</td>
                    <td>
                        <span class="badge bg-${service.status === 'healthy' ? 'success' : service.status === 'warning' ? 'warning' : 'danger'}">
                            ${service.status}
                        </span>
                    </td>
                    <td>${service.response_time}ms</td>
                    <td>${service.uptime}</td>
                    <td>${new Date(service.last_check).toLocaleTimeString()}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary" onclick="restartService('${service.id}')">
                                <i class="fas fa-redo"></i> Restart
                            </button>
                            <button class="btn btn-sm btn-info" onclick="viewServiceDetails('${service.id}')">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error loading service status:', error);
        });
}

// Load recent alerts
function loadRecentAlerts() {
    fetch('/admin/monitoring/recent-alerts')
        .then(response => response.json())
        .then(alerts => {
            const alertsContainer = document.getElementById('recentAlerts');
            alertsContainer.innerHTML = '';
            
            alerts.forEach(alert => {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${alert.severity === 'critical' ? 'danger' : alert.severity === 'warning' ? 'warning' : 'info'} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    <strong>${alert.title}</strong> - ${alert.message}
                    <br>
                    <small class="text-muted">${new Date(alert.created_at).toLocaleString()}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                alertsContainer.appendChild(alertDiv);
            });
        })
        .catch(error => {
            console.error('Error loading alerts:', error);
        });
}

// Load system logs
function loadSystemLogs() {
    fetch('/admin/monitoring/system-logs')
        .then(response => response.json())
        .then(logs => {
            const logsContainer = document.getElementById('systemLogs');
            logsContainer.innerHTML = '';
            
            logs.forEach(log => {
                const logDiv = document.createElement('div');
                logDiv.className = `log-entry log-${log.level.toLowerCase()}`;
                logDiv.innerHTML = `
                    <span class="log-timestamp">${new Date(log.timestamp).toLocaleTimeString()}</span>
                    <span class="log-level">[${log.level}]</span>
                    <span class="log-message">${log.message}</span>
                `;
                logsContainer.appendChild(logDiv);
            });
            
            // Auto-scroll to bottom
            logsContainer.scrollTop = logsContainer.scrollHeight;
        })
        .catch(error => {
            console.error('Error loading logs:', error);
        });
}

// Refresh charts
function refreshCharts() {
    updateCharts();
    updateMetrics();
}

// Restart service
function restartService(serviceId) {
    if (confirm('Are you sure you want to restart this service?')) {
        fetch(`/admin/monitoring/restart-service/${serviceId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Service restart initiated successfully');
                loadServiceStatus();
            } else {
                alert('Failed to restart service: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error restarting service:', error);
        });
    }
}

// View service details
function viewServiceDetails(serviceId) {
    window.open(`/admin/monitoring/service-details/${serviceId}`, '_blank');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>

<style>
/* Advanced Monitoring Dashboard Styles */
.log-entry {
    font-family: monospace;
    font-size: 12px;
    padding: 2px 0;
    border-bottom: 1px solid #eee;
}

.log-timestamp {
    color: #666;
    margin-right: 10px;
}

.log-level {
    font-weight: bold;
    margin-right: 10px;
}

.log-error .log-level {
    color: #dc3545;
}

.log-warning .log-level {
    color: #ffc107;
}

.log-info .log-level {
    color: #17a2b8;
}

.log-debug .log-level {
    color: #6c757d;
}

.progress {
    height: 25px;
}

.progress-bar {
    line-height: 25px;
}

.card-tools {
    float: right;
}

.alert {
    margin-bottom: 10px;
}

.badge {
    font-size: 12px;
}

.btn-group .btn {
    font-size: 12px;
    padding: 4px 8px;
}
</style>
