<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Email Queue') ?></h1>
        <div>
            <button class="btn btn-outline-primary btn-sm" onclick="processQueue()"><i class="fas fa-play me-1"></i>Process Now</button>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="fas fa-envelope fa-2x text-primary"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Total Queued</h6><h3 class="mb-0"><?= (int)($stats['queued'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-check fa-2x text-success"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Sent</h6><h3 class="mb-0"><?= (int)($stats['sent'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-clock fa-2x text-warning"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Pending</h6><h3 class="mb-0"><?= (int)($stats['pending'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-danger bg-opacity-10 p-3"><i class="fas fa-exclamation-triangle fa-2x text-danger"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Failed</h6><h3 class="mb-0"><?= (int)($stats['failed'] ?? 0) ?></h3></div></div></div></div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col"><h5 class="mb-0">Queue Items</h5></div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" onchange="filterQueue(this.value)">
                        <option value="">All</option>
                        <option value="queued">Queued</option>
                        <option value="sending">Sending</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>To</th><th>Subject</th><th>Status</th><th>Attempts</th><th>Queued At</th><th>Sent At</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($emails)): ?>
                            <?php foreach ($emails as $e): ?>
                                <tr class="email-row" data-status="<?= htmlspecialchars($e['status'] ?? '') ?>">
                                    <td><?= htmlspecialchars($e['recipient'] ?? '') ?></td>
                                    <td class="text-truncate" style="max-width:250px"><?= htmlspecialchars($e['subject'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($e['status'] ?? 'queued') === 'sent' ? 'success' : (($e['status'] ?? '') === 'failed' ? 'danger' : (($e['status'] ?? '') === 'sending' ? 'info' : 'secondary')) ?>">
                                            <?= htmlspecialchars($e['status'] ?? 'queued') ?>
                                        </span>
                                    </td>
                                    <td><?= (int)($e['attempts'] ?? 0) ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($e['created_at'] ?? '') ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($e['sent_at'] ?? '-') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelEmail(<?= (int)($e['id'] ?? 0) ?>)"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No emails in queue.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
function processQueue() {
    fetch('<?= $base ?? BASE_URL ?>/admin/emails/process', { method: 'POST' })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.error || 'Failed'); });
}
function cancelEmail(id) {
    if (!confirm('Cancel this email?')) return;
    fetch('<?= $base ?? BASE_URL ?>/admin/emails/cancel', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id}) })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function filterQueue(status) {
    document.querySelectorAll('.email-row').forEach(row => {
        row.style.display = !status || row.dataset.status === status ? '' : 'none';
    });
}
</script>
