<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Security Alerts') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/logging/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white"><div class="card-body text-center"><h6>Critical</h6><h2 class="mb-0"><?= (int)($stats['critical'] ?? 0) ?></h2></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white"><div class="card-body text-center"><h6>High</h6><h2 class="mb-0"><?= (int)($stats['high'] ?? 0) ?></h2></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white"><div class="card-body text-center"><h6>Medium</h6><h2 class="mb-0"><?= (int)($stats['medium'] ?? 0) ?></h2></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white"><div class="card-body text-center"><h6>Low</h6><h2 class="mb-0"><?= (int)($stats['low'] ?? 0) ?></h2></div></div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Alert History</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="markAllResolved()"><i class="fas fa-check-double me-1"></i>Mark All Resolved</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Severity</th><th>Alert</th><th>Source</th><th>Count</th><th>First Seen</th><th>Last Seen</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alerts)): ?>
                            <?php foreach ($alerts as $a): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?= ($a['severity'] ?? 'low') === 'critical' ? 'danger' : (($a['severity'] ?? 'low') === 'high' ? 'warning' : (($a['severity'] ?? 'low') === 'medium' ? 'info' : 'secondary')) ?>">
                                            <?= htmlspecialchars($a['severity'] ?? 'low') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($a['alert'] ?? '') ?></td>
                                    <td><code><?= htmlspecialchars($a['source'] ?? '-') ?></code></td>
                                    <td><?= (int)($a['count'] ?? 1) ?></td>
                                    <td class="text-nowrap small"><?= htmlspecialchars($a['first_seen'] ?? '') ?></td>
                                    <td class="text-nowrap small"><?= htmlspecialchars($a['last_seen'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($a['status'] ?? 'open') === 'resolved' ? 'success' : 'danger' ?>">
                                            <?= htmlspecialchars($a['status'] ?? 'open') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success" onclick="resolveAlert(<?= (int)($a['id'] ?? 0) ?>)"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewDetails(<?= (int)($a['id'] ?? 0) ?>)"><i class="fas fa-eye"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-shield-alt fa-2x d-block mb-2 text-success"></i>No security alerts. All clear!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
<script>
function resolveAlert(id) {
    fetch('<?= $base ?? BASE_URL ?>/logging/resolve-alert', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id}) })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function markAllResolved() {
    if (!confirm('Mark all alerts as resolved?')) return;
    fetch('<?= $base ?? BASE_URL ?>/logging/resolve-all-alerts', { method: 'POST' })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function viewDetails(id) {
    window.location.href = '<?= $base ?? BASE_URL ?>/logging/details?id=' + id;
}
</script>
