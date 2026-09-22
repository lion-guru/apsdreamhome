<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-clock me-2 text-primary"></i>Cron Health Monitor</h1>
            <p class="text-muted mb-0">Monitor master cron runner status, execution history, and task health</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <form method="POST" action="<?= BASE_URL ?>/admin/cron-health/run" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <select name="mode" class="form-select form-select-sm d-inline-block w-auto me-2">
                    <option value="daily">Daily</option>
                    <option value="monthly">Monthly</option>
                    <option value="all">All</option>
                </select>
                <label class="form-check form-check-inline me-2">
                    <input type="checkbox" name="dry_run" class="form-check-input" value="1">
                    <span class="form-check-label small">Dry Run</span>
                </label>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-play me-1"></i>Run Cron</button>
            </form>
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshStatus()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <div class="fs-2 <?= $cron_status['daily_task'] ? 'text-success' : 'text-warning' ?>">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="fw-semibold mt-1">Daily Cron</div>
                    <div class="small text-muted"><?= $cron_status['last_daily_run'] ? 'Last: ' . $cron_status['last_daily_run'] : 'Never run' ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <div class="fs-2 <?= $cron_status['monthly_task'] ? 'text-success' : 'text-warning' ?>">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="fw-semibold mt-1">Monthly Cron</div>
                    <div class="small text-muted"><?= $cron_status['last_monthly_run'] ? 'Last: ' . $cron_status['last_monthly_run'] : 'Never run' ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <div class="fs-2 <?= $cron_status['log_file_exists'] ? 'text-success' : 'text-danger' ?>">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="fw-semibold mt-1">Log File</div>
                    <div class="small text-muted"><?= $cron_status['log_file_exists'] ? 'OK' : 'Missing' ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <div class="fs-2 <?= $cron_status['log_file_writable'] ? 'text-success' : 'text-danger' ?>">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="fw-semibold mt-1">Log Writable</div>
                    <div class="small text-muted"><?= $cron_status['log_file_writable'] ? 'Yes' : 'No' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Run Cron Modal (inline form above) -->

    <!-- Execution History -->
    <div class="card aps-cp-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Execution History</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="refreshStatus()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
        </div>
        <div class="card-body p-0">
            <?php if (empty($entries)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-history fa-3x mb-3"></i>
                    <p>No cron execution history found</p>
                    <small>Run a cron job to see history here</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Mode</th>
                                <th>Tasks Run</th>
                                <th>Errors</th>
                                <th>Duration</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $entry): 
                                $data = $entry['data'] ?? [];
                                $results = $data['results'] ?? [];
                                $errors = $data['errors'] ?? 0;
                            ?>
                            <tr>
                                <td class="text-nowrap"><?= htmlspecialchars($entry['timestamp']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $entry['mode'] === 'DAILY' ? 'primary' : ($entry['mode'] === 'MONTHLY' ? 'warning' : 'info') ?>">
                                        <?= htmlspecialchars($entry['mode']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($data['tasks'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= $errors > 0 ? 'danger' : 'success' ?>">
                                        <?= $errors > 0 ? $errors . ' error(s)' : 'OK' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(($data['elapsed'] ?? '-') . 's') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="showDetails(<?= json_encode($data) ?>)">
                                        <i class="fas fa-eye me-1"></i>Details
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-list me-2"></i>Cron Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent"></div>
        </div>
    </div>

<script>
function refreshStatus() {
    fetch('<?= BASE_URL ?>/admin/cron-health/status')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Simple reload for now
            }
        })
        .catch(console.error);
}

function showDetails(data) {
    const container = document.getElementById('detailsContent');
    if (!data) {
        container.innerHTML = '<p class="text-muted">No details available</p>';
    } else {
        let html = '';
        if (data.results) {
            html += '<h6 class="mb-3">Task Results</h6>';
            html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
            html += '<thead><tr><th>Task</th><th>Status</th><th>Details</th></tr></thead><tbody>';
            foreach (data.results as $key => $val) {
                if (is_array($val)) {
                    $status = isset($val['success']) && $val['success'] ? '<span class="badge bg-success">OK</span>' : '<span class="badge bg-danger">FAIL</span>';
                    $details = json_encode($val);
                } else {
                    $status = '<span class="badge bg-secondary">INFO</span>';
                    $details = htmlspecialchars((string)$val);
                }
                html += "<tr><td>{$key}</td><td>{$status}</td><td>{$details}</td></tr>";
            }
            html += '</tbody></table></div>';
        }
        if (data.errors) {
            html += '<h6 class="mt-3 mb-2 text-danger">Errors</h6><ul class="text-danger">';
            foreach (data.errors as $err) {
                html += "<li>" . htmlspecialchars($err) . "</li>";
            }
            html += '</ul>';
        }
        container.innerHTML = html || '<p class="text-muted">No details available</p>';
    }
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

// Auto-refresh every 60 seconds
setInterval(refreshStatus, 60000);
</script>