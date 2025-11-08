<?php include '../app/views/includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar">
                <div class="sidebar-header">
                    <h5><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</h5>
                </div>
                <nav class="nav nav-pills flex-column">
                    <a href="/admin" class="nav-link">Dashboard</a>
                    <a href="/admin/properties" class="nav-link">Properties</a>
                    <a href="/admin/leads" class="nav-link">Leads</a>
                    <a href="/admin/users" class="nav-link">Users</a>
                    <a href="/admin/reports" class="nav-link">Reports</a>
                    <a href="/admin/settings" class="nav-link">Settings</a>
                    <a href="/admin/database" class="nav-link">Database</a>
                    <a href="/admin/logs" class="nav-link active">Logs</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="admin-content">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2>System Logs</h2>
                                <p class="text-muted">Monitor system activities and errors</p>
                            </div>
                            <div>
                                <button class="btn btn-danger" onclick="clearLogs()">
                                    <i class="fas fa-trash me-2"></i>Clear All Logs
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="log-filters">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select" id="logType">
                                        <option value="error" <?php echo ($logType ?? '') === 'error' ? 'selected' : ''; ?>>Error Logs</option>
                                        <option value="access" <?php echo ($logType ?? '') === 'access' ? 'selected' : ''; ?>>Access Logs</option>
                                        <option value="debug" <?php echo ($logType ?? '') === 'debug' ? 'selected' : ''; ?>>Debug Logs</option>
                                        <option value="info" <?php echo ($logType ?? '') === 'info' ? 'selected' : ''; ?>>Info Logs</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="logLines">
                                        <option value="50" <?php echo ($lines ?? 100) == 50 ? 'selected' : ''; ?>>Last 50 lines</option>
                                        <option value="100" <?php echo ($lines ?? 100) == 100 ? 'selected' : ''; ?>>Last 100 lines</option>
                                        <option value="200" <?php echo ($lines ?? 100) == 200 ? 'selected' : ''; ?>>Last 200 lines</option>
                                        <option value="500" <?php echo ($lines ?? 100) == 500 ? 'selected' : ''; ?>>Last 500 lines</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" id="logSearch" placeholder="Search logs...">
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary me-2" onclick="refreshLogs()">
                                        <i class="fas fa-refresh me-2"></i>Refresh
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="downloadLogs()">
                                        <i class="fas fa-download me-2"></i>Download
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="log-stat-card">
                            <div class="log-stat-icon error">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="log-stat-info">
                                <h4 id="errorCount">0</h4>
                                <p>Errors Today</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="log-stat-card">
                            <div class="log-stat-icon warning">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="log-stat-info">
                                <h4 id="warningCount">0</h4>
                                <p>Warnings Today</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="log-stat-card">
                            <div class="log-stat-icon info">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="log-stat-info">
                                <h4 id="infoCount">0</h4>
                                <p>Info Messages</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="log-stat-card">
                            <div class="log-stat-icon total">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="log-stat-info">
                                <h4 id="totalLogs">0</h4>
                                <p>Total Entries</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Viewer -->
                <div class="row">
                    <div class="col-12">
                        <div class="log-viewer">
                            <div class="log-viewer-header">
                                <h5><i class="fas fa-file-text me-2"></i>Log Viewer</h5>
                                <div class="log-controls">
                                    <button class="btn btn-sm btn-outline-primary" onclick="toggleAutoRefresh()">
                                        <i class="fas fa-play me-1" id="autoRefreshIcon"></i>
                                        Auto Refresh
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="clearLogFilters()">
                                        <i class="fas fa-filter me-1"></i>
                                        Clear Filters
                                    </button>
                                </div>
                            </div>
                            <div class="log-content">
                                <div class="log-entries" id="logEntries">
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                        <div class="log-entry log-<?php echo strtolower($log['level']); ?>">
                                            <div class="log-timestamp">
                                                <?php echo htmlspecialchars($log['timestamp']); ?>
                                            </div>
                                            <div class="log-level">
                                                <span class="badge bg-<?php echo getLogLevelColor($log['level']); ?>">
                                                    <?php echo htmlspecialchars($log['level']); ?>
                                                </span>
                                            </div>
                                            <div class="log-message">
                                                <?php echo htmlspecialchars($log['message']); ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-logs text-center py-5">
                                            <i class="fas fa-file-text fa-3x text-muted mb-3"></i>
                                            <h5>No log entries found</h5>
                                            <p class="text-muted">No logs match your current filters.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Files -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="log-files">
                            <h5>Available Log Files</h5>
                            <div class="log-files-grid">
                                <?php if (!empty($availableLogs)): ?>
                                    <?php foreach ($availableLogs as $logFile): ?>
                                    <div class="log-file-card">
                                        <div class="log-file-info">
                                            <h6><?php echo htmlspecialchars($logFile['name']); ?></h6>
                                            <div class="log-file-meta">
                                                <span class="file-size"><?php echo formatFileSize($logFile['size']); ?></span>
                                                <span class="file-date"><?php echo date('M d, Y H:i', strtotime($logFile['modified'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="log-file-actions">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewLogFile('<?php echo $logFile['name']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="downloadLogFile('<?php echo $logFile['name']; ?>')">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteLogFile('<?php echo $logFile['name']; ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No log files found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Refresh logs
function refreshLogs() {
    const logType = document.getElementById('logType').value;
    const logLines = document.getElementById('logLines').value;
    const logSearch = document.getElementById('logSearch').value;

    const url = `/admin/logs?type=${logType}&lines=${logLines}${logSearch ? `&search=${encodeURIComponent(logSearch)}` : ''}`;
    window.location.href = url;
}

// Auto refresh functionality
let autoRefreshInterval = null;

function toggleAutoRefresh() {
    const button = event.target.closest('button');
    const icon = document.getElementById('autoRefreshIcon');

    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        button.classList.remove('active');
        icon.classList.remove('fa-pause');
        icon.classList.add('fa-play');
        showToast('Auto refresh stopped', 'info');
    } else {
        autoRefreshInterval = setInterval(refreshLogs, 30000); // Refresh every 30 seconds
        button.classList.add('active');
        icon.classList.remove('fa-play');
        icon.classList.add('fa-pause');
        showToast('Auto refresh started (30 seconds)', 'success');
    }
}

// Download logs
function downloadLogs() {
    const logType = document.getElementById('logType').value;
    const logLines = document.getElementById('logLines').value;
    window.open(`/admin/logs/download?type=${logType}&lines=${logLines}`, '_blank');
}

// Clear logs
function clearLogs() {
    const logType = document.getElementById('logType').value;

    if (confirm(`Are you sure you want to clear all ${logType} logs? This action cannot be undone.`)) {
        fetch(`/admin/logs/clear`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ type: logType })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Logs cleared successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Failed to clear logs: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to clear logs. Please try again.', 'error');
        });
    }
}

