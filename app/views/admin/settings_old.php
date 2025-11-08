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
                    <a href="/admin/settings" class="nav-link active">Settings</a>
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
                                <h2>System Settings</h2>
                                <p class="text-muted">Configure your application settings and preferences</p>
                            </div>
                            <div>
                                <button class="btn btn-success" onclick="saveAllSettings()">
                                    <i class="fas fa-save me-2"></i>Save All Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tabs -->
                <div class="row">
                    <div class="col-12">
                        <div class="settings-container">
                            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                                        <i class="fas fa-cog me-2"></i>General
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button">
                                        <i class="fas fa-database me-2"></i>Database
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
                                        <i class="fas fa-shield-alt me-2"></i>Security
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>Backup
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="settingsTabsContent">
                                <!-- General Settings -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="settings-section">
                                        <h4 class="section-title">General Settings</h4>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Site Title</label>
                                                    <input type="text" class="form-control" id="site_title" value="APS Dream Home">
                                                    <small class="form-text text-muted">The main title of your website</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Site Description</label>
                                                    <textarea class="form-control" id="site_description" rows="3">Premium real estate platform for buying, selling, and renting properties in Gorakhpur and across India</textarea>
                                                    <small class="form-text text-muted">Brief description of your website</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Default Language</label>
                                                    <select class="form-select" id="default_language">
                                                        <option value="en" selected>English</option>
                                                        <option value="hi">Hindi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Timezone</label>
                                                    <select class="form-select" id="timezone">
                                                        <option value="Asia/Kolkata" selected>Asia/Kolkata</option>
                                                        <option value="Asia/Delhi">Asia/Delhi</option>
                                                        <option value="UTC">UTC</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Items Per Page</label>
                                                    <input type="number" class="form-control" id="items_per_page" value="20" min="5" max="100">
                                                    <small class="form-text text-muted">Number of items to display per page</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Maintenance Mode</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="maintenance_mode">
                                                        <label class="form-check-label" for="maintenance_mode">
                                                            Enable maintenance mode
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">When enabled, only admins can access the site</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Settings -->
                                <div class="tab-pane fade" id="email" role="tabpanel">
                                    <div class="settings-section">
                                        <h4 class="section-title">Email Configuration</h4>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">SMTP Host</label>
                                                    <input type="text" class="form-control" id="smtp_host" value="smtp.gmail.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">SMTP Port</label>
                                                    <input type="number" class="form-control" id="smtp_port" value="587">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">SMTP Username</label>
                                                    <input type="email" class="form-control" id="smtp_username" value="noreply@apsdreamhome.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">SMTP Password</label>
                                                    <input type="password" class="form-control" id="smtp_password" value="">
                                                    <small class="form-text text-muted">Leave empty to keep current password</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Email Notifications</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="email_new_lead" checked>
                                                        <label class="form-check-label" for="email_new_lead">
                                                            Notify on new leads
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="email_property_inquiry" checked>
                                                        <label class="form-check-label" for="email_property_inquiry">
                                                            Notify on property inquiries
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Test Email</label>
                                                    <button class="btn btn-outline-primary" onclick="testEmailSettings()">
                                                        <i class="fas fa-paper-plane me-2"></i>Send Test Email
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Database Settings -->
                                <div class="tab-pane fade" id="database" role="tabpanel">
                                    <div class="settings-section">
                                        <h4 class="section-title">Database Configuration</h4>

                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Database settings are configured in the config file. Contact your system administrator for changes.
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Database Host</label>
                                                    <input type="text" class="form-control" value="localhost" disabled>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Database Name</label>
                                                    <input type="text" class="form-control" value="aps_dream_home" disabled>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Database Backup</label>
                                                    <button class="btn btn-warning" onclick="createBackup()">
                                                        <i class="fas fa-download me-2"></i>Create Backup
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Database Status</label>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-success me-2">Connected</span>
                                                        <small class="text-muted">Last checked: <?php echo date('M d, Y H:i'); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Security Settings -->
                                <div class="tab-pane fade" id="security" role="tabpanel">
                                    <div class="settings-section">
                                        <h4 class="section-title">Security Settings</h4>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Session Timeout (minutes)</label>
                                                    <input type="number" class="form-control" id="session_timeout" value="60" min="15" max="480">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Max Login Attempts</label>
                                                    <input type="number" class="form-control" id="max_login_attempts" value="5" min="3" max="10">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Password Requirements</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="require_special_chars" checked>
                                                        <label class="form-check-label" for="require_special_chars">
                                                            Require special characters
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="require_numbers" checked>
                                                        <label class="form-check-label" for="require_numbers">
                                                            Require numbers
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Security Features</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="enable_csrf" checked>
                                                        <label class="form-check-label" for="enable_csrf">
                                                            Enable CSRF protection
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="enable_rate_limiting" checked>
                                                        <label class="form-check-label" for="enable_rate_limiting">
                                                            Enable rate limiting
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Backup Settings -->
                                <div class="tab-pane fade" id="backup" role="tabpanel">
                                    <div class="settings-section">
                                        <h4 class="section-title">Backup Configuration</h4>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Auto Backup</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="auto_backup" checked>
                                                        <label class="form-check-label" for="auto_backup">
                                                            Enable automatic backups
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Backup Frequency</label>
                                                    <select class="form-select" id="backup_frequency">
                                                        <option value="daily" selected>Daily</option>
                                                        <option value="weekly">Weekly</option>
                                                        <option value="monthly">Monthly</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Backup Retention (days)</label>
                                                    <input type="number" class="form-control" id="backup_retention" value="30" min="7" max="365">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="setting-group">
                                                    <label class="setting-label">Last Backup</label>
                                                    <p class="form-control-plaintext">Today at 2:30 AM</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="backup-actions">
                                                    <button class="btn btn-success me-2" onclick="createBackup()">
                                                        <i class="fas fa-download me-2"></i>Create Backup Now
                                                    </button>
                                                    <button class="btn btn-warning me-2" onclick="downloadLatestBackup()">
                                                        <i class="fas fa-cloud-download-alt me-2"></i>Download Latest
                                                    </button>
                                                    <button class="btn btn-danger" onclick="cleanupOldBackups()">
                                                        <i class="fas fa-trash me-2"></i>Cleanup Old Backups
                                                    </button>
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
        </div>
    </div>
