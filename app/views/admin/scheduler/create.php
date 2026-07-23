<?php
$pageTitle = $pageTitle ?? 'Create Task';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Create Scheduled Task</h1>
        <a href="<?= $base ?>/admin/scheduler" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Task Details</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/scheduler/create">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Task Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Send Daily Reports" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the task"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Task Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select type...</option>
                                <option value="email">Email Notification</option>
                                <option value="report">Report Generation</option>
                                <option value="cleanup">Database Cleanup</option>
                                <option value="sync">Data Sync</option>
                                <option value="backup">Backup</option>
                                <option value="custom">Custom Command</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Schedule Expression <span class="text-danger">*</span></label>
                            <input type="text" name="schedule" class="form-control" placeholder="e.g., 0 8 * * * (cron format)" required>
                            <small class="text-muted">Use cron format: minute hour day month weekday</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Command / Action</label>
                            <input type="text" name="command" class="form-control" placeholder="PHP class::method or shell command">
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" name="enabled" class="form-check-input" id="enabledToggle" value="1" checked>
                            <label class="form-check-label" for="enabledToggle">Enable task immediately</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Task</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-info">Schedule Examples</h6></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-unstyled small">
                        <li class="mb-2"><code>* * * * *</code> Every minute</li>
                        <li class="mb-2"><code>0 * * * *</code> Every hour</li>
                        <li class="mb-2"><code>0 8 * * *</code> Daily at 8 AM</li>
                        <li class="mb-2"><code>0 8 * * 1-5</code> Weekdays at 8 AM</li>
                        <li class="mb-2"><code>0 0 1 * *</code> First day of month</li>
                        <li class="mb-2"><code>*/15 * * * *</code> Every 15 minutes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
