<?php
$pageTitle = $pageTitle ?? 'Redemptions';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$redemptions = $redemptions ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-ticket-alt me-2 text-info"></i>Reward Redemptions</h1>
        <a href="<?= $base ?>/admin/loyalty" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">All Redemptions</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($redemptions)): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-ticket-alt fa-2x d-block mb-2"></i>No redemptions recorded yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Reward</th>
                                <th>Points Spent</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($redemptions as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['member_name'] ?? $r['name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($r['reward_name'] ?? $r['reward'] ?? '') ?></td>
                                <td><span class="badge bg-warning text-dark"><?= number_format(intval($r['points_spent'] ?? $r['points'] ?? 0)) ?> pts</span></td>
                                <td><?= htmlspecialchars($r['created_at'] ?? $r['date'] ?? '') ?></td>
                                <td>
                                    <?php $s = $r['status'] ?? 'pending'; ?>
                                    <span class="badge bg-<?= $s === 'completed' ? 'success' : ($s === 'pending' ? 'warning' : ($s === 'cancelled' ? 'danger' : 'info')) ?>"><?= ucfirst($s) ?></span>
                                </td>
                                <td>
                                    <?php if (($r['status'] ?? '') === 'pending'): ?>
                                    <form method="POST" action="<?= $base ?>/admin/loyalty/redemptions/<?= $r['id'] ?? 0 ?>/approve" class="style-71727">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-success" aria-label="Confirm"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form method="POST" action="<?= $base ?>/admin/loyalty/redemptions/<?= $r['id'] ?? 0 ?>/reject" class="style-71727" data-aps-confirm="Reject this redemption?">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" aria-label="Close"><i class="fas fa-times"></i></button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
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
