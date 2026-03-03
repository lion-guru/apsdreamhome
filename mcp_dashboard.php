<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - MCP Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --gradient-danger: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .server-card {
            background: white;
            border-radius: 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .server-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .server-header {
            background: var(--gradient-primary);
            color: white;
            padding: 15px;
            font-weight: 600;
            border-radius: 15px 15px 0 0;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-active { background: var(--success-color); }
        .status-inactive { background: var(--danger-color); }
        .status-error { background: var(--warning-color); }

        .metric-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .metric-label {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .log-container {
            background: #2d3748;
            color: #e2e8f0;
            border-radius: 15px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .log-entry {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .log-info { border-left-color: var(--info-color); background: rgba(23, 162, 184, 0.1); }
        .log-warning { border-left-color: var(--warning-color); background: rgba(255, 193, 7, 0.1); }
        .log-error { border-left-color: var(--danger-color); background: rgba(220, 53, 69, 0.1); }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="main-container p-4">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <h1 class="display-4 fw-bold text-white mb-3">
                            <i class="fas fa-server me-3"></i>
                            MCP Dashboard
                        </h1>
                        <p class="lead text-white-50">
                            APS Dream Home - Real-time MCP Server Monitoring & Management
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <span class="badge bg-success text-white">
                                <i class="fas fa-database me-2"></i>12+ Servers
                            </span>
                            <span class="badge bg-info text-white">
                                <i class="fas fa-chart-line me-2"></i>Live Monitoring
                            </span>
                            <span class="badge bg-warning text-white">
                                <i class="fas fa-clock me-2"></i>Auto Refresh
                            </span>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value" id="total-servers">12</div>
                                <div class="metric-label">Total Servers</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value" id="active-servers">0</div>
                                <div class="metric-label">Active Servers</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value" id="total-requests">0</div>
                                <div class="metric-label">Total Requests</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value" id="uptime">0%</div>
                                <div class="metric-label">System Uptime</div>
                            </div>
                        </div>
                    </div>

                    <!-- Server Status Grid -->
                    <div class="row" id="server-grid">
                        <!-- Server cards will be dynamically added here -->
                    </div>

                    <!-- Charts Section -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="chart-container">
                                <h5 class="mb-3">Server Performance</h5>
                                <canvas id="performanceChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container">
                                <h5 class="mb-3">Request Distribution</h5>
                                <canvas id="requestChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Logs -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="chart-container">
                                <h5 class="mb-3">
                                    <i class="fas fa-list me-2"></i>
                                    Recent Server Logs
                                    <button class="btn btn-sm btn-outline-primary float-end" onclick="refreshLogs()">
                                        <i class="fas fa-sync me-1"></i>Refresh
                                    </button>
                                </h5>
                                <div class="log-container" id="log-container">
                                    <!-- Logs will be dynamically added here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Control Panel -->
                    <div class="text-center mt-4">
                        <div class="d-flex justify-content-center gap-3">
                            <button class="btn btn-success btn-lg" onclick="startAllServers()">
                                <i class="fas fa-play me-2"></i>Start All
                            </button>
                            <button class="btn btn-warning btn-lg" onclick="stopAllServers()">
                                <i class="fas fa-stop me-2"></i>Stop All
                            </button>
                            <button class="btn btn-info btn-lg" onclick="refreshDashboard()">
                                <i class="fas fa-sync me-2"></i>Refresh
                            </button>
                            <button class="btn btn-primary btn-lg" onclick="openConfiguration()">
                                <i class="fas fa-cog me-2"></i>Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let performanceChart, requestChart;
        let refreshInterval;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            loadServerStatus();
            loadRecentLogs();
            
            // Auto-refresh every 30 seconds
            refreshInterval = setInterval(refreshDashboard, 30000);
        });

        // Initialize charts
        function initializeCharts() {
            // Performance Chart
            const perfCtx = document.getElementById('performanceChart').getContext('2d');
            performanceChart = new Chart(perfCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: [],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4
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

            // Request Chart
            const reqCtx = document.getElementById('requestChart').getContext('2d');
            requestChart = new Chart(reqCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Database', 'Search', 'Mapping', 'Payment', 'Communication', 'AI'],
                    datasets: [{
                        data: [0, 0, 0, 0, 0, 0],
                        backgroundColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#dc3545',
                            '#6f42c1',
                            '#e83e8c'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Load server status
        async function loadServerStatus() {
            try {
                const response = await fetch('/apsdreamhome/config/mcp_server_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'get_status'
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    displayServerGrid(result.result);
                    updateMetrics(result.result);
                }
            } catch (error) {
                console.error('Error loading server status:', error);
            }
        }

        // Display server grid
        function displayServerGrid(servers) {
            const grid = document.getElementById('server-grid');
            grid.innerHTML = '';

            Object.keys(servers).forEach(serverKey => {
                const server = servers[serverKey];
                const statusClass = server.running ? 'status-active' : 'status-inactive';
                const statusText = server.running ? 'Running' : 'Stopped';
                
                const serverCard = `
                    <div class="col-md-6 col-lg-4">
                        <div class="server-card">
                            <div class="server-header">
                                <span class="status-indicator ${statusClass}"></span>
                                ${serverKey}
                            </div>
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-${server.running ? 'success' : 'danger'}">
                                        ${statusText}
                                    </span>
                                    <small class="text-muted">
                                        ${server.configured ? 'Configured' : 'Not Configured'}
                                    </small>
                                </div>
                                <div class="text-muted small">
                                    Last check: ${server.last_check || 'Never'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                grid.innerHTML += serverCard;
            });
        }

        // Update metrics
        function updateMetrics(servers) {
            const totalServers = Object.keys(servers).length;
            const activeServers = Object.values(servers).filter(s => s.running).length;
            const uptime = totalServers > 0 ? Math.round((activeServers / totalServers) * 100) : 0;

            document.getElementById('total-servers').textContent = totalServers;
            document.getElementById('active-servers').textContent = activeServers;
            document.getElementById('uptime').textContent = uptime + '%';
        }

        // Load recent logs
        async function loadRecentLogs() {
            try {
                const response = await fetch('/apsdreamhome/config/mcp_database_integration.php?action=get_logs&limit=10');
                const result = await response.json();
                
                if (result.success) {
                    displayLogs(result.logs);
                }
            } catch (error) {
                console.error('Error loading logs:', error);
            }
        }

        // Display logs
        function displayLogs(logs) {
            const container = document.getElementById('log-container');
            
            if (logs.length === 0) {
                container.innerHTML = '<div class="text-muted text-center">No recent logs found</div>';
                return;
            }

            container.innerHTML = logs.map(log => `
                <div class="log-entry log-${log.log_level}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${log.server_name}</strong>
                            <span class="badge bg-secondary ms-2">${log.log_level.toUpperCase()}</span>
                        </div>
                        <small class="text-muted">${log.created_at}</small>
                    </div>
                    <div class="mt-2">${log.message}</div>
                </div>
            `).join('');
        }

        // Control functions
        async function startAllServers() {
            try {
                const response = await fetch('/apsdreamhome/config/mcp_server_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'start_all'
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    showNotification('All MCP servers started successfully!', 'success');
                    setTimeout(refreshDashboard, 2000);
                } else {
                    showNotification('Failed to start servers: ' + result.error, 'danger');
                }
            } catch (error) {
                showNotification('Error starting servers: ' + error.message, 'danger');
            }
        }

        async function stopAllServers() {
            if (!confirm('Are you sure you want to stop all MCP servers?')) {
                return;
            }

            try {
                const response = await fetch('/apsdreamhome/config/mcp_server_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'stop_all'
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    showNotification('All MCP servers stopped successfully!', 'warning');
                    setTimeout(refreshDashboard, 2000);
                } else {
                    showNotification('Failed to stop servers: ' + result.error, 'danger');
                }
            } catch (error) {
                showNotification('Error stopping servers: ' + error.message, 'danger');
            }
        }

        function refreshDashboard() {
            loadServerStatus();
            loadRecentLogs();
            showNotification('Dashboard refreshed!', 'info');
        }

        function refreshLogs() {
            loadRecentLogs();
        }

        function openConfiguration() {
            window.open('/apsdreamhome/mcp_configuration_gui.php', '_blank');
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
    </script>
</body>
</html>
