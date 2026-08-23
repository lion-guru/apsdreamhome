<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'System Logs') ?></h1>
        <div>
            <button class="btn btn-outline-danger btn-sm" onclick="clearLogs()"><i class="fas fa-trash me-1"></i>Clear</button>
            <a href="<?= $base ?? BASE_URL ?>/logging/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select class="form-select form-select-sm" id="levelFilter" onchange="applyFilter()">
                        <option value="">All Levels</option>
                        <option value="error" <?= ($filter['level'] ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                        <option value="warning" <?= ($filter['level'] ?? '') === 'warning' ? 'selected' : '' ?>>Warning</option>
                        <option value="info" <?= ($filter['level'] ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
                        <option value="debug" <?= ($filter['level'] ?? '') === 'debug' ? 'selected' : '' ?>>Debug</option>
                    </select>
                </div>
                <div class="col-auto flex-grow-1">
                    <input type="text" class="form-control form-control-sm" id="searchText" placeholder="Search logs..." value="<?= htmlspecialchars($filter['search'] ?? '') ?>" onkeyup="applyFilter()">
                </div>
                <div class="col text-end">
                    <span class="text-muted small"><?= (int)($total ?? 0) ?> entries (showing <?= count($logs ?? []) ?>)</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive style-96643">
                <div class="table-responsive"><table class="table table-hover table-sm mb-0 table-responsive">
                    <thead class="table-light position-sticky top-0">
                        <tr><th>Level</th><th>Message</th><th>File</th><th>Line</th><th>IP</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><span class="badge bg-<?= ($log['level'] ?? 'info') === 'error' ? 'danger' : (($log['level'] ?? 'info') === 'warning' ? 'warning' : (($log['level'] ?? 'info') === 'debug' ? 'secondary' : 'info')) ?>"><?= htmlspecialchars($log['level'] ?? 'info') ?></span></td>
                                    <td class="text-truncate style-38938"><?= htmlspecialchars($log['message'] ?? '') ?></td>
                                    <td><code><?= htmlspecialchars(basename($log['file'] ?? '-')) ?></code></td>
                                    <td><?= (int)($log['line'] ?? 0) ?></td>
                                    <td><code><?= htmlspecialchars($log['ip'] ?? '-') ?></code></td>
                                    <td class="text-nowrap small"><?= htmlspecialchars($log['created_at'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No logs found matching your criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <?php if (($total ?? 0) > count($logs ?? [])): ?>
    <div class="text-center mt-3">
        <button class="btn btn-outline-primary btn-sm" onclick="loadMore()"><i class="fas fa-plus me-1"></i>Load More</button>
    </div>
    <?php endif; ?>
</div>
<script>
function applyFilter() {
    const level = document.getElementById('levelFilter').value;
    const search = document.getElementById('searchText').value;
    const params = new URLSearchParams();
    if (level) params.set('level', level);
    if (search) params.set('search', search);
    window.location.href = '<?= $base ?? BASE_URL ?>/logging/logs?' + params.toString();
}
function clearLogs() {
    if (!confirm('Clear all logs?')) return;
    fetch('<?= $base ?? BASE_URL ?>/logging/clear', { method: 'POST' }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function loadMore() {
    const params = new URLSearchParams(window.location.search);
    const offset = <?= count($logs ?? []) ?>;
    params.set('offset', offset);
    window.location.href = '<?= $base ?? BASE_URL ?>/logging/logs?' + params.toString();
}
</script>