</div>

<script>
// Save all settings
function saveAllSettings() {
    const settings = {
        site_title: document.getElementById('site_title').value,
        site_description: document.getElementById('site_description').value,
        default_language: document.getElementById('default_language').value,
        timezone: document.getElementById('timezone').value,
        items_per_page: document.getElementById('items_per_page').value,
        maintenance_mode: document.getElementById('maintenance_mode').checked,
        smtp_host: document.getElementById('smtp_host').value,
        smtp_port: document.getElementById('smtp_port').value,
        smtp_username: document.getElementById('smtp_username').value,
        session_timeout: document.getElementById('session_timeout').value,
        max_login_attempts: document.getElementById('max_login_attempts').value,
        auto_backup: document.getElementById('auto_backup').checked,
        backup_frequency: document.getElementById('backup_frequency').value,
        backup_retention: document.getElementById('backup_retention').value
    };

    fetch('/admin/settings/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ settings })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Settings saved successfully!', 'success');
        } else {
            showToast('Failed to save settings: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to save settings. Please try again.', 'error');
    });
}

// Test email settings
function testEmailSettings() {
    const testEmail = prompt('Enter email address to send test email:');
    if (testEmail) {
        fetch('/admin/settings/test-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: testEmail })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Test email sent successfully!', 'success');
            } else {
                showToast('Failed to send test email: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to send test email. Please try again.', 'error');
        });
    }
}

// Create backup
function createBackup() {
    if (confirm('Are you sure you want to create a database backup?')) {
        fetch('/admin/database/backup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Backup created successfully!', 'success');
            } else {
                showToast('Failed to create backup: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to create backup. Please try again.', 'error');
        });
    }
}

// Download latest backup
function downloadLatestBackup() {
    window.open('/admin/database/download-latest', '_blank');
}

// Cleanup old backups
function cleanupOldBackups() {
    if (confirm('Are you sure you want to delete old backup files?')) {
        fetch('/admin/database/cleanup-backups', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Old backups cleaned up successfully!', 'success');
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

.settings-container {
    background: white;
    border-radius: 10px;
    overflow: hidden;
}

.section-title {
    color: #1a237e;
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
    margin-bottom: 30px;
}

.setting-group {
    margin-bottom: 25px;
}

.setting-label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
    color: #333;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.backup-actions {
    padding: 20px 0;
    border-top: 1px solid #eee;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .admin-sidebar {
        margin-bottom: 20px;
    }
}
</style>

<?php include '../app/views/includes/footer.php'; ?>