// View log file
function viewLogFile(filename) {
    window.open(`/admin/logs/view/${filename}`, '_blank');
}

// Download log file
function downloadLogFile(filename) {
    window.open(`/admin/logs/download/${filename}`, '_blank');
}

// Delete log file
function deleteLogFile(filename) {
    if (confirm(`Are you sure you want to delete the log file ${filename}?`)) {
        fetch(`/admin/logs/delete/${filename}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Log file deleted successfully!', 'success');
                location.reload();
            } else {
                showToast('Failed to delete log file: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to delete log file. Please try again.', 'error');
        });
    }
}

// Clear log filters
function clearLogFilters() {
    document.getElementById('logSearch').value = '';
    document.getElementById('logType').value = 'error';
    document.getElementById('logLines').value = '100';
    refreshLogs();
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Get log level color
function getLogLevelColor(level) {
    const colors = {
        'ERROR': 'danger',
        'WARNING': 'warning',
        'INFO': 'info',
        'DEBUG': 'secondary'
    };
    return colors[level] || 'secondary';
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update log statistics
    updateLogStats();
});

function updateLogStats() {
    // This would typically fetch real-time stats from the server
    // For now, we'll just show placeholder data
    document.getElementById('errorCount').textContent = '3';
    document.getElementById('warningCount').textContent = '12';
    document.getElementById('infoCount').textContent = '45';
    document.getElementById('totalLogs').textContent = '60';
}
</script>

<style>
.admin-sidebar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.admin-content {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
}

.log-filters {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.log-stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.log-stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 20px;
}

.log-stat-icon.error { background: rgba(220, 53, 69, 0.3); }
.log-stat-icon.warning { background: rgba(255, 193, 7, 0.3); }
.log-stat-icon.info { background: rgba(23, 162, 184, 0.3); }
.log-stat-icon.total { background: rgba(108, 117, 125, 0.3); }

.log-viewer {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.log-viewer-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.log-content {
    max-height: 600px;
    overflow-y: auto;
    background: #1e1e1e;
    color: #fff;
    font-family: 'Courier New', monospace;
}

.log-entries {
    padding: 20px;
}

.log-entry {
    display: flex;
    margin-bottom: 10px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.log-entry:last-child {
    border-bottom: none;
}

.log-timestamp {
    width: 150px;
    font-size: 0.85rem;
    color: #888;
    flex-shrink: 0;
}

.log-level {
    width: 80px;
    text-align: center;
    flex-shrink: 0;
}

.log-message {
    flex: 1;
    word-wrap: break-word;
}

.log-error .log-message { color: #ff6b6b; }
.log-warning .log-message { color: #ffd93d; }
.log-info .log-message { color: #74c0fc; }
.log-debug .log-message { color: #b197fc; }

.log-files {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
}

.log-files-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.log-file-card {
    background: #f8f9fa;
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.log-file-meta {
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
}

.file-size, .file-date {
    display: block;
}

.log-file-actions {
    display: flex;
    gap: 5px;
}

.no-logs {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 60px 20px;
}

@media (max-width: 768px) {
    .admin-sidebar {
        margin-bottom: 20px;
    }

    .log-files-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../app/views/includes/footer.php'; ?>
