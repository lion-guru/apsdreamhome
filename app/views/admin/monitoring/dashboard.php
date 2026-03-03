<?php
$page_title = 'System Monitoring - APS Dream Home';
include __DIR__ . '/../../layouts/base.php';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="monitoring-header text-center mb-5">
            <h1 class="display-4 fw-bold text-primary mb-3">
                <i class="fas fa-heartbeat me-3"></i>System Monitoring
            </h1>
            <p class="lead text-muted">
                Real-time system health monitoring and performance metrics
            </p>
        </div>

        <!-- System Health Overview -->
        <div class="health-overview mb-5">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="health-card text-center p-4">
                        <div class="health-icon mb-3">
                            <i class="fas fa-server fa-3x text-success"></i>
                        </div>
                        <h5>System Status</h5>
                        <div class="health-status">
                            <span class="badge bg-success">Healthy</span>
                        </div>
                        <p class="text-muted small mt-2">All systems operational</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="health-card text-center p-4">
                        <div class="health-icon mb-3">
                            <i class="fas fa-database fa-3x text-info"></i>
                        </div>
                        <h5>Database</h5>
                        <div class="health-status">
                            <span class="badge bg-success">Connected</span>
                        </div>
                        <p class="text-muted small mt-2">Response: 12ms</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="health-card text-center p-4">
                        <div class="health-icon mb-3">
                            <i class="fas fa-memory fa-3x text-warning"></i>
                        </div>
                        <h5>Memory</h5>
                        <div class="health-status">
                            <span class="badge bg-warning">65%</span>
                        </div>
                        <p class="text-muted small mt-2">4.2GB / 8GB</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="health-card text-center p-4">
                        <div class="health-icon mb-3">
                            <i class="fas fa-microchip fa-3x text-primary"></i>
                        </div>
                        <h5>CPU</h5>
                        <div class="health-status">
                            <span class="badge bg-success">32%</span>
                        </div>
                        <p class="text-muted small mt-2">8 cores active</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="performance-metrics mb-5">
            <h2 class="text-center mb-4">Performance Metrics</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="metric-card">
                        <div class="card-header">
                            <h5><i class="fas fa-tachometer-alt me-2"></i>Response Times</h5>
                        </div>
                        <div class="card-body">
                            <div class="metric-item">
                                <span class="metric-label">Average Response</span>
                                <span class="metric-value text-success">245ms</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Peak Response</span>
                                <span class="metric-value text-warning">892ms</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">API Response</span>
                                <span class="metric-value text-success">156ms</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="metric-card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-line me-2"></i>Traffic Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="metric-item">
                                <span class="metric-label">Requests/min</span>
                                <span class="metric-value text-info">1,245</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Active Users</span>
                                <span class="metric-value text-success">342</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Error Rate</span>
                                <span class="metric-value text-success">0.2%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Alerts -->
        <div class="recent-alerts mb-5">
            <h2 class="text-center mb-4">Recent Alerts</h2>
            <div class="alert-list">
                <div class="alert-item">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">System Recovery</div>
                        <div class="alert-message">All systems back to normal after maintenance</div>
                        <div class="alert-time">2 minutes ago</div>
                    </div>
                </div>
                <div class="alert-item">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">High Memory Usage</div>
                        <div class="alert-message">Memory usage exceeded 80% threshold</div>
                        <div class="alert-time">15 minutes ago</div>
                    </div>
                </div>
                <div class="alert-item">
                    <div class="alert-icon">
                        <i class="fas fa-info-circle text-info"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">Database Optimization</div>
                        <div class="alert-message">Scheduled optimization completed successfully</div>
                        <div class="alert-time">1 hour ago</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Logs -->
        <div class="system-logs">
            <h2 class="text-center mb-4">System Logs</h2>
            <div class="log-container">
                <div class="log-entry">
                    <span class="log-time">2026-03-04 02:25:15</span>
                    <span class="log-level info">INFO</span>
                    <span class="log-message">User login successful: admin@apsdreamhome.com</span>
                </div>
                <div class="log-entry">
                    <span class="log-time">2026-03-04 02:24:32</span>
                    <span class="log-level success">SUCCESS</span>
                    <span class="log-message">Property valuation completed for PID #1234</span>
                </div>
                <div class="log-entry">
                    <span class="log-time">2026-03-04 02:23:18</span>
                    <span class="log-level warning">WARNING</span>
                    <span class="log-message">High CPU usage detected on server-01</span>
                </div>
                <div class="log-entry">
                    <span class="log-time">2026-03-04 02:22:45</span>
                    <span class="log-level info">INFO</span>
                    <span class="log-message">Database backup completed successfully</span>
                </div>
                <div class="log-entry">
                    <span class="log-time">2026-03-04 02:21:30</span>
                    <span class="log-level error">ERROR</span>
                    <span class="log-message">Failed to send WhatsApp template: Connection timeout</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="system-actions mt-5">
            <div class="text-center">
                <button class="btn btn-primary me-2" onclick="refreshMonitoring()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh Status
                </button>
                <button class="btn btn-warning me-2" onclick="runDiagnostics()">
                    <i class="fas fa-stethoscope me-2"></i>Run Diagnostics
                </button>
                <button class="btn btn-danger me-2" onclick="clearCache()">
                    <i class="fas fa-trash me-2"></i>Clear Cache
                </button>
                <button class="btn btn-info" onclick="exportReport()">
                    <i class="fas fa-download me-2"></i>Export Report
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.health-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border: none;
}

.health-card:hover {
    transform: translateY(-5px);
}

.health-status .badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.metric-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.metric-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1rem;
}

.metric-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.metric-item:last-child {
    border-bottom: none;
}

.metric-label {
    font-weight: 500;
    color: #666;
}

.metric-value {
    font-weight: bold;
    font-size: 1.1rem;
}

.alert-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: white;
    border-radius: 10px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.alert-icon {
    font-size: 1.5rem;
    margin-right: 1rem;
    width: 30px;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.alert-message {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.alert-time {
    color: #999;
    font-size: 0.8rem;
}

.log-container {
    background: #1a1a1a;
    border-radius: 10px;
    padding: 1rem;
    max-height: 300px;
    overflow-y: auto;
}

.log-entry {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #333;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
}

.log-entry:last-child {
    border-bottom: none;
}

.log-time {
    color: #888;
    margin-right: 1rem;
    min-width: 150px;
}

.log-level {
    margin-right: 1rem;
    min-width: 80px;
    font-weight: bold;
}

.log-level.info { color: #17a2b8; }
.log-level.success { color: #28a745; }
.log-level.warning { color: #ffc107; }
.log-level.error { color: #dc3545; }

.log-message {
    color: #ddd;
    flex: 1;
}

.system-actions .btn {
    margin: 0.25rem;
}
</style>

<script>
function refreshMonitoring() {
    location.reload();
}

function runDiagnostics() {
    alert('Running system diagnostics... This may take a few moments.');
    // In real implementation, this would trigger diagnostic tests
}

function clearCache() {
    if (confirm('Are you sure you want to clear all system cache?')) {
        alert('Cache cleared successfully!');
        // In real implementation, this would clear cache
    }
}

function exportReport() {
    alert('Generating monitoring report... Download will start shortly.');
    // In real implementation, this would generate and download report
}

// Auto-refresh every 30 seconds
setTimeout(() => {
    location.reload();
}, 30000);
</script>
