<?php
$benefits = $benefits ?? [];
$levels   = $levels ?? [];
$settings = $settings ?? [];
$base     = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="m-0"><i class="fas fa-cogs text-primary me-2"></i>MLM Plan Editor</h4>
    <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/rank-benefits" class="btn btn-outline-secondary btn-sm"><i class="fas fa-eye me-1"></i>View Benefits</a>
</div>

<?php if ($msg = \App\Core\Session::flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $msg; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($msg = \App\Core\Session::flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= $msg; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<form method="POST" action="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/plan-editor/update">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <!-- RANK BENEFITS TABLE -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-medal text-warning me-2"></i>Rank Benefits — Commission Rates & Thresholds</h5>
        </div>
        <div class="alert alert-info mb-3 mx-3 mt-3" role="alert">
            <h6 class="alert-heading"><i class="fas fa-layer-group me-2"></i>Differential Commission Model Active</h6>
            <p class="mb-1"><strong>Upline Override = Upline Rate âˆ’ Rate of Level Below</strong></p>
            <p class="mb-0 small">The <code>direct_sale_pct</code> below is each rank's own commission rate. Upline overrides are calculated dynamically as the difference between their rate and the rate of the person directly below them. <strong>L1/L2/L3 columns are no longer used</strong> (set to 0 in DB). Same-rank breakaway: 2% Gen 1, 1% Gen 2, 0% Gen 3+.</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Order</th>
                            <th>Rank Name</th>
                            <th>Min Legs</th>
                            <th>Min Volume (₹)</th>
                            <th>Direct %</th>
                            <th>Commission Model</th>
                            <th>Badge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($benefits as $b):
                            $rn = htmlspecialchars($b['rank_name'] ?? '');
                        ?>
                            <tr>
                                <td>
                                    <input type="number" name="benefits[<?= $rn ?>][rank_order]" value="<?= (int)$b['rank_order'] ?>" class="form-control form-control-sm" class="style-30170" min="1" max="99">
                                </td>
                                <td class="fw-bold">
                                    <span class="badge" class="style-61509">
                                        <i class="fas <?= htmlspecialchars($b['badge_icon'] ?? 'fa-user') ?>"></i>
                                    </span>
                                    <?= $rn ?>
                                </td>
                                <td>
                                    <input type="number" name="benefits[<?= $rn ?>][min_legs]" value="<?= (int)($b['min_leg_count'] ?? 0) ?>" class="form-control form-control-sm" class="style-73350" min="0">
                                </td>
                                <td>
                                    <input type="number" name="benefits[<?= $rn ?>][min_volume]" value="<?= (float)$b['min_qualifying_volume'] ?>" class="form-control form-control-sm" class="style-39472" min="0" step="1000">
                            </td>
                            <td>
                                <input type="number" name="benefits[<?= $rn ?>][direct_sale_pct]" value="<?= (float)$b['direct_sale_pct'] ?>" class="form-control form-control-sm" class="style-73350" min="0" max="100" step="0.1" required>
                            </td>
                            <td class="text-center">
                                <span class="text-muted small">Differential</span>
                                <input type="hidden" name="benefits[<?= $rn ?>][l1_pct]" value="0">
                                <input type="hidden" name="benefits[<?= $rn ?>][l2_pct]" value="0">
                                <input type="hidden" name="benefits[<?= $rn ?>][l3_pct]" value="0">
                                <div class="form-text small text-info"><i class="fas fa-info-circle"></i> Upline gets (their rate âˆ’ rate below)</div>
                            </td>
                                <td>
                                    <span class="badge" class="style-39009">
                                        <?= htmlspecialchars($b['badge_icon'] ?? 'fa-user') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DIFFERENTIAL LEVELS TABLE -->
    <?php if (!empty($levels)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-layer-group text-info me-2"></i>Differential Commission Levels</h5>
            <small class="text-muted">These rates are used by the Differential Commission Calculator for upline earnings.</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Level Name</th>
                            <th>Direct %</th>
                            <th>Team %</th>
                            <th>Diff %</th>
                            <th>Matching %</th>
                            <th>Leadership %</th>
                            <th>Performance %</th>
                            <th>Team Size</th>
                            <th>Direct Refs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($levels as $l): ?>
                            <tr>
                                <td><?= (int)$l['level_number'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($l['level_name'] ?? '') ?></td>
                                <td><strong><?= number_format((float)$l['direct_commission_percentage'], 1) ?>%</strong></td>
                                <td><?= number_format((float)($l['team_commission_percentage'] ?? 0), 1) ?>%</td>
                                <td><?= number_format((float)($l['level_difference_commission_percentage'] ?? 0), 1) ?>%</td>
                                <td><?= number_format((float)($l['matching_bonus_percentage'] ?? 0), 1) ?>%</td>
                                <td><?= number_format((float)($l['leadership_bonus_percentage'] ?? 0), 1) ?>%</td>
                                <td><?= number_format((float)($l['performance_bonus_percentage'] ?? 0), 1) ?>%</td>
                                <td><?= (int)($l['team_size_required'] ?? 0) ?></td>
                                <td><?= (int)($l['direct_referrals_required'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>To edit differential levels, go to <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm-settings/levels">MLM Settings â†’ Levels</a></small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- GLOBAL SETTINGS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-sliders-h text-success me-2"></i>Global Cap & Pool Settings</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Global Commission Cap (%)</label>
                    <div class="input-group">
                        <input type="number" name="settings[global_cap_pct]" value="<?= htmlspecialchars($settings['global_cap_pct'] ?? '20') ?>" class="form-control" min="1" max="50" step="0.5">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Max % of sale value distributed as commission</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Royalty Pool (%)</label>
                    <div class="input-group">
                        <input type="number" name="settings[royalty_pool_pct]" value="<?= htmlspecialchars($settings['royalty_pool_pct'] ?? '2') ?>" class="form-control" min="0" max="10" step="0.5">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Shared by VP+ ranks from total sales</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Min Qualifying Volume (₹)</label>
                    <input type="number" name="settings[min_qualifying_volume]" value="<?= htmlspecialchars($settings['min_qualifying_volume'] ?? '50000') ?>" class="form-control" min="0" step="10000">
                    <small class="text-muted">Min monthly team sales for Track B</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Escrow Release Threshold (₹)</label>
                    <input type="number" name="settings[escrow_release_threshold]" value="<?= htmlspecialchars($settings['escrow_release_threshold'] ?? '100000') ?>" class="form-control" min="10000" step="10000">
                    <small class="text-muted">Min escrow balance to trigger payout</small>
                </div>
            </div>
        </div>
    </div>

    <!-- MATCHING BONUS SETTINGS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-hands-helping text-warning me-2"></i>Matching Bonus</h5>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="settings[matching_bonus_enabled]" value="1" id="matchingToggle"
                    <?= ($settings['matching_bonus_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="matchingToggle">Enabled</label>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 mb-3">
                <small><strong>How it works:</strong> Upline leader earns a % of their downline leader's commission earnings. Gen 1 = 100% means you earn the same amount your Gen 1 downline leader earned.</small>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Gen 1 Match Rate (%)</label>
                    <div class="input-group">
                        <input type="number" name="settings[gen1_match_pct]" value="<?= htmlspecialchars($settings['gen1_match_pct'] ?? '100') ?>" class="form-control" min="0" max="200" step="5">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Default: 100%</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Gen 2 Match Rate (%)</label>
                    <div class="input-group">
                        <input type="number" name="settings[gen2_match_pct]" value="<?= htmlspecialchars($settings['gen2_match_pct'] ?? '50') ?>" class="form-control" min="0" max="200" step="5">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Default: 50%</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Gen 3 Match Rate (%)</label>
                    <div class="input-group">
                        <input type="number" name="settings[gen3_match_pct]" value="<?= htmlspecialchars($settings['gen3_match_pct'] ?? '25') ?>" class="form-control" min="0" max="200" step="5">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Default: 25%</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Max Generations</label>
                    <input type="number" name="settings[matching_max_levels]" value="<?= htmlspecialchars($settings['matching_max_levels'] ?? '3') ?>" class="form-control" min="1" max="10">
                    <small class="text-muted">How many generations deep</small>
                </div>
            </div>
        </div>
    </div>

    <!-- GENERATION BONUS SETTINGS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-network-wired text-info me-2"></i>Generation Bonus</h5>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="settings[generation_bonus_enabled]" value="1" id="genBonusToggle"
                    <?= ($settings['generation_bonus_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="genBonusToggle">Enabled</label>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 mb-3">
                <small><strong>How it works:</strong> Monthly bonus based on your downline's total GBV across 7 generations. Runs once per month via cron. Rates: Gen1=2%, Gen2=1.5%, Gen3=1%, Gen4-7=0.5%.</small>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Base Rate (%)</label>
                    <div class="input-group">
                        <input type="number" name="settings[generation_bonus_pct]" value="<?= htmlspecialchars($settings['generation_bonus_pct'] ?? '5') ?>" class="form-control" min="0" max="20" step="0.5">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Applied to Gen 1 volume</small>
                </div>
            </div>
        </div>
    </div>

    <!-- INFINITY OVERRIDE SETTINGS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-infinity text-purple me-2" class="style-58381"></i>Infinity Override</h5>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="settings[infinity_override_enabled]" value="1" id="infinityToggle"
                    <?= ($settings['infinity_override_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="infinityToggle">Enabled</label>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 mb-3">
                <small><strong>How it works:</strong> Qualified leaders earn this % on ALL sales in their downline regardless of breakaway depth. Designed to reward leaders who keep growing past same-rank bypasses.</small>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Override Rate (%)</label>
                    <div class="input-group">
                        <input type="number" name="settings[infinity_override_pct]" value="<?= htmlspecialchars($settings['infinity_override_pct'] ?? '1') ?>" class="form-control" min="0" max="5" step="0.25">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Default: 1%</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Min Qualifying Rank</label>
                    <select name="settings[infinity_min_rank]" class="form-select">
                        <?php
                        $rankOptions = ['associate'=>'Associate','sr_associate'=>'Sr. Associate','bdm'=>'BDM','sr_bdm'=>'Sr. BDM','vice_president'=>'Vice President','president'=>'President','site_manager'=>'Site Manager'];
                        $curRank = $settings['infinity_min_rank'] ?? 'vice_president';
                        foreach ($rankOptions as $rv => $rl): ?>
                        <option value="<?= $rv ?>" <?= $rv === $curRank ? 'selected' : '' ?>><?= $rl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Min rank to receive infinity override</small>
                </div>
            </div>
        </div>
    </div>

    <!-- RANK BONUS SETTINGS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-medal text-warning me-2"></i>Rank Promotion Bonuses</h5>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="settings[rank_bonus_enabled]" value="1" id="rankBonusToggle"
                    <?= ($settings['rank_bonus_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="rankBonusToggle">Enabled</label>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 mb-3">
                <small><strong>One-time cash bonus</strong> paid when an associate achieves a new rank. Values stored as JSON. Edit amounts per rank below.</small>
            </div>
            <?php
            $rankBonusAmounts = json_decode($settings['rank_bonus_amounts'] ?? '{}', true) ?: [
                'sr_associate'  => 5000,
                'bdm'           => 15000,
                'sr_bdm'        => 35000,
                'vice_president'=> 75000,
                'president'     => 150000,
                'site_manager'  => 300000,
            ];
            $rankLabels = ['sr_associate'=>'Sr. Associate','bdm'=>'BDM','sr_bdm'=>'Sr. BDM','vice_president'=>'Vice President','president'=>'President','site_manager'=>'Site Manager'];
            ?>
            <div class="row g-3">
                <?php foreach ($rankLabels as $rk => $rl): ?>
                <div class="col-md-2 col-sm-4">
                    <label class="form-label fw-semibold small"><?= $rl ?></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">₹</span>
                        <input type="number" name="rank_bonus[<?= $rk ?>]" value="<?= (int)($rankBonusAmounts[$rk] ?? 0) ?>"
                               class="form-control rank-bonus-input" min="0" step="1000" data-rank="<?= $rk ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Hidden JSON field updated by JS -->
            <input type="hidden" name="settings[rank_bonus_amounts]" id="rankBonusJson"
                   value="<?= htmlspecialchars(json_encode($rankBonusAmounts)) ?>">
        </div>
    </div>

    <!-- MONTHLY QUALIFICATION -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-calendar-check text-success me-2"></i>Monthly Qualification Rules</h5>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="settings[qualification_required]" value="1" id="qualToggle"
                    <?= ($settings['qualification_required'] ?? '1') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="qualToggle">Required</label>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Min Monthly Volume (₹)</label>
                    <input type="number" name="settings[min_monthly_volume]" value="<?= htmlspecialchars($settings['min_monthly_volume'] ?? '10000') ?>" class="form-control" min="0" step="1000">
                    <small class="text-muted">Associate must achieve this volume monthly to stay active</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SAVE BUTTON -->
    <div class="text-end mb-4">
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
        <button type="submit" class="btn btn-primary btn-lg" onclick="return confirmSave()">
            <i class="fas fa-save me-1"></i> Save MLM Plan
        </button>
    </div>
</form>

<!-- PLAN SUMMARY -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>Plan Quick Reference</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="bg-light rounded-3 p-3">
                    <h6 class="fw-bold text-primary">Commission Cap Rule</h6>
                    <p class="mb-0 small text-muted">Total commission per sale cannot exceed <strong><?= htmlspecialchars($settings['global_cap_pct'] ?? '20') ?>%</strong> of sale value. Any amount exceeding the cap is retained by the company.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light rounded-3 p-3">
                    <h6 class="fw-bold text-success">Royalty Pool</h6>
                    <p class="mb-0 small text-muted"><strong><?= htmlspecialchars($settings['royalty_pool_pct'] ?? '2') ?>%</strong> of all sales goes to the royalty pool, distributed monthly among VP, President, and Site Manager ranks proportional to their GBV.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light rounded-3 p-3">
                    <h6 class="fw-bold text-warning">Matching Bonus (Active)</h6>
                    <p class="mb-0 small text-muted">Gen1=<strong><?= htmlspecialchars($settings['gen1_match_pct'] ?? '100') ?>%</strong> &nbsp; Gen2=<strong><?= htmlspecialchars($settings['gen2_match_pct'] ?? '50') ?>%</strong> &nbsp; Gen3=<strong><?= htmlspecialchars($settings['gen3_match_pct'] ?? '25') ?>%</strong> of downline leader earnings.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmSave() {
    // Rebuild rank_bonus_amounts JSON from individual inputs before submit
    const inputs = document.querySelectorAll('.rank-bonus-input');
    const amounts = {};
    inputs.forEach(function(inp) {
        amounts[inp.dataset.rank] = parseInt(inp.value) || 0;
    });
    document.getElementById('rankBonusJson').value = JSON.stringify(amounts);
    return confirm('Save all changes to the MLM plan? This will affect commission calculations immediately.');
}
</script>

