<?php
/** Price revision scheduler. */
/** @var array $rows */
/** @var array $colonies */
$rows = $rows ?? [];
$colonies = $colonies ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-arrow-trend-up me-2"></i>Price Revisions</h4>
        <p class="text-muted mb-0">Schedule rate hikes — public countdown ticker drives 24h holds</p>
    </div>
</div>
<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <h6 class="fw-bold mb-3">Schedule Revision</h6>
    <form method="POST" action="<?= $base ?>/admin/colonies/price-revisions/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label small">Colony *</label>
                <select name="colony_id" class="form-select" required>
                    <?php foreach ($colonies as $c): ?><option value="<?= (int)$c['id'] ?>"><?= $e($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label small">Title *</label><input type="text" name="announcement_title" class="form-control" required maxlength="150" placeholder="e.g. Damar road done — rates up"></div>
            <div class="col-md-2"><label class="form-label small">Current ₹/sqft</label><input type="number" step="0.01" min="0" name="current_price" class="form-control" value="0"></div>
            <div class="col-md-2"><label class="form-label small">New ₹/sqft *</label><input type="number" step="0.01" min="0.01" name="new_price" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label small">Effective *</label><input type="date" name="effective_date" class="form-control" required></div>
            <div class="col-md-2 pt-4"><button type="submit" class="btn btn-primary w-100">Schedule</button></div>
        </div>
    </form>
</div></div>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Colony</th><th>Title</th><th class="text-end">Current</th><th class="text-end">New</th><th>Effective</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No revisions scheduled.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= $e($r['colony_name'] ?? '') ?></td>
                <td class="fw-medium"><?= $e($r['announcement_title']) ?></td>
                <td class="text-end">₹<?= number_format((float)$r['current_price']) ?></td>
                <td class="text-end fw-bold">₹<?= number_format((float)$r['new_price']) ?></td>
                <td><?= $e($r['effective_date']) ?></td>
                <td><span class="badge bg-<?= !empty($r['is_active']) ? 'success' : 'secondary' ?>"><?= !empty($r['is_active']) ? 'Active' : 'Off' ?></span></td>
                <td class="text-end">
                    <?php if (!empty($r['is_active'])): ?>
                    <form method="POST" action="<?= $base ?>/admin/colonies/price-revisions/deactivate/<?= (int)$r['id'] ?>" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Off</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
</div></div>
