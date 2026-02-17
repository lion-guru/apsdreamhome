<?php
require_once 'core/init.php';

$page_title = "Notification Debugger";
require_once 'includes/admin_header.php';
require_once 'includes/admin_sidebar.php';
?>

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

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles mb-4">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor"><?php echo $mlSupport->translate('Notification Debugger'); ?></h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php"><?php echo $mlSupport->translate('Dashboard'); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo $mlSupport->translate('Notification Debugger'); ?></li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="btn-toolbar justify-content-end">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearLogs()">
                            <i class="fas fa-trash me-1"></i> <?php echo $mlSupport->translate('Clear Logs'); ?>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="exportLogs()">
                            <i class="fas fa-download me-1"></i> <?php echo $mlSupport->translate('Export Logs'); ?>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-primary" onclick="toggleAutoRefresh()">
                        <i class="fas fa-sync-alt me-1"></i> <span id="refreshStatus"><?php echo $mlSupport->translate('Auto-refresh On'); ?></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Anomaly Detection -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Anomaly Detection'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="anomalies"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Monitoring -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Queue Monitor'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="notification-flow" id="queueFlow">
                            <div class="flow-step text-center">
                                <div class="text-muted small mb-1"><?php echo $mlSupport->translate('Pending'); ?></div>
                                <h4 id="pendingCount" class="mb-0">0</h4>
                            </div>
                            <div class="flow-arrow text-center my-2"><i class="fas fa-arrow-down"></i></div>
                            <div class="flow-step text-center">
                                <div class="text-muted small mb-1"><?php echo $mlSupport->translate('Processing'); ?></div>
                                <h4 id="processingCount" class="mb-0">0</h4>
                            </div>
                            <div class="flow-arrow text-center my-2"><i class="fas fa-arrow-down"></i></div>
                            <div class="flow-step text-center">
                                <div class="text-muted small mb-1"><?php echo $mlSupport->translate('Completed/Failed'); ?></div>
                                <h4 id="completedCount" class="mb-0">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Performance Monitor'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="text-muted small d-block"><?php echo $mlSupport->translate('Avg. Processing Time'); ?></label>
                                    <h3 id="avgProcessingTime" class="fw-bold">0ms</h3>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="text-muted small d-block"><?php echo $mlSupport->translate('Success Rate'); ?></label>
                                    <h3 id="successRate" class="fw-bold">0%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" id="successRateBar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Logs -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Live Logs'); ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-3 g-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="logFilter" placeholder="<?php echo $mlSupport->translate('Filter logs...'); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="logLevel">
                            <option value="all"><?php echo $mlSupport->translate('All Levels'); ?></option>
                            <option value="error"><?php echo $mlSupport->translate('Errors'); ?></option>
                            <option value="warning"><?php echo $mlSupport->translate('Warnings'); ?></option>
                            <option value="info"><?php echo $mlSupport->translate('Info'); ?></option>
                            <option value="success"><?php echo $mlSupport->translate('Success'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="applyFilter()">
                            <i class="fas fa-filter me-1"></i> <?php echo $mlSupport->translate('Apply'); ?>
                        </button>
                    </div>
                </div>
                <div class="log-container border rounded bg-light" style="height: 400px; overflow-y: auto; padding: 15px;">
                    <div id="logEntries"></div>
                </div>
            </div>
        </div>

        <!-- Notification Testing -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><?php echo $mlSupport->translate('Test Notification'); ?></h5>
            </div>
            <div class="card-body">
                <form id="testForm" class="row g-3">
                    <?php echo getCsrfField(); ?>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo $mlSupport->translate('Notification Type'); ?></label>
                        <select class="form-select" id="testType" required>
                            <option value="email"><?php echo $mlSupport->translate('Email'); ?></option>
                            <option value="sms"><?php echo $mlSupport->translate('SMS'); ?></option>
                            <option value="both"><?php echo $mlSupport->translate('Both'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo $mlSupport->translate('Recipient'); ?></label>
                        <select class="form-select" id="testRecipient" required>
                            <?php
                            $results = \App\Core\App::database()->fetchAll("SELECT uid as id, uemail as email, uphone as phone FROM user WHERE uemail IS NOT NULL");
                            foreach ($results as $user) {
                                $display = h($user['email']) . ($user['phone'] ? " / " . h($user['phone']) : "");
                                echo "<option value='" . h($user['id']) . "'>$display</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo $mlSupport->translate('Template'); ?></label>
                        <select class="form-select" id="testTemplate" required>
                            <?php
                            $results = \App\Core\App::database()->fetchAll("SELECT type FROM notification_templates");
                            foreach ($results as $template) {
                                echo "<option value='" . h($template['type']) . "'>" . h($template['type']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> <?php echo $mlSupport->translate('Send Test Notification'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = '<?php echo generateCSRFToken(); ?>';
    let autoRefresh = true;
    let refreshInterval;

    function h(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

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
            autoRefresh ? '<?php echo $mlSupport->translate('Auto-refresh On'); ?>' : '<?php echo $mlSupport->translate('Auto-refresh Off'); ?>';
        
        if (autoRefresh) {
            startAutoRefresh();
        } else {
            clearInterval(refreshInterval);
        }
    }

    function refreshData() {
        fetch('../api/notification_debug.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.message || data.error);
                updateQueueMonitor(data.queue);
                updatePerformanceMonitor(data.performance);
                updateLogs(data.logs);
                updateAnomalies(data.anomalies);
            })
            .catch(error => console.error('Error refreshing data:', error));
    }

    function updateQueueMonitor(queue) {
        document.getElementById('pendingCount').textContent = queue.pending || 0;
        document.getElementById('processingCount').textContent = queue.processing || 0;
        document.getElementById('completedCount').textContent = 
            (queue.completed || 0) + ' / ' + (queue.failed || 0);
    }

    function updatePerformanceMonitor(performance) {
        document.getElementById('avgProcessingTime').textContent = 
            (performance.avgProcessingTime || 0) + 'ms';
        document.getElementById('successRate').textContent = 
            (performance.successRate || 0) + '%';
        
        const bar = document.getElementById('successRateBar');
        bar.style.width = (performance.successRate || 0) + '%';
        bar.className = 'progress-bar ' + 
            (performance.successRate >= 90 ? 'bg-success' : 
             performance.successRate >= 70 ? 'bg-warning' : 'bg-danger');
    }

    function updateLogs(logs) {
        if (!logs) return;
        const container = document.getElementById('logEntries');
        const filter = document.getElementById('logFilter').value.toLowerCase();
        const level = document.getElementById('logLevel').value;

        let html = '';
        logs.forEach(log => {
            if (shouldShowLog(log, filter, level)) {
                html += `
                    <div class="log-entry log-${h(log.level)} mb-1">
                        <small class="text-muted">[${h(log.timestamp)}]</small> ${h(log.message)}
                    </div>
                `;
            }
        });

        container.innerHTML = html || '<div class="text-muted text-center"><?php echo $mlSupport->translate('No logs found'); ?></div>';
    }

    function updateAnomalies(anomalies) {
        if (!anomalies) return;
        const container = document.getElementById('anomalies');
        let html = '';

        anomalies.forEach(anomaly => {
            html += `
                <div class="card mb-2 anomaly-card anomaly-${h(anomaly.severity)} border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-uppercase small ${anomaly.severity === 'critical' ? 'text-danger' : 'text-warning'}">
                                ${h(anomaly.severity)}
                            </h6>
                            <small class="text-muted">${h(anomaly.timestamp)}</small>
                        </div>
                        <p class="card-text mb-1">${h(anomaly.message)}</p>
                        ${anomaly.details ? `<small class="text-muted d-block">${h(anomaly.details)}</small>` : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html || '<div class="text-muted text-center"><?php echo $mlSupport->translate('No anomalies detected'); ?></div>';
    }

    function shouldShowLog(log, filter, level) {
        if (level !== 'all' && log.level !== level) return false;
        if (filter && !log.message.toLowerCase().includes(filter)) return false;
        return true;
    }

    function applyFilter() {
        refreshData();
    }

    function clearLogs() {
        if (confirm('<?php echo $mlSupport->translate('Are you sure you want to clear all logs?'); ?>')) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            
            fetch('../api/notification_debug.php?action=clear', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('logEntries').innerHTML = '';
                    refreshData();
                } else {
                    alert(data.message || 'Error clearing logs');
                }
            })
            .catch(error => console.error('Error clearing logs:', error));
        }
    }

    function exportLogs() {
        window.location.href = '../api/notification_debug.php?action=export';
    }

    function setupTestForm() {
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const data = {
                type: document.getElementById('testType').value,
                recipient: document.getElementById('testRecipient').value,
                template: document.getElementById('testTemplate').value,
                csrf_token: CSRF_TOKEN
            };

            fetch('../api/notification_debug.php?action=test', {
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

<?php require_once 'includes/admin_footer.php'; ?>

