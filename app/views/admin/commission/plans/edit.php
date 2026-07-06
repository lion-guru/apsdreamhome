<?php
$plan = $plan ?? null;
$levels = $levels ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

if (!$plan) {
    echo '<div class="alert alert-danger">Plan not found.</div>';
    return;
}

$totalCommission = 0;
foreach ($levels as $lv) {
    $totalCommission += (float)$lv['direct_commission'] + (float)$lv['team_commission'] + (float)$lv['level_bonus'] + (float)$lv['matching_bonus'] + (float)$lv['leadership_bonus'] + (float)$lv['performance_bonus'];
}
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-edit me-2"></i>Edit: <?= htmlspecialchars($plan['plan_name']) ?></h5>
        <a href="<?= htmlspecialchars($base) ?>/admin/commission-plans" class="btn btn-link btn-sm">Back to Plans</a>
    </div>
    <div class="aps-cp-card-body">
        <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/commission-plans/update/<?= $plan['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Plan Name</label>
                    <input type="text" name="plan_name" class="form-control" value="<?= htmlspecialchars($plan['plan_name']) ?>" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($plan['plan_code']) ?>" disabled>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Type</label>
                    <select name="plan_type" class="form-select">
                        <?php foreach (['hybrid','direct','team','binary','matrix'] as $t): ?>
                            <option value="<?= $t ?>" <?= $plan['plan_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" value="<?= ucfirst(htmlspecialchars($plan['status'])) ?>" disabled>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($plan['description'] ?? '') ?></textarea>
            </div>

            <h6 class="mb-3"><i class="fas fa-layer-group me-1"></i>Commission Levels</h6>

            <?php if ($totalCommission > 20): ?>
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Warning:</strong> Total commission across all levels is <strong><?= number_format($totalCommission, 1) ?>%</strong>, exceeding the 20% cap per payment.
                </div>
            <?php endif; ?>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm m-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Level</th>
                            <th>Direct %</th>
                            <th>Team %</th>
                            <th>Level Bonus %</th>
                            <th>Matching %</th>
                            <th>Leadership %</th>
                            <th>Performance %</th>
                            <th>Row Total</th>
                            <th>Monthly Target (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($levels)): ?>
                            <tr><td colspan="10" class="text-center text-muted">No levels configured</td></tr>
                        <?php else: ?>
                            <?php foreach ($levels as $lv): ?>
                                <?php
                                $rowTotal = (float)$lv['direct_commission'] + (float)$lv['team_commission'] + (float)$lv['level_bonus'] + (float)$lv['matching_bonus'] + (float)$lv['leadership_bonus'] + (float)$lv['performance_bonus'];
                                ?>
                                <tr>
                                    <td><?= (int)$lv['level_order'] ?></td>
                                    <td><strong><?= htmlspecialchars($lv['level_name']) ?></strong></td>
                                    <td><input type="number" name="levels[<?= $lv['id'] ?>][direct_commission]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="<?= htmlspecialchars((string)$lv['direct_commission']) ?>"></td>
                                    <td><input type="number" name="levels[<?= $lv['id'] ?>][team_commission]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="<?= htmlspecialchars((string)$lv['team_commission']) ?>"></td>
                                    <td><input type="number" name="levels[<?= $lv['id'] ?>][level_bonus]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="<?= htmlspecialchars((string)$lv['level_bonus']) ?>"></td>
                                    <td><input type="number" name="levels[<?= $lv['id'] ?>][matching_bonus]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="<?= htmlspecialchars((string)$lv['matching_bonus']) ?>"></td>
                                    <td><input type="number" name="levels[<?= $lv['id'] ?>][leadership_bonus]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="<?= htmlspecialchars((string)$lv['leadership_bonus']) ?>"></td>
                                    <td><input type="number" name="levels[<?= $lv['id'] ?>][performance_bonus]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="<?= htmlspecialchars((string)$lv['performance_bonus']) ?>"></td>
                                    <td class="<?= $rowTotal > 20 ? 'text-danger fw-bold' : 'text-muted' ?>"><?= number_format($rowTotal, 1) ?>%</td>
                                    <td><input type="number" name="levels[<?= $lv['id'] ?>][monthly_target]" class="form-control form-control-sm" step="1000" min="0" value="<?= htmlspecialchars((string)$lv['monthly_target']) ?>"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="8" class="text-end"><strong>Sum Total:</strong></td>
                            <td class="<?= $totalCommission > 20 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>"><?= number_format($totalCommission, 1) ?>%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                <a href="<?= htmlspecialchars($base) ?>/admin/commission-plans" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
