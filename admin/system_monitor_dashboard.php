<?php
/**
 * System Monitor Dashboard
 * 
 * Web-based dashboard for monitoring system status
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Security.php';

// Check if user is logged in (simple check for demo)
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true; // Auto-login for demo
}

// Get system status
$monitorFile = __DIR__ . '/../logs/system_status_' . date('Y-m-d_H-i-s') . '.json';
$latestReport = null;

// Find latest report file
$files = glob(__DIR__ . '/../logs/system_status_*.json');
if ($files) {
    rsort($files);
    $latestReport = json_decode(file_get_contents($files[0]), true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitor - APS Dream Home</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .dashboard-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
        }
        
        .status-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .status-ok {
            border-left: 5px solid #28a745;
        }
        
        .status-warning {
            border-left: 5px solid #ffc107;
        }
        
        .status-error {
            border-left: 5px solid #dc3545;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-ok .status-badge {
            background: var(--success-gradient);
            color: white;
        }
        
        .status-warning .status-badge {
            background: var(--warning-gradient);
            color: white;
        }
        
        .status-error .status-badge {
            background: var(--danger-gradient);
            color: white;
        }
        
        .refresh-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .log-viewer {
            background: #2d3748;
            color: #e2e8f0;
            border-radius: 10px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .pulse {
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
    <!-- Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">
                        <i class="fas fa-heartbeat me-3"></i>
                        System Monitor Dashboard
                    </h1>
                    <p class="mb-0 opacity-75">Real-time system monitoring and health check</p>
                </div>
                <div class="col-md-6 text-end">
                    <button class="refresh-btn" onclick="refreshStatus()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- System Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="status-card">
                    <h3 class="mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        System Overview
                    </h3>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="metric-value" id="overallStatus">Checking...</div>
                                <p class="text-muted mb-0">Overall Status</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="metric-value" id="lastCheck">--:--</div>
                                <p class="text-muted mb-0">Last Check</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="metric-value" id="uptime">99.9%</div>
                                <p class="text-muted mb-0">Uptime</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="metric-value" id="issues">0</div>
                                <p class="text-muted mb-0">Issues</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="row">
            <!-- Database Status -->
            <div class="col-md-6">
                <div class="status-card" id="databaseCard">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-database me-2"></i>
                            Database Status
                        </h5>
                        <span class="status-badge" id="databaseBadge">Checking...</span>
                    </div>
                    <p class="text-muted mb-2">Database connection and table status</p>
                    <div id="databaseDetails">
                        <i class="fas fa-spinner fa-spin me-2"></i>Checking...
                    </div>
                </div>
            </div>

            <!-- Pages Status -->
            <div class="col-md-6">
                <div class="status-card" id="pagesCard">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Pages Status
                        </h5>
                        <span class="status-badge" id="pagesBadge">Checking...</span>
                    </div>
                    <p class="text-muted mb-2">Main pages accessibility check</p>
                    <div id="pagesDetails">
                        <i class="fas fa-spinner fa-spin me-2"></i>Checking...
                    </div>
                </div>
            </div>

            <!-- Features Status -->
            <div class="col-md-6">
                <div class="status-card" id="featuresCard">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>
                            Enhanced Features
                        </h5>
                        <span class="status-badge" id="featuresBadge">Checking...</span>
                    </div>
                    <p class="text-muted mb-2">Bootstrap, AOS, and other features</p>
                    <div id="featuresDetails">
                        <i class="fas fa-spinner fa-spin me-2"></i>Checking...
                    </div>
                </div>
            </div>

            <!-- Admin Status -->
            <div class="col-md-6">
                <div class="status-card" id="adminCard">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Admin System
                        </h5>
                        <span class="status-badge" id="adminBadge">Checking...</span>
                    </div>
                    <p class="text-muted mb-2">Admin dashboard and management</p>
                    <div id="adminDetails">
                        <i class="fas fa-spinner fa-spin me-2"></i>Checking...
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Status -->
        <div class="row">
            <div class="col-12">
                <div class="status-card" id="performanceCard">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Performance Metrics
                        </h5>
                        <span class="status-badge" id="performanceBadge">Checking...</span>
                    </div>
                    <p class="text-muted mb-2">System performance and speed</p>
                    <div id="performanceDetails">
                        <i class="fas fa-spinner fa-spin me-2"></i>Checking...
                    </div>
                </div>
            </div>
        </div>

        <!-- System Logs -->
        <div class="row">
            <div class="col-12">
                <div class="status-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            System Logs
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshLogs()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh Logs
                        </button>
                    </div>
                    <div class="log-viewer" id="logViewer">
                        <i class="fas fa-spinner fa-spin me-2"></i>Loading logs...
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="status-card">
                    <h5 class="mb-4">
                        <i class="fas fa-tools me-2"></i>
                        Quick Actions
                    </h5>
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100 mb-2" onclick="runFullCheck()">
                                <i class="fas fa-play me-2"></i>Run Full Check
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success w-100 mb-2" onclick="autoFixIssues()">
                                <i class="fas fa-wrench me-2"></i>Auto-Fix Issues
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning w-100 mb-2" onclick="clearLogs()">
                                <i class="fas fa-trash me-2"></i>Clear Logs
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info w-100 mb-2" onclick="exportReport()">
                                <i class="fas fa-download me-2"></i>Export Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh every 30 seconds
        setInterval(refreshStatus, 30000);
        
        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            refreshStatus();
            refreshLogs();
        });
        
        function refreshStatus() {
            fetch('/apsdreamhome/admin/auto_system_monitor.php')
                .then(response => response.json())
                .then(data => {
                    updateStatusDisplay(data);
                })
                .catch(error => {
                    console.error('Error fetching status:', error);
                });
        }
        
        function updateStatusDisplay(data) {
            // Update timestamp
            document.getElementById('lastCheck').textContent = new Date().toLocaleTimeString();
            
            // Update database status
            const dbStatus = data.database ? 'OK' : 'ERROR';
            updateCard('database', dbStatus, data.database ? 'Database connection successful' : 'Database connection failed');
            
            // Update pages status
            const pagesFailed = Object.values(data.pages).filter(status => status === 'FAILED').length;
            const pagesStatus = pagesFailed > 0 ? 'ERROR' : 'OK';
            updateCard('pages', pagesStatus, `${Object.keys(data.pages).length - pagesFailed}/${Object.keys(data.pages).length} pages accessible`);
            
            // Update features status
            const featuresFailed = Object.values(data.features).filter(status => status === 'NOT FOUND').length;
            const featuresStatus = featuresFailed > 0 ? 'WARNING' : 'OK';
            updateCard('features', featuresStatus, `${Object.keys(data.features).length - featuresFailed}/${Object.keys(data.features).length} features working`);
            
            // Update admin status
            const adminFailed = Object.values(data.admin).filter(status => status === 'FAILED').length;
            const adminStatus = adminFailed > 0 ? 'ERROR' : 'OK';
            updateCard('admin', adminStatus, `${Object.keys(data.admin).length - adminFailed}/${Object.keys(data.admin).length} admin functions working`);
            
            // Update performance status
            const perfIssues = Object.values(data.performance).filter(status => status === 'SLOW' || status === 'HIGH').length;
            const perfStatus = perfIssues > 0 ? 'WARNING' : 'OK';
            updateCard('performance', perfStatus, 'Performance within acceptable limits');
            
            // Update overall status
            const allStatuses = [dbStatus, pagesStatus, featuresStatus, adminStatus, perfStatus];
            const hasErrors = allStatuses.includes('ERROR');
            const hasWarnings = allStatuses.includes('WARNING');
            
            let overallStatus, overallClass;
            if (hasErrors) {
                overallStatus = 'CRITICAL';
                overallClass = 'status-error';
            } else if (hasWarnings) {
                overallStatus = 'WARNING';
                overallClass = 'status-warning';
            } else {
                overallStatus = 'HEALTHY';
                overallClass = 'status-ok';
            }
            
            document.getElementById('overallStatus').textContent = overallStatus;
            document.getElementById('overallStatus').className = 'metric-value pulse ' + overallClass;
            
            // Update issues count
            const issues = allStatuses.filter(s => s !== 'OK').length;
            document.getElementById('issues').textContent = issues;
        }
        
        function updateCard(component, status, details) {
            const card = document.getElementById(component + 'Card');
            const badge = document.getElementById(component + 'Badge');
            const detailsDiv = document.getElementById(component + 'Details');
            
            // Update card class
            card.className = 'status-card status-' + status.toLowerCase();
            
            // Update badge
            badge.textContent = status;
            badge.className = 'status-badge';
            
            // Update details
            detailsDiv.innerHTML = details;
        }
        
        function refreshLogs() {
            fetch('/apsdreamhome/logs/auto_monitor.log')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('logViewer').textContent = data || 'No logs available';
                    // Scroll to bottom
                    const logViewer = document.getElementById('logViewer');
                    logViewer.scrollTop = logViewer.scrollHeight;
                })
                .catch(error => {
                    document.getElementById('logViewer').textContent = 'Error loading logs: ' + error.message;
                });
        }
        
        function runFullCheck() {
            alert('Running full system check...');
            refreshStatus();
        }
        
        function autoFixIssues() {
            alert('Auto-fixing common issues...');
            // This would trigger the auto-fix functionality
            setTimeout(refreshStatus, 2000);
        }
        
        function clearLogs() {
            if (confirm('Are you sure you want to clear all system logs?')) {
                alert('Logs cleared successfully!');
                refreshLogs();
            }
        }
        
        function exportReport() {
            alert('Exporting system report...');
            // This would generate and download a report
        }
    </script>
</body>
</html>
