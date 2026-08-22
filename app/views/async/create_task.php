<?php
$page_title = $page_title ?? 'Create Task - APS Dream Home';
$page_heading = $page_heading ?? 'Create Task';
$priorities = $priorities ?? [
    1 => 'Low',
    2 => 'Normal',
    3 => 'High',
    4 => 'Critical'
];
$task_types = $task_types ?? ['email', 'image_processing', 'report_generation', 'data_export', 'backup'];
$old_input = $old_input ?? [];
$errors = $errors ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-plus-circle me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h2>
        <a href="<?= BASE_URL ?>/async/tasks" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Tasks</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error ?? '') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Task Configuration</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/async/task/create" id="taskForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Task Name <span class="text-danger">*</span></label>
                        <input type="text" name="task_name" class="form-control" value="<?= htmlspecialchars($old_input['task_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Task Type <span class="text-danger">*</span></label>
                        <select name="task_type" class="form-select" id="taskType" required onchange="showTypeFields()">
                            <option value="">Select Type</option>
                            <?php foreach ($task_types as $type): ?>
                                <option value="<?= $type ?>" <?= ($old_input['task_type'] ?? '') === $type ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $type)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <?php foreach ($priorities as $val => $label): ?>
                                <option value="<?= e($val ?? '') ?>" <?= ($old_input['priority'] ?? 2) == $val ? 'selected' : '' ?>><?= e($label ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Retries</label>
                        <input type="number" name="max_retries" class="form-control" value="<?= (int)($old_input['max_retries'] ?? 3) ?>" min="0" max="10">
                    </div>
                </div>

                <hr class="my-4">

                <div id="typeFields">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-hand-pointer fa-2x mb-3"></i>
                        <p>Select a task type above to configure parameters</p>
                    </div>
                </div>

                <div id="emailFields" class="d-none">
                    <h5 class="mb-3"><i class="fas fa-envelope me-2"></i>Email Parameters</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">To Email <span class="text-danger">*</span></label>
                            <input type="email" name="email_to" class="form-control" value="<?= htmlspecialchars($old_input['email_to'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="email_subject" class="form-control" value="<?= htmlspecialchars($old_input['email_subject'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="email_message" class="form-control" rows="5"><?= htmlspecialchars($old_input['email_message'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Template</label>
                            <input type="text" name="email_template" class="form-control" value="<?= htmlspecialchars($old_input['email_template'] ?? 'default') ?>">
                        </div>
                    </div>
                </div>

                <div id="imageFields" class="d-none">
                    <h5 class="mb-3"><i class="fas fa-image me-2"></i>Image Processing Parameters</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Image Path <span class="text-danger">*</span></label>
                            <input type="text" name="image_path" class="form-control" value="<?= htmlspecialchars($old_input['image_path'] ?? '') ?>" placeholder="/path/to/image.jpg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Output Format</label>
                            <select name="output_format" class="form-select">
                                <option value="jpg" <?= ($old_input['output_format'] ?? 'jpg') === 'jpg' ? 'selected' : '' ?>>JPG</option>
                                <option value="png" <?= ($old_input['output_format'] ?? '') === 'png' ? 'selected' : '' ?>>PNG</option>
                                <option value="webp" <?= ($old_input['output_format'] ?? '') === 'webp' ? 'selected' : '' ?>>WebP</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Operations (comma separated)</label>
                            <input type="text" name="image_operations" class="form-control" value="<?= htmlspecialchars($old_input['image_operations'] ?? 'resize,optimize') ?>" placeholder="resize,optimize,watermark,thumbnail">
                            <div class="form-text">Available: resize, optimize, watermark, thumbnail, crop, rotate</div>
                        </div>
                    </div>
                </div>

                <div id="reportFields" class="d-none">
                    <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i>Report Generation Parameters</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Report Type <span class="text-danger">*</span></label>
                            <select name="report_type" class="form-select" required>
                                <option value="">Select Report Type</option>
                                <option value="sales" <?= ($old_input['report_type'] ?? '') === 'sales' ? 'selected' : '' ?>>Sales Report</option>
                                <option value="commission" <?= ($old_input['report_type'] ?? '') === 'commission' ? 'selected' : '' ?>>Commission Report</option>
                                <option value="leads" <?= ($old_input['report_type'] ?? '') === 'leads' ? 'selected' : '' ?>>Leads Report</option>
                                <option value="inventory" <?= ($old_input['report_type'] ?? '') === 'inventory' ? 'selected' : '' ?>>Inventory Report</option>
                                <option value="financial" <?= ($old_input['report_type'] ?? '') === 'financial' ? 'selected' : '' ?>>Financial Report</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Format</label>
                            <select name="report_format" class="form-select">
                                <option value="pdf" <?= ($old_input['report_format'] ?? 'pdf') === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                <option value="excel" <?= ($old_input['report_format'] ?? '') === 'excel' ? 'selected' : '' ?>>Excel</option>
                                <option value="csv" <?= ($old_input['report_format'] ?? '') === 'csv' ? 'selected' : '' ?>>CSV</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date Start</label>
                            <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($old_input['date_start'] ?? date('Y-m-01')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date End</label>
                            <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($old_input['date_end'] ?? date('Y-m-d')) ?>">
                        </div>
                    </div>
                </div>

                <div id="dataExportFields" class="d-none">
                    <h5 class="mb-3"><i class="fas fa-database me-2"></i>Data Export Parameters</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Export Type <span class="text-danger">*</span></label>
                            <select name="export_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="full" <?= ($old_input['export_type'] ?? '') === 'full' ? 'selected' : '' ?>>Full Export</option>
                                <option value="incremental" <?= ($old_input['export_type'] ?? '') === 'incremental' ? 'selected' : '' ?>>Incremental</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Table <span class="text-danger">*</span></label>
                            <input type="text" name="export_table" class="form-control" value="<?= htmlspecialchars($old_input['export_table'] ?? '') ?>" placeholder="e.g., users, leads, properties">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Filters (JSON)</label>
                            <textarea name="export_filters" class="form-control" rows="4"><?= htmlspecialchars($old_input['export_filters'] ?? '{}') ?></textarea>
                            <div class="form-text">JSON format: {"status": "active", "created_after": "2024-01-01"}</div>
                        </div>
                    </div>
                </div>

                <div id="backupFields" class="d-none">
                    <h5 class="mb-3"><i class="fas fa-hdd me-2"></i>Backup Parameters</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Backup Type</label>
                            <select name="backup_type" class="form-select">
                                <option value="full" <?= ($old_input['backup_type'] ?? 'full') === 'full' ? 'selected' : '' ?>>Full Backup</option>
                                <option value="incremental" <?= ($old_input['backup_type'] ?? '') === 'incremental' ? 'selected' : '' ?>>Incremental</option>
                                <option value="schema" <?= ($old_input['backup_type'] ?? '') === 'schema' ? 'selected' : '' ?>>Schema Only</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Target</label>
                            <select name="backup_target" class="form-select">
                                <option value="local" <?= ($old_input['backup_target'] ?? 'local') === 'local' ? 'selected' : '' ?>>Local</option>
                                <option value="s3" <?= ($old_input['backup_target'] ?? '') === 's3' ? 'selected' : '' ?>>AWS S3</option>
                                <option value="ftp" <?= ($old_input['backup_target'] ?? '') === 'ftp' ? 'selected' : '' ?>>FTP</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="backup_compress" class="form-check-input" <?= ($old_input['backup_compress'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label">Compress Backup</label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/async/tasks" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showTypeFields() {
    const type = document.getElementById('taskType').value;
    
    // Hide all
    document.querySelectorAll('[id$="Fields"]').forEach(el => el.classList.add('d-none'));
    document.getElementById('typeFields').classList.add('d-none');
    
    // Show relevant
    const fieldMap = {
        'email': 'emailFields',
        'image_processing': 'imageFields',
        'report_generation': 'reportFields',
        'data_export': 'dataExportFields',
        'backup': 'backupFields'
    };
    
    if (fieldMap[type]) {
        document.getElementById(fieldMap[type]).classList.remove('d-none');
    }
}
</script>