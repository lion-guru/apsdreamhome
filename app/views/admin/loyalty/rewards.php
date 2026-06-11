<?php
$pageTitle = $pageTitle ?? 'Rewards';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
$rewards = $rewards ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-gift me-2 text-success"></i>Rewards</h1>
        <div>
            <a href="<?= $base ?>/admin/loyalty" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <a href="<?= $base ?>/admin/loyalty/rewards/create" class="btn btn-success"><i class="fas fa-plus me-1"></i>Add Reward</a>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Available Rewards</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($rewards)): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-gift fa-2x d-block mb-2"></i>No rewards defined yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Points Required</th>
                                <th>Stock</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rewards as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['name'] ?? '') ?></strong></td>
                                <td><span class="badge bg-warning text-dark"><?= number_format(intval($r['points_required'] ?? $r['points'] ?? 0)) ?> pts</span></td>
                                <td><?= number_format(intval($r['stock'] ?? $r['quantity'] ?? 0)) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($r['category'] ?? 'General') ?></span></td>
                                <td><?php $s = $r['status'] ?? 'active'; ?>
                                    <span class="badge bg-<?= $s === 'active' ? 'success' : ($s === 'inactive' ? 'secondary' : 'danger') ?>"><?= ucfirst($s) ?></span>
                                </td>
                                <td>
                                    <a href="<?= $base ?>/admin/loyalty/rewards/<?= $r['id'] ?? 0 ?>/edit" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="<?= $base ?>/admin/loyalty/rewards/<?= $r['id'] ?? 0 ?>/delete" style="display:inline" onsubmit="return confirm('Delete this reward?')">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
