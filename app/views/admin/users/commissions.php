<?php
$user = $user ?? [];
$commissions = $commissions ?? [];
$totals = $totals ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-2"></i><?= htmlspecialchars($user['name'] ?? '') ?></a>
        <h4 class="mt-2 mb-0"><i class="fas fa-coins me-2 text-info"></i>Commission History</h4>
    </div>
</div>

<!-- Commission Summary by Type -->
<?php if (!empty($totals)): ?>
<div class="row g-3 mb-4">
    <?php foreach ($totals as $t): ?>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted"><?= str_replace('_', ' ', ucfirst($t['commission_type'] ?? '')) ?></h6>
                <h4 class="text-success">₹<?= number_format((float)$t['total']) ?></h4>
                <small class="text-muted"><?= $t['cnt'] ?> entries</small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Commission Ledger -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>All Commissions (<?= count($commissions) ?> entries)</h6></div>
    <div class="card-body p-0">
        <?php if (empty($commissions)): ?>
        <div class="text-center py-4 text-muted">No commission records found</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Amount</th><th>Source (From)</th><th>Level</th><th>Status</th><th>Notes</th></tr></thead>
                <tbody>
                <?php foreach ($commissions as $c): ?>
                <tr>
                    <td><small><?= date('M d, Y H:i', strtotime($c['created_at'] ?? '')) ?></small></td>
                    <td><span class="badge bg-primary"><?= str_replace('_', ' ', ucfirst($c['commission_type'] ?? '')) ?></span></td>
                    <td class="fw-bold text-success">₹<?= number_format((float)($c['amount'] ?? 0)) ?></td>
                    <td>
                        <?php if (!empty($c['source_name'])): ?>
                            <a href="<?= $base ?>/admin/users/<?= $c['source_user_id'] ?? '' ?>"><?= htmlspecialchars($c['source_name']) ?></a>
                        <?php else: ?>
                            <?= $c['source_user_id'] ?? '-' ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $c['level'] ?? '-' ?></td>
                    <td><span class="badge bg-<?= ($c['status'] ?? '') === 'approved' ? 'success' : (($c['status'] ?? '') === 'pending' ? 'warning' : 'danger') ?>"><?= ucfirst($c['status'] ?? '') ?></span></td>
                    <td><small class="text-muted"><?= htmlspecialchars(substr($c['notes'] ?? '', 0, 50)) ?></small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
