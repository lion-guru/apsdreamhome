<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Log Viewer') ?></h1>
        <div>
            <button class="btn btn-outline-secondary" onclick="location.reload()"><i class="fas fa-sync me-1"></i> Refresh</button>
            <button class="btn btn-outline-danger" onclick="clearLogs()"><i class="fas fa-trash me-1"></i> Clear</button>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select id="levelFilter" class="form-select form-select-sm" onchange="applyFilters()">
                        <option value="">All Levels</option>
                        <option value="error">Error</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>
                <div class="col">
                    <input type="text" id="searchFilter" class="form-control form-control-sm" placeholder="Search logs..." onkeyup="applyFilters()">
                </div>
                <div class="col-auto">
                    <select id="dateFilter" class="form-select form-select-sm" onchange="applyFilters()">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0 table-responsive" id="logTable">
                    <thead class="table-light">
                        <tr><th>Level</th><th>Message</th><th>File</th><th>Line</th><th>IP</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($log_entries)): ?>
                            <?php foreach ($log_entries as $e): ?>
                                <tr class="log-row" data-level="<?= htmlspecialchars($e['level'] ?? 'info') ?>" data-message="<?= htmlspecialchars($e['message'] ?? '') ?>">
                                    <td><span class="badge bg-<?= ($e['level'] ?? 'info') === 'error' ? 'danger' : (($e['level'] ?? 'info') === 'warning' ? 'warning' : (($e['level'] ?? 'info') === 'debug' ? 'secondary' : 'info')) ?>"><?= htmlspecialchars($e['level'] ?? 'info') ?></span></td>
                                    <td class="text-truncate style-89018"><?= htmlspecialchars($e['message'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(basename($e['file'] ?? '-')) ?></td>
                                    <td><?= (int)($e['line'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($e['ip'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($e['created_at'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No log entries found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const level = document.getElementById('levelFilter').value.toLowerCase();
    const search = document.getElementById('searchFilter').value.toLowerCase();
    document.querySelectorAll('.log-row').forEach(row => {
        const matchesLevel = !level || row.dataset.level === level;
        const matchesSearch = !search || row.dataset.message.toLowerCase().includes(search);
        row.style.display = matchesLevel && matchesSearch ? '' : 'none';
    });
}
function clearLogs() {
    if (!confirm('Clear all logs?')) return;
    fetch('<?= $base ?? BASE_URL ?>/logging/clear', { method: 'POST' })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>