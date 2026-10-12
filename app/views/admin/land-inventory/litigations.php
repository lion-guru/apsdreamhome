<?php
/** Land litigation & stay radar. */
/** @var array $rows */
$rows = $rows ?? [];
$status_filter = $status_filter ?? '';
$q = $q ?? '';
$stay_count = (int)($stay_count ?? 0);
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$stBadge = ['pending' => 'warning text-dark', 'decided_in_favor' => 'success', 'decided_against' => 'danger', 'settled' => 'info text-dark'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-gavel me-2"></i>Land Litigation Radar (मुकदमा ट्रैकर)</h4>
        <p class="text-muted mb-0"><?= count($rows) ?> case(s)<?= $stay_count > 0 ? ' · <span class="badge bg-danger">' . $stay_count . ' ACTIVE STAY(S)</span>' : '' ?></p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Khasra / village / case no." value="<?= $e($q) ?>">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (['pending', 'decided_in_favor', 'decided_against', 'settled'] as $s): ?>
                <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm btn-outline-primary">Search</button>
    </form>
</div>
<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <h6 class="fw-bold mb-3">Track New Case</h6>
    <form method="POST" action="<?= $base ?>/admin/land-inventory/litigations/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="row g-2">
            <div class="col-md-2"><label class="form-label small">Khasra no. *</label><input type="text" name="khasra_no" class="form-control form-control-sm" required maxlength="100"></div>
            <div class="col-md-2"><label class="form-label small">Village *</label><input type="text" name="village" class="form-control form-control-sm" required maxlength="100"></div>
            <div class="col-md-2"><label class="form-label small">Court *</label><input type="text" name="court_name" class="form-control form-control-sm" required maxlength="150" placeholder="SDM Sadar"></div>
            <div class="col-md-2"><label class="form-label small">Case no. *</label><input type="text" name="case_number" class="form-control form-control-sm" required maxlength="100"></div>
            <div class="col-md-2"><label class="form-label small">Petitioner *</label><input type="text" name="petitioner_name" class="form-control form-control-sm" required maxlength="150"></div>
            <div class="col-md-2"><label class="form-label small">Respondent *</label><input type="text" name="respondent_name" class="form-control form-control-sm" required maxlength="150"></div>
            <div class="col-md-2"><label class="form-label small">Type</label><input type="text" name="case_type" class="form-control form-control-sm" maxlength="100" placeholder="Title / Partition"></div>
            <div class="col-md-2"><label class="form-label small">Next hearing</label><input type="date" name="next_hearing_date" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Advocate</label><input type="text" name="advocate_name" class="form-control form-control-sm" maxlength="100"></div>
            <div class="col-md-2 pt-4"><div class="form-check"><input type="checkbox" name="stay_order_active" value="1" class="form-check-input" id="stayBox"><label class="form-check-label small text-danger fw-bold" for="stayBox">STAY active</label></div></div>
            <div class="col-md-2 pt-4"><button type="submit" class="btn btn-sm btn-primary w-100">Track Case</button></div>
        </div>
    </form>
</div></div>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Khasra / Village</th><th>Court / Case</th><th>Parties</th><th>Stay</th><th>Next Hearing</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No cases tracked — clean title landscape. Add one above when a dispute surfaces.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr<?= !empty($r['stay_order_active']) && ($r['status'] ?? '') === 'pending' ? ' class="table-danger"' : '' ?>>
                <td><strong><?= $e($r['khasra_no']) ?></strong><br><small class="text-muted"><?= $e($r['village']) ?></small></td>
                <td><?= $e($r['court_name']) ?><br><small class="text-muted"><?= $e($r['case_number']) ?> · <?= $e($r['case_type'] ?? '—') ?></small></td>
                <td class="small"><?= $e($r['petitioner_name']) ?> <span class="text-muted">vs</span> <?= $e($r['respondent_name']) ?><br><small class="text-muted"><?= $e($r['advocate_name'] ?? '') ?></small></td>
                <td><?= !empty($r['stay_order_active']) ? '<span class="badge bg-danger">STAY</span>' : '<span class="text-muted">—</span>' ?></td>
                <td class="small"><?= $e($r['next_hearing_date'] ?? '—') ?></td>
                <td><span class="badge bg-<?= $stBadge[$r['status']] ?? 'secondary' ?>"><?= $e(ucwords(str_replace('_', ' ', $r['status'] ?? ''))) ?></span></td>
                <td class="text-end text-nowrap">
                    <form method="POST" action="<?= $base ?>/admin/land-inventory/litigations/update/<?= (int)$r['id'] ?>" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="stay_order_active" value="<?= !empty($r['stay_order_active']) ? '1' : '' ?>">
                        <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                            <?php foreach (['pending', 'decided_in_favor', 'decided_against', 'settled'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($r['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <form method="POST" action="<?= $base ?>/admin/land-inventory/litigations/delete/<?= (int)$r['id'] ?>" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" data-aps-confirm="Remove this case?"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
</div></div>
