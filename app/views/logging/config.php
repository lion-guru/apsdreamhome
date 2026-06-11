<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Logging Configuration') ?></h1>
    </div>
    <form method="post" action="<?= $base ?? BASE_URL ?>/logging/config/update">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom"><h5 class="mb-0">Log Levels</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Minimum Log Level</label>
                            <select name="config[min_level]" class="form-select">
                                <option value="debug" <?= ($config['min_level'] ?? '') === 'debug' ? 'selected' : '' ?>>Debug</option>
                                <option value="info" <?= ($config['min_level'] ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
                                <option value="warning" <?= ($config['min_level'] ?? '') === 'warning' ? 'selected' : '' ?>>Warning</option>
                                <option value="error" <?= ($config['min_level'] ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="config[log_debug]" value="1" <?= ($config['log_debug'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label">Log debug messages</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="config[log_info]" value="1" <?= ($config['log_info'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label">Log info messages</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="config[log_warning]" value="1" <?= ($config['log_warning'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label">Log warnings</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="config[log_error]" value="1" <?= ($config['log_error'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label">Log errors</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom"><h5 class="mb-0">Retention</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Retention Period (days)</label>
                            <input type="number" name="config[retention_days]" class="form-control" value="<?= (int)($config['retention_days'] ?? 30) ?>" min="1" max="365">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Log File Size (MB)</label>
                            <input type="number" name="config[max_file_size]" class="form-control" value="<?= (int)($config['max_file_size'] ?? 100) ?>" min="1">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="config[auto_archive]" value="1" <?= ($config['auto_archive'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label">Auto-archive logs after retention period</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Configuration</button>
            <a href="<?= $base ?? BASE_URL ?>/logging" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>