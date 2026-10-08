<?php
$page_title = $page_title ?? 'Salary Grants';
$grants = $grants ?? [];
$tiers = $tiers ?? [];
$users = $users ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i><?= htmlspecialchars($page_title) ?></h4>
    <div class="d-flex gap-2">
    <form method="POST" action="<?= BASE_URL ?>/admin/salary/salary-grants/process" class="d-inline">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="month" value="<?= date('Y-m') ?>">
        <button class="btn btn-success" onclick="return confirm('Run monthly payout for <?= date('Y-m') ?>? Already-paid grants are skipped.')"><i class="fas fa-play me-1"></i>Process <?= date('M Y') ?> Payout</button>
    </form>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#grantModal"><i class="fas fa-plus me-1"></i>Activate Grant</button>
    </div>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">Salary Tiers (lifetime business volume → monthly grant)</div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th class="text-end">Volume ≥</th><th class="text-center">Window</th><th class="text-end">Monthly</th><th class="text-center">Months</th></tr></thead>
        <tbody>
            <?php foreach ($tiers as $t): ?><tr><td class="text-end">₹<?= number_format($t['volume_threshold'] ?? 0, 0) ?></td><td class="text-center"><?= (int)($t['window_days'] ?? 0) ?> days</td><td class="text-end">₹<?= number_format($t['monthly_grant'] ?? 0, 0) ?></td><td class="text-center"><?= (int)($t['months'] ?? 0) ?></td></tr><?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">Active &amp; Past Grants</div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Associate</th><th class="text-end">Volume</th><th class="text-end">Monthly</th><th class="text-center">Paid</th><th>Status</th><th>Activated</th></tr></thead>
        <tbody>
            <?php if (empty($grants)): ?><tr><td colspan="6" class="text-center text-muted py-3">No grants yet</td></tr><?php endif; ?>
            <?php foreach ($grants as $g): ?>
            <tr>
                <td class="fw-medium"><?= htmlspecialchars($g['associate_name'] ?? ('#' . $g['user_id'])) ?></td>
                <td class="text-end">₹<?= number_format($g['volume_threshold'] ?? 0, 0) ?></td>
                <td class="text-end">₹<?= number_format($g['monthly_amount'] ?? 0, 0) ?></td>
                <td class="text-center"><?= (int)($g['months_paid'] ?? 0) ?>/<?= (int)($g['months_total'] ?? 0) ?></td>
                <td><span class="badge bg-<?= ($g['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($g['status'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($g['activated_at'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<div class="modal fade" id="grantModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?= BASE_URL ?>/admin/salary/salary-grants/activate">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="modal-header"><h5 class="modal-title">Activate Salary Grant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Associate / Agent <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select</option>
                        <?php foreach ($users as $u): ?><option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name'] ?? '') ?></option><?php endforeach; ?>
                    </select></div>
                <p class="text-muted small mb-0">Eligibility is checked automatically (lifetime volume vs tiers, one active grant per tier).</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Activate</button>
            </div>
        </form>
    </div></div>
</div>
