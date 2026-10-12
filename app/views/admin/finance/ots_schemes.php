<?php
/** OTS / festival waiver schemes. */
/** @var array $rows */
$rows = $rows ?? [];
$active = $active ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-badge-percent me-2"></i>OTS Schemes</h4>
        <p class="text-muted mb-0">One-time settlement &amp; festival penalty waivers<?= $active ? ' — <span class="badge bg-success">LIVE: ' . $e($active['scheme_name']) . ' (' . $e($active['waiver_percentage']) . '% till ' . $e($active['end_date']) . ')</span>' : '' ?></p>
    </div>
</div>
<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <h6 class="fw-bold mb-3">Launch New Scheme</h6>
    <form method="POST" action="<?= $base ?>/admin/finance/ots-schemes/create">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label small">Scheme name *</label><input type="text" name="scheme_name" class="form-control" required maxlength="150" placeholder="e.g. Diwali Fast-Track"></div>
            <div class="col-md-2"><label class="form-label small">Start *</label><input type="date" name="start_date" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label small">End *</label><input type="date" name="end_date" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label small">Waiver %</label><input type="number" step="0.01" min="0" max="100" name="waiver_percentage" class="form-control" value="100"></div>
            <div class="col-md-1"><label class="form-label small">Min OD mo.</label><input type="number" min="0" name="min_overdue_months" class="form-control" value="2"></div>
            <div class="col-md-2"><label class="form-label small">&nbsp;</label><button type="submit" class="btn btn-primary w-100">Launch</button></div>
            <div class="col-12"><label class="form-label small">Terms &amp; conditions</label><textarea name="terms_conditions" class="form-control" rows="2" maxlength="2000"></textarea></div>
        </div>
    </form>
</div></div>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Scheme</th><th>Period</th><th class="text-end">Waiver</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No schemes yet — launch the first above.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td class="fw-medium"><?= $e($r['scheme_name']) ?></td>
                <td class="small"><?= $e($r['start_date']) ?> → <?= $e($r['end_date']) ?></td>
                <td class="text-end"><?= number_format((float)$r['waiver_percentage'], 2) ?>%</td>
                <td><span class="badge bg-<?= !empty($r['is_active']) ? 'success' : 'secondary' ?>"><?= !empty($r['is_active']) ? 'Active' : 'Off' ?></span></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
</div></div>
