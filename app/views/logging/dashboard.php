<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Logs Dashboard') ?></h1>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0"><div class="rounded-circle bg-danger bg-opacity-10 p-3"><i class="fas fa-exclamation-triangle fa-2x text-danger"></i></div></div>
                        <div class="ms-3">
                            <h6 class="mb-1 text-muted">Errors</h6>
                            <h3 class="mb-0"><?= (int)($stats['errors'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0"><div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-exclamation-circle fa-2x text-warning"></i></div></div>
                        <div class="ms-3">
                            <h6 class="mb-1 text-muted">Warnings</h6>
                            <h3 class="mb-0"><?= (int)($stats['warnings'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0"><div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-info-circle fa-2x text-info"></i></div></div>
                        <div class="ms-3">
                            <h6 class="mb-1 text-muted">Info</h6>
                            <h3 class="mb-0"><?= (int)($stats['info'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0"><div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-check-circle fa-2x text-success"></i></div></div>
                        <div class="ms-3">
                            <h6 class="mb-1 text-muted">Total Today</h6>
                            <h3 class="mb-0"><?= (int)($stats['total_today'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Logs</h5>
            <a href="<?= $base ?? BASE_URL ?>/logging/viewer" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Level</th><th>Message</th><th>File</th><th>Line</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><span class="badge bg-<?= ($log['level'] ?? 'info') === 'error' ? 'danger' : (($log['level'] ?? 'info') === 'warning' ? 'warning' : 'info') ?>"><?= htmlspecialchars($log['level'] ?? 'info') ?></span></td>
                                    <td class="text-truncate" class="style-78037"><?= htmlspecialchars($log['message'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(basename($log['file'] ?? '-')) ?></td>
                                    <td><?= (int)($log['line'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($log['created_at'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>