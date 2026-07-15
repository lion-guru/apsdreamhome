<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Virtual Tours') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/features/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-vr-cardboard fa-2x text-info"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Total Tours</h6><h3 class="mb-0"><?= (int)($stats['total'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-eye fa-2x text-success"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Total Views</h6><h3 class="mb-0"><?= (int)($stats['views'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="fas fa-building fa-2x text-primary"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Properties</h6><h3 class="mb-0"><?= (int)($stats['properties'] ?? 0) ?></h3></div></div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas-clock fa-2x text-warning"></i></div></div><div class="ms-3"><h6 class="mb-1 text-muted">Avg Duration</h6><h3 class="mb-0"><?= htmlspecialchars($stats['avg_duration'] ?? '0s') ?></h3></div></div></div></div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Virtual Tour Properties</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Property</th><th>Tour Type</th><th>Views</th><th>Duration</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tours)): ?>
                            <?php foreach ($tours as $t): ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['property_name'] ?? '') ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($t['tour_type'] ?? '360') ?></span></td>
                                    <td><?= (int)($t['views'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($t['avg_duration'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($t['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($t['status'] ?? 'active') ?></span></td>
                                    <td>
                                        <a href="<?= $base ?? BASE_URL ?>/properties/view?id=<?= (int)($t['property_id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No virtual tours found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
