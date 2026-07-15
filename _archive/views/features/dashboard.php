<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Features Dashboard') ?></h1>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-cubes fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1 text-muted">Total Features</h6>
                            <h3 class="mb-0"><?= (int)($stats['total'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1 text-muted">Active</h6>
                            <h3 class="mb-0"><?= (int)($stats['active'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1 text-muted">Pending</h6>
                            <h3 class="mb-0"><?= (int)($stats['pending'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-chart-line fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1 text-muted">Usage</h6>
                            <h3 class="mb-0"><?= (int)($stats['usage'] ?? 0) ?>%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">All Features</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Feature</th>
                            <th>Status</th>
                            <th>Version</th>
                            <th>Last Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($features)): ?>
                            <?php foreach ($features as $i => $f): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($f['name'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($f['status'] ?? 'inactive') === 'active' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars($f['status'] ?? 'inactive') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($f['version'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($f['last_used'] ?? 'Never') ?></td>
                                    <td>
                                        <a href="<?= $base ?? BASE_URL ?>/features/edit?id=<?= (int)($f['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No features found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>