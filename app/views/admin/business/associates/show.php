<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user text-info me-2"></i><?= htmlspecialchars($associate['name'] ?? 'Associate') ?></h4>
        <div>
            <a href="<?= BASE_URL ?>/admin/business/associates" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <a href="<?= BASE_URL ?>/admin/business/associates/edit/<?= $associate['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
        </div>
    </div>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success ?? '') ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div><?php endif; ?>
    <div class="row">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>Details</div>
                <div class="aps-cp-card-body">
                    <p><strong>Email:</strong> <?= htmlspecialchars($associate['email'] ?? '-') ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($associate['phone'] ?? '-') ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-<?= ($associate['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= $associate['status'] ?? 'unknown' ?></span></p>
                    <p><strong>Joined:</strong> <?= date('d M Y', strtotime($associate['created_at'] ?? 'now')) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-chart-bar me-2"></i>Performance Metrics</div>
                <div class="aps-cp-card-body">
                    <?php if (!empty($metrics)): ?>
                        <div class="row text-center">
                            <div class="col-4"><h5><?= $metrics['total_sales'] ?? 0 ?></h5><small class="text-muted">Total Sales</small></div>
                            <div class="col-4"><h5>₹<?= number_format($metrics['total_sales_amount'] ?? 0) ?></h5><small class="text-muted">Total Amount</small></div>
                            <div class="col-4"><h5>₹<?= number_format($metrics['total_commission'] ?? 0) ?></h5><small class="text-muted">Commission</small></div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No performance data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
