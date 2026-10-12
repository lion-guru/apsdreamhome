<?php
/** Daily site labor muster — mobile-friendly for site engineers. */
/** @var array $colonies */
/** @var array $logs */
$colonies = $colonies ?? [];
$logs = $logs ?? [];
$work_date = $work_date ?? date('Y-m-d');
$colony_id = (int)($colony_id ?? 0);
$day_total = $day_total ?? 0;
$day_headcount = $day_headcount ?? 0;
$roles = $roles ?? ['mason' => 'Mason', 'helper' => 'Helper'];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$roleBadge = ['mason' => 'warning text-dark', 'helper' => 'info text-dark', 'operator' => 'primary', 'supervisor' => 'success', 'other' => 'secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-clipboard-list me-2"></i>Daily Site Muster Roll</h4>
        <p class="text-muted mb-0">Haziri + daily wages — <strong><?= (int)$day_headcount ?> present</strong>, day total <strong class="text-success">₹<?= number_format((float)$day_total) ?></strong></p>
    </div>
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="date" name="work_date" class="form-control" value="<?= $e($work_date) ?>" onchange="this.form.submit()">
        <select name="colony_id" class="form-select" onchange="this.form.submit()">
            <option value="0">All colonies</option>
            <?php foreach ($colonies as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $colony_id === (int)$c['id'] ? 'selected' : '' ?>><?= $e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <h6 class="fw-bold mb-3"><i class="fas fa-plus me-1"></i>Add Attendance</h6>
    <form method="POST" action="<?= $base ?>/admin/construction/muster-roll/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="work_date" value="<?= $e($work_date) ?>">
        <div class="row g-2">
            <div class="col-md-3 col-6">
                <label class="form-label small">Colony</label>
                <select name="colony_id" class="form-control form-control-lg">
                    <option value="0">—</option>
                    <?php foreach ($colonies as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $colony_id === (int)$c['id'] ? 'selected' : '' ?>><?= $e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label small">Worker name *</label>
                <input type="text" name="worker_name" class="form-control form-control-lg" required maxlength="150" placeholder="e.g. Ramesh">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small">Role</label>
                <select name="worker_role" class="form-select form-select-lg" id="musterRole">
                    <?php foreach ($roles as $k => $label): ?>
                        <option value="<?= $e($k) ?>"><?= $e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small">Daily wage ₹ *</label>
                <input type="number" step="1" min="0" name="daily_wage" id="musterWage" class="form-control form-control-lg" required placeholder="500">
            </div>
            <div class="col-md-2 col-12">
                <label class="form-label small">Work done</label>
                <input type="text" name="work_done" class="form-control form-control-lg" maxlength="255" placeholder="e.g. boundary 40ft">
            </div>
        </div>
        <div class="d-flex gap-2 mt-3 align-items-center flex-wrap">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_present" value="1" checked id="musterPresent">
                <label class="form-check-label" for="musterPresent">Present</label>
            </div>
            <button type="submit" class="btn btn-primary btn-lg flex-grow-1"><i class="fas fa-save me-1"></i>Save Entry</button>
        </div>
    </form>
</div></div>

<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Worker</th><th>Role</th><th>Colony</th><th class="text-end">Wage</th><th>Present</th><th>Work done</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($logs)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No muster entries for <?= $e($work_date) ?> — add the first above.</td></tr>
        <?php else: foreach ($logs as $l): ?>
            <tr>
                <td class="fw-medium"><?= $e($l['worker_name']) ?></td>
                <td><span class="badge bg-<?= $roleBadge[$l['worker_role']] ?? 'secondary' ?>"><?= $e($roles[$l['worker_role']] ?? $l['worker_role']) ?></span></td>
                <td><?= $e($l['colony_name'] ?? '—') ?></td>
                <td class="text-end">₹<?= number_format((float)$l['daily_wage']) ?></td>
                <td><?= !empty($l['is_present']) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                <td class="small"><?= $e($l['work_done'] ?? '—') ?></td>
                <td class="text-end">
                    <form method="POST" action="<?= $base ?>/admin/construction/muster-roll/delete/<?= (int)$l['id'] ?>" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" data-aps-confirm="Delete this entry?"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
</div></div>
<script>
// Typical UP site rates as hints (editable — not enforced).
document.getElementById('musterRole')?.addEventListener('change', function () {
    var hints = { mason: 800, helper: 500, operator: 900, supervisor: 1000, other: '' };
    document.getElementById('musterWage').placeholder = hints[this.value] ?? '';
});
</script>
