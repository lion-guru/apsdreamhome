<?php
/** @var array $colonies */
/** @var int $selected_colony */
/** @var array $rows */
$colonies = $colonies ?? [];
$selected_colony = (int)$selected_colony;
$rows = $rows ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-3">
    <div class="aps-cp-card-header"><h5 class="m-0"><i class="fas fa-shield-alt me-2"></i>RERA Compliance — 70% Escrow & Quarterly Progress</h5></div>
    <div class="aps-cp-card-body">
        <form method="get" class="row g-2 mb-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Colony</label>
                <select name="colony_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($colonies as $c): ?>
                        <option value="<?= (int)($c['id'] ?? 0) ?>" <?= ((int)($c['id'] ?? 0) === $selected_colony) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)($c['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-filter me-1"></i>Apply</button>
            </div>
        </form>

        <?php if ($selected_colony <= 0): ?>
            <div class="text-muted">Select a colony to view RERA records.</div>
        <?php else: ?>
            <h6 class="mt-3">Existing Records</h6>
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Quarter</th>
                        <th class="text-end">Escrow Withdrawn</th>
                        <th class="text-end">Construction %</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">No records yet</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)($r['year'] ?? 0) ?></td>
                            <td><?= htmlspecialchars((string)($r['quarter'] ?? '')) ?></td>
                            <td class="text-end">&#8377;<?= number_format((float)($r['escrow_withdrawn'] ?? 0)) ?></td>
                            <td class="text-end"><?= number_format((float)($r['construction_progress'] ?? 0), 2) ?>%</td>
                            <td><?= htmlspecialchars((string)($r['submitted_at'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <h6 class="mt-4">Add / Update Quarterly Filing</h6>
            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/sales/rera/store" class="row g-2 align-items-end">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
                <input type="hidden" name="colony_id" value="<?= $selected_colony ?>">
                <div class="col-md-2">
                    <label class="form-label small">Year</label>
                    <input type="number" name="year" value="<?= (int)date('Y') ?>" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Quarter</label>
                    <select name="quarter" class="form-select form-select-sm" required>
                        <?php foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $q): ?>
                            <option value="<?= $q ?>"><?= $q ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Construction %</label>
                    <input type="number" step="0.01" name="progress" min="0" max="100" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Escrow Withdrawn (&#8377;)</label>
                    <input type="number" step="0.01" name="withdrawn" value="0" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
