<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Import / Export') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/admin/import-export/import" class="btn btn-primary btn-sm"><i class="fas fa-upload me-1"></i>Import</a>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-download fa-2x text-success"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Exports Today</h6><h3 class="mb-0"><?= (int)($stats['exports_today'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-upload fa-2x text-info"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Imports Today</h6><h3 class="mb-0"><?= (int)($stats['imports_today'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-clock fa-2x text-warning"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Pending</h6><h3 class="mb-0"><?= (int)($stats['pending'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-danger bg-opacity-10 p-3"><i class="fas fa-times-circle fa-2x text-danger"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Failed</h6><h3 class="mb-0"><?= (int)($stats['failed'] ?? 0) ?></h3></div></div></div></div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Recent Import/Export Activity</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Type</th><th>File</th><th>Records</th><th>Status</th><th>By</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $a): ?>
                                <tr>
                                    <td class="text-nowrap"><?= htmlspecialchars($a['created_at'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= ($a['type'] ?? 'export') === 'import' ? 'info' : 'success' ?>"><?= htmlspecialchars($a['type'] ?? 'export') ?></span></td>
                                    <td><?= htmlspecialchars($a['filename'] ?? '-') ?></td>
                                    <td><?= (int)($a['records'] ?? 0) ?></td>
                                    <td><span class="badge bg-<?= ($a['status'] ?? 'completed') === 'completed' ? 'success' : (($a['status'] ?? '') === 'failed' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($a['status'] ?? 'completed') ?></span></td>
                                    <td><?= htmlspecialchars($a['user'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No activity yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
