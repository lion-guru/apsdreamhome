<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Log Reports') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/logging/reports/generate" class="btn btn-primary"><i class="fas fa-file-pdf me-1"></i> Generate Report</a>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                    <h5>Daily Summary</h5>
                    <p class="text-muted small">Get a daily summary of all log activity</p>
                    <a href="<?= $base ?? BASE_URL ?>/logging/reports/daily" class="btn btn-outline-primary btn-sm">View</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Error Analysis</h5>
                    <p class="text-muted small">Analyze error patterns and frequency</p>
                    <a href="<?= $base ?? BASE_URL ?>/logging/reports/errors" class="btn btn-outline-warning btn-sm">View</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                    <h5>Trend Report</h5>
                    <p class="text-muted small">View log volume trends over time</p>
                    <a href="<?= $base ?? BASE_URL ?>/logging/reports/trends" class="btn btn-outline-success btn-sm">View</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom"><h5 class="mb-0">Generated Reports</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Report</th><th>Type</th><th>Period</th><th>Generated</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reports)): ?>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($r['type'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars(($r['period_start'] ?? '') . ' to ' . ($r['period_end'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($r['generated_at'] ?? '') ?></td>
                                    <td>
                                        <a href="<?= $base ?? BASE_URL ?>/logging/reports/download?id=<?= (int)($r['id'] ?? 0) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteReport(<?= (int)($r['id'] ?? 0) ?>)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No reports generated yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteReport(id) {
    if (!confirm('Delete this report?')) return;
    fetch('<?= $base ?? BASE_URL ?>/logging/reports/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>