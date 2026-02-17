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
                    <a href="/admin/database" class="nav-link active">Database</a>
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
                                <h2>Database Management</h2>
                                <p class="text-muted">Monitor and manage your database</p>
                            </div>
                            <div>
                                <button class="btn btn-success" onclick="createBackup()">
                                    <i class="fas fa-download me-2"></i>Create Backup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $dbStats['total_tables'] ?? 0; ?></h3>
                                <p>Tables</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-hdd"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo formatFileSize($dbStats['total_size'] ?? 0); ?></h3>
                                <p>Database Size</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo count($backupFiles ?? []); ?></h3>
                                <p>Backup Files</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Connected</h3>
                                <p>Status</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Tables -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="table-card">
                            <div class="card-header">
                                <h5><i class="fas fa-table me-2"></i>Database Tables</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Table Name</th>
                                                <th>Rows</th>
                                                <th>Size</th>
                                                <th>Engine</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>users</td>
                                                <td>25</td>
                                                <td>16 KB</td>
                                                <td>InnoDB</td>
                                                <td>2024-01-15</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTable('users')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="optimizeTable('users')">
                                                        <i class="fas fa-magic"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>properties</td>
                                                <td>150</td>
                                                <td>2.5 MB</td>
                                                <td>InnoDB</td>
                                                <td>2024-01-15</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTable('properties')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="optimizeTable('properties')">
                                                        <i class="fas fa-magic"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>leads</td>
                                                <td>89</td>
                                                <td>512 KB</td>
                                                <td>InnoDB</td>
                                                <td>2024-01-15</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTable('leads')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="optimizeTable('leads')">
                                                        <i class="fas fa-magic"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>contact_messages</td>
                                                <td>45</td>
                                                <td>128 KB</td>
                                                <td>InnoDB</td>
                                                <td>2024-01-15</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTable('contact_messages')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="optimizeTable('contact_messages')">
                                                        <i class="fas fa-magic"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Management -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="backup-card">
                            <div class="card-header">
                                <h5><i class="fas fa-cloud-upload-alt me-2"></i>Backup Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="backup-info mb-4">
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Total Backups:</strong>
                                            <div class="backup-count"><?php echo count($backupFiles ?? []); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <strong>Total Size:</strong>
                                            <div class="backup-size"><?php echo formatFileSize(array_sum(array_column($backupFiles ?? [], 'size'))); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="backup-actions">
                                    <button class="btn btn-success w-100 mb-2" onclick="createBackup()">
                                        <i class="fas fa-plus me-2"></i>Create New Backup
                                    </button>
                                    <button class="btn btn-info w-100 mb-2" onclick="downloadAllBackups()">
                                        <i class="fas fa-download me-2"></i>Download All Backups
                                    </button>
                                    <button class="btn btn-warning w-100" onclick="cleanupOldBackups()">
                                        <i class="fas fa-trash me-2"></i>Cleanup Old Backups
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="backup-card">
                            <div class="card-header">
                                <h5><i class="fas fa-history me-2"></i>Recent Backups</h5>
                            </div>
                            <div class="card-body">
                                <div class="backup-list">
                                    <?php if (!empty($backupFiles)): ?>
                                        <?php foreach (array_slice($backupFiles, 0, 5) as $backup): ?>
                                        <div class="backup-item">
                                            <div class="backup-info">
                                                <div class="backup-name"><?php echo htmlspecialchars($backup['name']); ?></div>
                                                <div class="backup-meta">
                                                    <?php echo formatFileSize($backup['size']); ?> • <?php echo date('M d, Y H:i', strtotime($backup['modified'])); ?>
                                                </div>
                                            </div>
                                            <div class="backup-actions">
                                                <button class="btn btn-sm btn-outline-primary" onclick="downloadBackup('<?php echo $backup['name']; ?>')">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteBackup('<?php echo $backup['name']; ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No backups found</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Health -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="health-card">
                            <div class="card-header">
                                <h5><i class="fas fa-heartbeat me-2"></i>Database Health</h5>
                            </div>
                            <div class="card-body">
                                <div class="health-metrics">
                                    <div class="health-item">
                                        <div class="health-indicator health-good"></div>
                                        <span>Database Connection</span>
                                        <span class="health-status">Healthy</span>
                                    </div>
                                    <div class="health-item">
                                        <div class="health-indicator health-good"></div>
                                        <span>Query Performance</span>
                                        <span class="health-status">Good</span>
                                    </div>
                                    <div class="health-item">
                                        <div class="health-indicator health-warning"></div>
                                        <span>Storage Usage</span>
                                        <span class="health-status">75%</span>
                                    </div>
                                    <div class="health-item">
                                        <div class="health-indicator health-good"></div>
                                        <span>Last Backup</span>
                                        <span class="health-status">Today</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Create database backup
function createBackup() {
    if (confirm('Are you sure you want to create a database backup? This may take a few minutes.')) {
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Backup...';
        button.disabled = true;

        fetch('/admin/database/backup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Database backup created successfully!', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('Failed to create backup: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to create backup. Please try again.', 'error');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Download backup
function downloadBackup(filename) {
    window.open(`/admin/database/download/${filename}`, '_blank');
}

// Delete backup
function deleteBackup(filename) {
    if (confirm('Are you sure you want to delete this backup?')) {
        fetch(`/admin/database/delete/${filename}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Backup deleted successfully!', 'success');
                location.reload();
            } else {
                showToast('Failed to delete backup: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to delete backup. Please try again.', 'error');
        });
    }
}

// Download all backups
function downloadAllBackups() {
    window.open('/admin/database/download-all', '_blank');
}

// Cleanup old backups
function cleanupOldBackups() {
    if (confirm('Are you sure you want to delete backups older than 30 days?')) {
        fetch('/admin/database/cleanup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Old backups cleaned up successfully!', 'success');
                location.reload();
            } else {
                showToast('Failed to cleanup backups: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to cleanup backups. Please try again.', 'error');
        });
    }
}

// View table
function viewTable(tableName) {
    showToast(`Table view for ${tableName} coming soon!`, 'info');
}

// Optimize table
function optimizeTable(tableName) {
    if (confirm(`Are you sure you want to optimize the ${tableName} table?`)) {
        fetch(`/admin/database/optimize/${tableName}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`Table ${tableName} optimized successfully!`, 'success');
            } else {
                showToast('Failed to optimize table: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to optimize table. Please try again.', 'error');
        });
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 20px;
}

.table-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.backup-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: 100%;
}

.backup-info {
    margin-bottom: 20px;
}

.backup-count, .backup-size {
    font-size: 1.5rem;
    font-weight: bold;
    color: #667eea;
}

.backup-list {
    max-height: 300px;
    overflow-y: auto;
}

.backup-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.backup-item:last-child {
    border-bottom: none;
}

.backup-meta {
    font-size: 0.85rem;
    color: #666;
}

.health-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.health-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.health-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
}

.health-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 10px;
}

.health-status {
    margin-left: auto;
    font-weight: 600;
}

@media (max-width: 768px) {
    .admin-sidebar {
        margin-bottom: 20px;
    }

    .health-metrics {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../app/views/includes/footer.php'; ?>
