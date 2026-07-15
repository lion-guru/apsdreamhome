<?php
$plan = $plan ?? null;
$levels = $plan['levels'] ?? [];
$versions = $versions ?? [];
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
if (!$plan) { echo '<div class="alert alert-danger">Plan not found.</div>'; return; }
$totalCommission = 0;
foreach ($levels as $lv) {
    $totalCommission += (float)$lv['direct_commission'] + (float)$lv['team_commission'] + (float)$lv['level_bonus'] + (float)$lv['matching_bonus'] + (float)$lv['leadership_bonus'] + (float)$lv['performance_bonus'];
}
$isActive = $plan['status'] === 'active';
$canEdit = !$isActive;
$statusBadge = match($plan['status']) { 'active' => 'bg-success', 'draft' => 'bg-warning text-dark', default => 'bg-secondary' };
?>
<style>
.cp-card{background:#1a1f36;border:1px solid #2a2f4a;border-radius:12px;color:#e0e0e0;margin-bottom:1.5rem}
.cp-card-header{background:linear-gradient(135deg,#141829,#1e2340);padding:1rem 1.5rem;border-bottom:1px solid #2a2f4a;display:flex;justify-content:space-between;align-items:center}
.cp-card-body{padding:1.5rem}
.cp-input{background:#0f1225;border:1px solid #2a2f4a;border-radius:8px;color:#e0e0e0;padding:8px 12px;width:100%;font-size:.85rem}
.cp-input:focus{border-color:#4f8cff;outline:none;box-shadow:0 0 0 2px #4f8cff33}
.cp-input:disabled{opacity:.5;cursor:not-allowed}
.cp-label{color:#8892b0;font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;display:block}
.cp-btn{padding:8px 20px;border-radius:8px;font-size:.85rem;font-weight:500;border:none;cursor:pointer;transition:all .2s}
.cp-btn-primary{background:linear-gradient(135deg,#4f8cff,#6366f1);color:#fff}
.cp-btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 15px #4f8cff44}
.cp-btn-outline{background:transparent;border:1px solid #4f8cff44;color:#4f8cff;text-decoration:none;display:inline-block}
.cp-btn-success{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff}
.cp-btn-warning{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff}
.cp-version{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:600;background:#1e2340;color:#a855f7;border:1px solid #a855f733}
.rank-table th{background:#1e2340;color:#8892b0;font-size:.72rem;text-transform:uppercase;padding:8px 10px;border:none}
.rank-table td{padding:8px 10px;border-top:1px solid #1e2340}
.rank-table input{background:#0f1225;border:1px solid #2a2f4a;border-radius:6px;color:#e0e0e0;padding:6px 8px;width:100%;font-size:.82rem;text-align:right}
.rank-table input:focus{border-color:#4f8cff;outline:none}
.rank-table input:disabled{opacity:.5}
.version-chip{display:inline-block;padding:3px 10px;border-radius:6px;font-size:.72rem;margin:2px;background:#1e2340;color:#8892b0;border:1px solid #2a2f4a}
.version-chip.current{background:#a855f722;color:#a855f7;border-color:#a855f7}
</style>

<div class="cp-card">
    <div class="cp-card-header">
        <div style="display:flex;align-items:center;gap:12px">
            <h5 class="m-0" style="color:#e0e0e0"><i class="fas fa-edit me-2" style="color:#4f8cff"></i><?= htmlspecialchars($plan['plan_name']) ?></h5>
            <span class="cp-version">v<?= $plan['version'] ?></span>
            <span class="cp-badge <?= $statusBadge ?>" style="padding:3px 10px;border-radius:20px;font-size:.72rem"><?= ucfirst($plan['status']) ?></span>
            <?php if ($isActive): ?>
                <span style="color:#22c55e;font-size:.78rem"><i class="fas fa-lock me-1"></i>Active — editing locked</span>
            <?php endif; ?>
        </div>
        <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="cp-card-body">
        <?php if ($isActive): ?>
            <div style="background:#f59e0b15;border:1px solid #f59e0b33;border-radius:10px;padding:12px 16px;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px">
                <i class="fas fa-info-circle" style="color:#f59e0b;font-size:1.1rem"></i>
                <div>
                    <strong style="color:#f59e0b">Active plans cannot be edited.</strong>
                    <span style="color:#8892b0;font-size:.85rem"> Clone as a new version to make changes, then activate the new version.</span>
                </div>
                <form method="POST" action="<?= $base ?>/admin/commission-plans/clone/<?= $plan['id'] ?>" style="margin-left:auto">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="effective_date" value="<?= date('Y-m-d') ?>">
                    <button type="submit" class="cp-btn cp-btn-success"><i class="fas fa-copy me-1"></i>Clone as New Version</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($versions) && count($versions) > 1): ?>
            <div style="margin-bottom:1.5rem">
                <label class="cp-label">Version History</label>
                <div>
                    <?php foreach ($versions as $v): ?>
                        <span class="version-chip <?= $v['id'] == $plan['id'] ? 'current' : '' ?>">
                            v<?= $v['version'] ?> — <?= $v['status'] ?>
                            (<?= $v['level_count'] ?? 0 ?> levels)
                            <?= $v['id'] == $plan['id'] ? '← current' : '' ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $base ?>/admin/commission-plans/update/<?= $plan['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="cp-label">Plan Name</label>
                    <input type="text" name="plan_name" class="cp-input" value="<?= htmlspecialchars($plan['plan_name']) ?>" required <?= $canEdit ? '' : 'disabled' ?>>
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Code</label>
                    <input type="text" class="cp-input" value="<?= htmlspecialchars($plan['plan_code']) ?>" disabled>
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Type</label>
                    <select name="plan_type" class="cp-input" <?= $canEdit ? '' : 'disabled' ?>>
                        <?php foreach (['hybrid','binary','unilevel','matrix'] as $t): ?>
                            <option value="<?= $t ?>" <?= $plan['plan_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Effective Date</label>
                    <input type="date" name="effective_date" class="cp-input" value="<?= $plan['effective_date'] ?? '' ?>" <?= $canEdit ? '' : 'disabled' ?>>
                </div>
                <div class="col-md-3">
                    <label class="cp-label">Description</label>
                    <input type="text" name="description" class="cp-input" value="<?= htmlspecialchars($plan['description'] ?? '') ?>" <?= $canEdit ? '' : 'disabled' ?>>
                </div>
            </div>

            <h6 style="color:#a855f7;margin-bottom:1rem"><i class="fas fa-cog me-1"></i>Global Parameters</h6>
            <div class="row mb-4">
                <div class="col-md-2"><label class="cp-label">Global Cap %</label><input type="number" name="global_cap_pct" class="cp-input" value="<?= $plan['global_cap_pct'] ?>" step="0.5" <?= $canEdit ? '' : 'disabled' ?>></div>
                <div class="col-md-2"><label class="cp-label">Track A %</label><input type="number" name="track_a_pct" class="cp-input" value="<?= $plan['track_a_pct'] ?>" step="0.5" <?= $canEdit ? '' : 'disabled' ?>></div>
                <div class="col-md-2"><label class="cp-label">Track B %</label><input type="number" name="track_b_pct" class="cp-input" value="<?= $plan['track_b_pct'] ?>" step="0.5" <?= $canEdit ? '' : 'disabled' ?>></div>
                <div class="col-md-2"><label class="cp-label">Track C %</label><input type="number" name="track_c_pct" class="cp-input" value="<?= $plan['track_c_pct'] ?>" step="0.5" <?= $canEdit ? '' : 'disabled' ?>></div>
                <div class="col-md-2"><label class="cp-label">Royalty %</label><input type="number" name="royalty_pool_pct" class="cp-input" value="<?= $plan['royalty_pool_pct'] ?>" step="0.5" <?= $canEdit ? '' : 'disabled' ?>></div>
                <div class="col-md-1"><label class="cp-label">Override G1</label><input type="number" name="same_level_override_gen1" class="cp-input" value="<?= $plan['same_level_override_gen1'] ?>" step="0.5" <?= $canEdit ? '' : 'disabled' ?>></div>
                <div class="col-md-1"><label class="cp-label">Override G2</label><input type="number" name="same_level_override_gen2" class="cp-input" value="<?= $plan['same_level_override_gen2'] ?>" step="0.5" <?= $canEdit ? '' : 'disabled' ?>></div>
            </div>

            <h6 style="color:#a855f7;margin-bottom:1rem"><i class="fas fa-layer-group me-1"></i>Rank Levels</h6>
            <div style="margin-bottom:1.5rem;overflow-x:auto">
                <table class="table rank-table m-0">
                    <thead>
                        <tr><th>#</th><th>Level</th><th>Direct %</th><th>Team %</th><th>Level Bonus %</th><th>Matching %</th><th>Leadership %</th><th>Performance %</th><th>GBV Threshold</th><th>Row Total</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($levels)): ?>
                            <tr><td colspan="10" style="color:#8892b0;text-align:center;padding:1.5rem">No levels configured</td></tr>
                        <?php else: ?>
                            <?php foreach ($levels as $lv):
                                $rowTotal = (float)$lv['direct_commission'] + (float)$lv['team_commission'] + (float)$lv['level_bonus'] + (float)$lv['matching_bonus'] + (float)$lv['leadership_bonus'] + (float)$lv['performance_bonus'];
                            ?>
                            <tr>
                                <td style="color:#8892b0"><?= $lv['level_order'] ?></td>
                                <td style="color:#e0e0e0;font-weight:600"><?= htmlspecialchars($lv['level_name']) ?></td>
                                <td><input type="number" name="levels[<?= $lv['id'] ?>][direct_commission]" value="<?= $lv['direct_commission'] ?>" step="0.01" min="0" max="100" <?= $canEdit ? '' : 'disabled' ?>></td>
                                <td><input type="number" name="levels[<?= $lv['id'] ?>][team_commission]" value="<?= $lv['team_commission'] ?>" step="0.01" min="0" max="100" <?= $canEdit ? '' : 'disabled' ?>></td>
                                <td><input type="number" name="levels[<?= $lv['id'] ?>][level_bonus]" value="<?= $lv['level_bonus'] ?>" step="0.01" min="0" max="100" <?= $canEdit ? '' : 'disabled' ?>></td>
                                <td><input type="number" name="levels[<?= $lv['id'] ?>][matching_bonus]" value="<?= $lv['matching_bonus'] ?>" step="0.01" min="0" max="100" <?= $canEdit ? '' : 'disabled' ?>></td>
                                <td><input type="number" name="levels[<?= $lv['id'] ?>][leadership_bonus]" value="<?= $lv['leadership_bonus'] ?>" step="0.01" min="0" max="100" <?= $canEdit ? '' : 'disabled' ?>></td>
                                <td><input type="number" name="levels[<?= $lv['id'] ?>][performance_bonus]" value="<?= $lv['performance_bonus'] ?>" step="0.01" min="0" max="100" <?= $canEdit ? '' : 'disabled' ?>></td>
                                <td><input type="number" name="levels[<?= $lv['id'] ?>][monthly_target]" value="<?= $lv['monthly_target'] ?>" step="1000" min="0" <?= $canEdit ? '' : 'disabled' ?>></td>
                                <td style="color:<?= $rowTotal > 20 ? '#ef4444' : '#22c55e' ?>;font-weight:600;font-size:.82rem"><?= number_format($rowTotal, 1) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($canEdit): ?>
            <div class="d-flex gap-2">
                <button type="submit" class="cp-btn cp-btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                <?php if (!$isActive): ?>
                    <form method="POST" action="<?= $base ?>/admin/commission-plans/activate/<?= $plan['id'] ?>" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="cp-btn cp-btn-success"><i class="fas fa-power-off me-1"></i>Activate This Plan</button>
                    </form>
                <?php endif; ?>
                <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline">Cancel</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>
