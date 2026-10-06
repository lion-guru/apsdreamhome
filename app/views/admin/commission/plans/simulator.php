<?php
$plans = $plans ?? [];
$activePlan = $activePlan ?? null;
$result = $result ?? null;
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$ranks = ['Associate','Sr. Associate','BDM','Sr. BDM','Vice President','President','Site Manager'];
$simMode = $_POST['sim_mode'] ?? 'single';
?>
<style>
.cp-card{background:#1a1f36;border:1px solid #2a2f4a;border-radius:12px;color:#e0e0e0;margin-bottom:1.5rem}
.cp-card-header{background:linear-gradient(135deg,#141829,#1e2340);padding:1rem 1.5rem;border-bottom:1px solid #2a2f4a;display:flex;justify-content:space-between;align-items:center}
.cp-card-body{padding:1.5rem}
.cp-btn{padding:8px 20px;border-radius:8px;font-size:.85rem;font-weight:500;border:none;cursor:pointer;transition:all .2s}
.cp-btn-primary{background:linear-gradient(135deg,#4f8cff,#6366f1);color:#fff}
.cp-btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 15px #4f8cff44}
.cp-btn-outline{background:transparent;border:1px solid #4f8cff44;color:#4f8cff;text-decoration:none;display:inline-block}
.cp-input{background:#0f1225;border:1px solid #2a2f4a;border-radius:8px;color:#e0e0e0;padding:8px 12px;width:100%;font-size:.85rem}
.cp-label{color:#8892b0;font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;display:block}
.result-card{background:#141829;border:1px solid #2a2f4a;border-radius:10px;padding:16px;text-align:center}
.result-num{font-size:1.6rem;font-weight:700;background:linear-gradient(135deg,#4f8cff,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.result-label{font-size:.7rem;color:#8892b0;text-transform:uppercase;letter-spacing:.5px;margin-top:4px}
.sim-table th{background:#1e2340;color:#8892b0;font-size:.72rem;text-transform:uppercase;padding:8px 12px;border:none}
.sim-table td{padding:8px 12px;border-top:1px solid #1e2340;color:#e0e0e0;font-size:.82rem}
.track-bar{height:8px;border-radius:4px;background:#1e2340;overflow:hidden;margin-top:4px}
.track-bar-fill{height:100%;border-radius:4px}
.mode-tab{padding:6px 16px;border-radius:8px;border:1px solid #2a2f4a;background:#141829;color:#8892b0;cursor:pointer;font-size:.82rem;transition:all .2s}
.mode-tab.active{background:#4f8cff22;color:#4f8cff;border-color:#4f8cff}
.mode-tab:hover{border-color:#4f8cff66}
.cp-version{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:600;background:#1e2340;color:#a855f7;border:1px solid #a855f733}
</style>

<div class="cp-card">
    <div class="cp-card-header">
        <h5 class="m-0"><i class="fas fa-flask me-2"></i>Commission What-If Simulator</h5>
        <div class="d-flex gap-2">
            <button type="button" class="cp-btn cp-btn-outline" data-bs-toggle="modal" data-bs-target="#savePresetModal"><i class="fas fa-save me-1"></i>Save Preset</button>
            <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="cp-card-body">
        <form method="POST" id="simForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="cp-label">Sale Amount (₹)</label>
                    <input type="number" name="sale_amount" class="cp-input" value="<?= htmlspecialchars($_POST['sale_amount'] ?? 1500000, ENT_QUOTES, 'UTF-8') ?>" step="10000" min="0">
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Plan A</label>
                    <select name="plan_id" class="cp-input">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($activePlan && $activePlan['id'] == $p['id'] && empty($_POST['plan_id'])) || ($_POST['plan_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['plan_name'] ?? '') ?> v<?= $p['version'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Seller Rank</label>
                    <select name="rank_index" class="cp-input">
                        <?php foreach ($ranks as $i => $r): ?>
                            <option value="<?= $i ?>" <?= ($_POST['rank_index'] ?? 0) == $i ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
<div class="col-md-2">
                <label class="cp-label">Mode</label>
                <div class="d-flex gap-1 flex-wrap">
                    <button type="button" class="mode-tab <?= $simMode === 'single' ? 'active' : '' ?>" onclick="setMode('single')">Single</button>
                    <button type="button" class="mode-tab <?= $simMode === 'bulk' ? 'active' : '' ?>" onclick="setMode('bulk')">All Ranks</button>
                    <button type="button" class="mode-tab <?= $simMode === 'compare' ? 'active' : '' ?>" onclick="setMode('compare')">Compare</button>
                    <button type="button" class="mode-tab <?= $simMode === 'referral_sweep' ? 'active' : '' ?>" onclick="setMode('referral_sweep')">Referral %</button>
                    <button type="button" class="mode-tab <?= $simMode === 'wallet_sweep' ? 'active' : '' ?>" onclick="setMode('wallet_sweep')">Wallet %</button>
                </div>
            </div>
            <div class="col-md-2">
                <label class="cp-label">Presets</label>
                <select name="preset_id" class="cp-input" id="presetSelect" onchange="loadPreset(this.value)">
                    <option value="">-- Load Preset --</option>
                    <?php foreach (($presets ?? []) as $pr): ?>
                        <option value="<?= $pr['id'] ?>" data-mode="<?= htmlspecialchars($pr['sim_mode']) ?>" data-params='<?= htmlspecialchars($pr['params_json'], ENT_QUOTES) ?>'>
                            <?= htmlspecialchars($pr['name']) ?> <span class="cp-version"><?= $pr['sim_mode'] ?></span>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($simMode === 'compare'): ?>
                <div class="col-md-2">
                    <label class="cp-label">Plan B</label>
                    <select name="plan_id_b" class="cp-input">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($_POST['plan_id_b'] ?? 0) == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['plan_name'] ?? '') ?> v<?= $p['version'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <label class="cp-label">&nbsp;</label>
                    <button type="submit" name="sim_mode" value="<?= $simMode ?>" class="cp-btn cp-btn-primary"><i class="fas fa-play me-1"></i>Simulate</button>
                </div>
            </div>
            <?php if (in_array($simMode, ['referral_sweep', 'wallet_sweep'], true)): ?>
            <div class="row mb-3">
                <?php if ($simMode === 'referral_sweep'): ?>
                <div class="col-md-3">
                    <label class="cp-label">Candidate Customer % (guard ≤5)</label>
                    <input type="number" name="candidate_pct" class="cp-input" value="<?= htmlspecialchars($_POST['candidate_pct'] ?? 2.0, ENT_QUOTES, 'UTF-8') ?>" step="0.1" min="0" max="5">
                </div>
                <?php else: ?>
                <div class="col-md-3">
                    <label class="cp-label">Candidate L1 % (guard L1+L2≤30)</label>
                    <input type="number" name="candidate_l1" class="cp-input" value="<?= htmlspecialchars($_POST['candidate_l1'] ?? 20.0, ENT_QUOTES, 'UTF-8') ?>" step="0.5" min="0" max="30">
                </div>
                <div class="col-md-3">
                    <label class="cp-label">Candidate L2 %</label>
                    <input type="number" name="candidate_l2" class="cp-input" value="<?= htmlspecialchars($_POST['candidate_l2'] ?? 5.0, ENT_QUOTES, 'UTF-8') ?>" step="0.5" min="0" max="30">
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <label class="cp-label">History Window (days)</label>
                    <input type="number" name="window_days" class="cp-input" value="<?= htmlspecialchars($_POST['window_days'] ?? 90, ENT_QUOTES, 'UTF-8') ?>" step="30" min="7" max="365">
                </div>
                <div class="col-md-3">
                    <label class="cp-label">&nbsp;</label>
                    <div class="small" style="color:#8892b0">Replays real history. Nothing is written — Apply happens in service-configs.</div>
                </div>
            </div>
            <?php endif; ?>
        </form>

        <?php if ($result && ($result['success'] ?? false)): ?>
            <?php if ($simMode === 'single'): ?>
                <?php
                $r = $result;
                $totalCap = $r['global_cap'];
                $pctUsed = $totalCap > 0 ? min(100, ($r['total_distributed'] / $totalCap) * 100) : 0;
                ?>
                <div class="row mb-4">
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($r['sale_amount']) ?></div><div class="result-label">Sale Amount</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num"><?= $r['seller_rank'] ?></div><div class="result-label"><?= $r['seller_rate'] ?>% Direct Rate</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($r['global_cap']) ?></div><div class="result-label">Global Cap (<?= $result['plan']['name'] ?? '' ?>)</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($r['track_a_total']) ?></div><div class="result-label">Track A Total</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($r['total_distributed']) ?></div><div class="result-label">Total Distributed</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num"><?= $r['payout_ratio'] ?>%</div><div class="result-label">Payout Ratio</div></div></div>
                </div>

                <div >
                    <div >
                        <span >Cap Utilization: <?= number_format($pctUsed, 1) ?>%</span>
                        <span >Remaining: ₹<?= number_format($r['remaining_cap']) ?></span>
                    </div>
                    <div class="track-bar">
                        <div class="track-bar-fill"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <h6 >Track A: Slab Differential Breakdown</h6>
                        <div >
                            <table class="table sim-table m-0">
                                <thead><tr><th>Recipient</th><th>Type</th><th>Rate</th><th>Amount</th><th>% of Sale</th></tr></thead>
                                <tbody>
                                    <?php foreach ($r['track_a_entries'] as $e): ?>
                                    <tr>
                                        <td ><?= htmlspecialchars($e['label'] ?? '') ?></td>
                                        <td><span ><?= $e['type'] ?></span></td>
                                        <td><?= $e['rate'] ?>%</td>
                                        <td >₹<?= number_format($e['amount']) ?></td>
                                        <td ><?= $r['sale_amount'] > 0 ? number_format(($e['amount'] / $r['sale_amount']) * 100, 2) : 0 ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr ><td colspan="3" >Track A Total</td><td >₹<?= number_format($r['track_a_total']) ?></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 >Tracks B + C</h6>
                        <?php foreach ($r['track_b_entries'] as $e): ?>
                        <div >
                            <div ><?= htmlspecialchars($e['label'] ?? '') ?></div>
                            <div >₹<?= number_format($e['amount']) ?></div>
                        </div>
                        <?php endforeach; ?>
                        <?php foreach ($r['track_c_entries'] as $e): ?>
                        <div >
                            <div ><?= htmlspecialchars($e['label'] ?? '') ?></div>
                            <div >₹<?= number_format($e['amount']) ?></div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (!empty($r['monthly_bonuses'])): ?>
                        <h6 >Monthly Bonuses (Estimated)</h6>
                        <?php foreach ($r['monthly_bonuses'] as $bName => $b): ?>
                        <div >
                            <span ><?= ucfirst($bName) ?> (<?= $b['rate'] ?>%)</span>
                            <span >₹<?= number_format($b['estimated']) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($simMode === 'bulk'): ?>
                <h6 >All Ranks at ₹<?= number_format($_POST['sale_amount'] ?? 1500000) ?> — <?= htmlspecialchars($result['plan']['name'] ?? '') ?></h6>
                <div >
                    <table class="table sim-table m-0">
                        <thead><tr><th>Rank</th><th>Direct Rate</th><th>Track A</th><th>Track B</th><th>Track C</th><th>Total Payout</th><th>Payout %</th></tr></thead>
                        <tbody>
                            <?php foreach ($result['rank_results'] as $rr): ?>
                            <?php if ($rr['success']): ?>
                            <tr>
                                <td ><?= htmlspecialchars($rr['seller_rank'] ?? '') ?></td>
                                <td><?= $rr['seller_rate'] ?>%</td>
                                <td>₹<?= number_format($rr['track_a_total']) ?></td>
                                <td>₹<?= number_format($rr['track_b_total']) ?></td>
                                <td>₹<?= number_format($rr['track_c_total']) ?></td>
                                <td >₹<?= number_format($rr['total_distributed']) ?></td>
                                <td><?= $rr['payout_ratio'] ?>%</td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($simMode === 'compare'): ?>
                <?php
                $simA = $result['plan_a'];
                $simB = $result['plan_b'];
                ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div >
                            <div ><?= htmlspecialchars($simA['plan']['name'] ?? '') ?></div>
                            <div >₹<?= number_format($simA['total_distributed']) ?></div>
                            <div ><?= $simA['payout_ratio'] ?>% payout ratio</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div >
                            <div ><?= htmlspecialchars($simB['plan']['name'] ?? '') ?></div>
                            <div >₹<?= number_format($simB['total_distributed']) ?></div>
                            <div ><?= $simB['payout_ratio'] ?>% payout ratio</div>
                        </div>
                    </div>
                </div>
                <?php
                $diffTotal = $simB['total_distributed'] - $simA['total_distributed'];
                $diffCap = $simB['global_cap'] - $simA['global_cap'];
                ?>
                <div class="result-card">
                    <div >Difference (Plan B âˆ’ Plan A)</div>
                    <div class="result-num">₹<?= number_format($diffTotal) ?></div>
                    <div ><?= $diffTotal > 0 ? 'Plan B pays MORE' : ($diffTotal < 0 ? 'Plan A pays MORE' : 'Same payout') ?></div>
                </div>

            <?php elseif ($simMode === 'referral_sweep'): ?>
                <?php $rs = $result; ?>
                <h6>Customer Referral @ <?= htmlspecialchars($rs['candidate_pct']) ?>% — last <?= (int)$rs['window_days'] ?> days of paid bookings</h6>
                <?php if (!empty($rs['warnings'])): ?>
                    <?php foreach ($rs['warnings'] as $w): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($w) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="row mb-4">
                    <div class="col-md-2"><div class="result-card"><div class="result-num"><?= number_format($rs['paid_bookings']) ?></div><div class="result-label">Paid Bookings</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($rs['volume']) ?></div><div class="result-label">Volume</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num"><?= htmlspecialchars($rs['current_pct']) ?>%</div><div class="result-label">Current Policy</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($rs['baseline_payout']) ?></div><div class="result-label">Baseline Payout</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($rs['projected_payout']) ?></div><div class="result-label">Projected @ <?= htmlspecialchars($rs['candidate_pct']) ?>%</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($rs['delta']) ?></div><div class="result-label">Delta vs Baseline</div></div></div>
                </div>
                <form method="POST" action="<?= $base ?>/admin/service-configs/update" onsubmit="return confirm('Apply <?= htmlspecialchars($rs['candidate_pct']) ?>% as the live customer-referral rate? Past payouts are NOT recalculated.');">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="configs[referral][customer_booking_pct]" value="<?= htmlspecialchars($rs['candidate_pct']) ?>">
                    <button type="submit" class="cp-btn cp-btn-primary"><i class="fas fa-check me-1"></i>Apply <?= htmlspecialchars($rs['candidate_pct']) ?>% as Live Rate</button>
                    <span class="small" style="color:#8892b0">Writes service_configs (audited) · prospective only · current: <?= htmlspecialchars($rs['current_pct']) ?>%</span>
                </form>

            <?php elseif ($simMode === 'wallet_sweep'): ?>
                <?php $ws = $result; ?>
                <h6>Wallet Activation L1 <?= htmlspecialchars($ws['candidate_l1']) ?>% + L2 <?= htmlspecialchars($ws['candidate_l2']) ?>% — last <?= (int)$ws['window_days'] ?> days</h6>
                <?php if (!empty($ws['warnings'])): ?>
                    <?php foreach ($ws['warnings'] as $w): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($w) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="row mb-4">
                    <div class="col-md-3"><div class="result-card"><div class="result-num">₹<?= number_format($ws['total_revenue']) ?></div><div class="result-label">Activation Revenue</div></div></div>
                    <div class="col-md-3"><div class="result-card"><div class="result-num">₹<?= number_format($ws['total_payout']) ?></div><div class="result-label">Referral Payout</div></div></div>
                    <div class="col-md-3"><div class="result-card"><div class="result-num">₹<?= number_format($ws['company_keeps']) ?></div><div class="result-label">Company Keeps</div></div></div>
                    <div class="col-md-3"><div class="result-card"><div class="result-num"><?= htmlspecialchars($ws['company_margin_pct']) ?>%</div><div class="result-label">Margin</div></div></div>
                </div>
                <?php if (!empty($ws['per_package'])): ?>
                <table class="table sim-table">
                    <thead><tr><th>Package</th><th>Activations</th><th>Revenue</th><th>L1 Payout</th><th>L2 Payout</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($ws['per_package'] as $pp): ?>
                        <tr>
                            <td><?= htmlspecialchars($pp['package']) ?></td>
                            <td><?= number_format($pp['activations']) ?></td>
                            <td>₹<?= number_format($pp['revenue']) ?></td>
                            <td>₹<?= number_format($pp['l1_payout']) ?></td>
                            <td>₹<?= number_format($pp['l2_payout']) ?></td>
                            <td>₹<?= number_format($pp['total_payout']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
                <?php $pkgCount = count($ws['per_package'] ?? []); ?>
                <form method="POST" action="<?= $base ?>/admin/commission-plans/apply-wallet-pct" onsubmit="return confirm('Set L1=<?= htmlspecialchars($ws['candidate_l1']) ?>% L2=<?= htmlspecialchars($ws['candidate_l2']) ?>% on ALL active wallet packages (<?= $pkgCount ?>)? Past activations are NOT recalculated.');">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="candidate_l1" value="<?= htmlspecialchars($ws['candidate_l1']) ?>">
                    <input type="hidden" name="candidate_l2" value="<?= htmlspecialchars($ws['candidate_l2']) ?>">
                    <button type="submit" class="cp-btn cp-btn-primary"><i class="fas fa-check me-1"></i>Apply L1/L2 to All Packages</button>
                    <span class="small" style="color:#8892b0">Bulk-updates package rows (audited) · prospective only</span>
                </form>

            <?php endif; ?>
        <?php elseif ($result && !($result['success'] ?? false)): ?>
            <div >
                <i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($result['error'] ?? 'Simulation failed') ?>
            </div>
        <?php else: ?>
            <div >
                <i class="fas fa-flask"></i>
                Configure parameters above and click Simulate to run what-if analysis.
            </div>
        <?php endif; ?>
    </div>
</div>

</form>
    </div>
</div>

<!-- Save Preset Modal -->
<div class="modal fade" id="savePresetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1a1f36;border:1px solid #2a2f4a;border-radius:12px">
            <div class="modal-header" style="border-bottom:1px solid #2a2f4a;background:#141829">
                <h5 class="modal-title"><i class="fas fa-save me-2"></i>Save Simulation Preset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $base ?>/admin/commission-plans/save-preset" onsubmit="return savePreset(event)">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="sim_mode" id="saveSimMode" value="<?= $simMode ?>">
                <input type="hidden" name="params_json" id="saveParamsJson" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-white">Preset Name</label>
                        <input type="text" name="name" class="cp-input" required placeholder="e.g., Diwali 2026 Push, Monsoon Slowdown">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Description</label>
                        <textarea name="description" class="cp-input" rows="3" placeholder="Optional notes about this scenario..."></textarea>
                    </div>
                    <div class="alert alert-info small mb-0">Current mode: <strong id="currentMode"><?= $simMode ?></strong> — will be saved with all current parameters</div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #2a2f4a;background:#141829">
                    <button type="button" class="cp-btn cp-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="cp-btn cp-btn-primary"><i class="fas fa-save me-1"></i>Save Preset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Presets Management Modal -->
<div class="modal fade" id="managePresetsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:#1a1f36;border:1px solid #2a2f4a;border-radius:12px">
            <div class="modal-header" style="border-bottom:1px solid #2a2f4a;background:#141829">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Saved Presets</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($presets)): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mode</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($presets as $pr): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pr['name']) ?></strong></td>
                                    <td><span class="cp-version"><?= $pr['sim_mode'] ?></span></td>
                                    <td class="text-muted small"><?= htmlspecialchars($pr['description'] ?? '-') ?></td>
                                    <td class="small"><?= date('d M Y', strtotime($pr['created_at'])) ?></td>
                                    <td class="text-end">
                                        <button class="cp-btn cp-btn-outline cp-btn-sm" onclick="loadPresetById(<?= $pr['id'] ?>); bootstrap.Modal.getInstance(document.getElementById('managePresetsModal')).hide();"><i class="fas fa-play me-1"></i>Load</button>
                                        <button class="cp-btn cp-btn-outline cp-btn-sm" onclick="deletePreset(<?= $pr['id'] ?>)" style="background:#dc262622;border-color:#dc2626;color:#f87171"><i class="fas fa-trash me-1"></i>Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No saved presets yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function setMode(mode) {
    document.querySelectorAll('.mode-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    document.querySelector('input[name="sim_mode"]').value = mode;
    document.getElementById('simForm').submit();
}

function loadPreset(presetId) {
    if (!presetId) return;
    const opt = document.querySelector('#presetSelect option[value="' + presetId + '"]');
    if (!opt) return;
    const params = JSON.parse(opt.dataset.params || '{}');
    const mode = opt.dataset.mode;
    
    // Set mode
    document.querySelector('input[name="sim_mode"]').value = mode;
    document.querySelectorAll('.mode-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('.mode-tab[onclick*="' + mode + '"]')?.classList.add('active');
    
    // Fill form fields
    Object.entries(params).forEach(([key, val]) => {
        const el = document.querySelector('[name="' + key + '"]');
        if (el) el.value = val;
    });
    
    // Submit to refresh page with new mode
    document.getElementById('simForm').submit();
}

async function loadPresetById(presetId) {
    try {
        const res = await fetch('<?= $base ?>/admin/commission-plans/preset/' + presetId, {
            headers: { 'X-CSRF-Token': '<?= $csrf_token ?>' }
        });
        const data = await res.json();
        if (data.success) {
            loadPreset(data.preset.id); // uses existing logic
        }
    } catch (e) { console.error(e); }
}

async function savePreset(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    // Capture all current form values
    const params = {};
    document.querySelectorAll('#simForm input, #simForm select').forEach(el => {
        if (el.name && el.name !== 'csrf_token' && el.name !== 'sim_mode' && el.name !== 'preset_id') {
            params[el.name] = el.value;
        }
    });
    formData.set('params_json', JSON.stringify(params));
    
    try {
        const res = await fetch('<?= $base ?>/admin/commission-plans/save-preset', {
            method: 'POST',
            headers: { 'X-CSRF-Token': '<?= $csrf_token ?>' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            alert('Preset saved!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save'));
        }
    } catch (e) {
        console.error(e);
        alert('Error saving preset');
    }
}

async function deletePreset(presetId) {
    if (!confirm('Delete this preset?')) return;
    try {
        const res = await fetch('<?= $base ?>/admin/commission-plans/preset/' + presetId + '/delete', {
            method: 'POST',
            headers: { 'X-CSRF-Token': '<?= $csrf_token ?>' }
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete'));
        }
    } catch (e) {
        console.error(e);
        alert('Error deleting preset');
    }
}
</script>
