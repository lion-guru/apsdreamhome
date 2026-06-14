<?php
$plans = $plans ?? [];
$activePlan = $activePlan ?? null;
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$statusBadge = function ($s) {
    $map = ['active' => 'bg-success', 'draft' => 'bg-warning text-dark', 'inactive' => 'bg-secondary'];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-file-invoice-dollar me-2"></i>Commission Plans</h5>
        <div>
            <a href="<?= htmlspecialchars($base) ?>/admin/commission-plans/calculator" class="btn btn-outline-info btn-sm me-2"><i class="fas fa-calculator me-1"></i>Calculator</a>
            <a href="<?= htmlspecialchars($base) ?>/admin/commission-plans/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Plan</a>
        </div>
    </div>
    <div class="aps-cp-card-body">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($activePlan): ?>
            <div class="alert alert-info d-flex align-items-center mb-3">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Active Plan:</strong>&nbsp;<?= htmlspecialchars($activePlan['plan_name']) ?>
                <span class="badge bg-success ms-2"><?= htmlspecialchars($activePlan['plan_code']) ?></span>
            </div>
        <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No active commission plan. Activate one from the list below.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover m-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plan Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Levels</th>
                        <th>Total Commission %</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($plans)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No plans found. Create your first plan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($plans as $i => $plan): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($plan['plan_name']) ?></strong></td>
                                <td><code><?= htmlspecialchars($plan['plan_code']) ?></code></td>
                                <td><?= ucfirst(htmlspecialchars($plan['plan_type'])) ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= (int)($plan['level_count'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <?php $total = (float)($plan['total_commission_pct'] ?? 0); ?>
                                    <span class="<?= $total > 20 ? 'text-danger' : 'text-success' ?>">
                                        <?= number_format($total, 1) ?>%
                                    </span>
                                    <?php if ($total > 20): ?>
                                        <i class="fas fa-exclamation-triangle text-danger ms-1" title="Exceeds 20% cap"></i>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $statusBadge($plan['status']) ?>"><?= ucfirst(htmlspecialchars($plan['status'])) ?></span></td>
                                <td><?= date('d M Y', strtotime($plan['created_at'])) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= htmlspecialchars($base) ?>/admin/commission-plans/edit/<?= $plan['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php if ($plan['status'] !== 'active'): ?>
                                            <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/commission-plans/activate/<?= $plan['id'] ?>" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                <button class="btn btn-outline-success" title="Activate" onclick="return confirm('Activate this plan? Other active plans will be deactivated.')"><i class="fas fa-check"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/commission-plans/deactivate/<?= $plan['id'] ?>" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                <button class="btn btn-outline-warning" title="Deactivate"><i class="fas fa-pause"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($plan['status'] !== 'active'): ?>
                                            <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/commission-plans/delete/<?= $plan['id'] ?>" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                <button class="btn btn-outline-danger" title="Delete" onclick="return confirm('Permanently delete this plan?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
