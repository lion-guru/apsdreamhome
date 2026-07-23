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
        <h5 class="m-0" style="color:#e0e0e0"><i class="fas fa-flask me-2" style="color:#a855f7"></i>Commission What-If Simulator</h5>
        <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
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
                                <?= htmlspecialchars($p['plan_name']) ?> v<?= $p['version'] ?>
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
                    <div class="d-flex gap-1">
                        <button type="button" class="mode-tab <?= $simMode === 'single' ? 'active' : '' ?>" onclick="setMode('single')">Single</button>
                        <button type="button" class="mode-tab <?= $simMode === 'bulk' ? 'active' : '' ?>" onclick="setMode('bulk')">All Ranks</button>
                        <button type="button" class="mode-tab <?= $simMode === 'compare' ? 'active' : '' ?>" onclick="setMode('compare')">Compare</button>
                    </div>
                </div>
                <?php if ($simMode === 'compare'): ?>
                <div class="col-md-2">
                    <label class="cp-label">Plan B</label>
                    <select name="plan_id_b" class="cp-input">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($_POST['plan_id_b'] ?? 0) == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['plan_name']) ?> v<?= $p['version'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <label class="cp-label">&nbsp;</label>
                    <button type="submit" name="sim_mode" value="<?= $simMode ?>" class="cp-btn cp-btn-primary" style="width:100%"><i class="fas fa-play me-1"></i>Simulate</button>
                </div>
            </div>
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
                    <div class="col-md-2"><div class="result-card"><div class="result-num" style="font-size:1.2rem"><?= $r['seller_rank'] ?></div><div class="result-label"><?= $r['seller_rate'] ?>% Direct Rate</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($r['global_cap']) ?></div><div class="result-label">Global Cap (<?= $result['plan']['name'] ?? '' ?>)</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($r['track_a_total']) ?></div><div class="result-label">Track A Total</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num">₹<?= number_format($r['total_distributed']) ?></div><div class="result-label">Total Distributed</div></div></div>
                    <div class="col-md-2"><div class="result-card"><div class="result-num"><?= $r['payout_ratio'] ?>%</div><div class="result-label">Payout Ratio</div></div></div>
                </div>

                <div style="background:#141829;border:1px solid #2a2f4a;border-radius:10px;padding:16px;margin-bottom:1.5rem">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                        <span style="font-size:.82rem;color:#8892b0">Cap Utilization: <?= number_format($pctUsed, 1) ?>%</span>
                        <span style="font-size:.82rem;color:#8892b0">Remaining: ₹<?= number_format($r['remaining_cap']) ?></span>
                    </div>
                    <div class="track-bar" style="height:12px">
                        <div class="track-bar-fill" style="width:<?= min(100, $pctUsed) ?>%;background:linear-gradient(90deg,<?= $pctUsed > 80 ? '#ef4444,#f59e0b' : '#4f8cff,#22c55e' ?>)"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <h6 style="color:#a855f7;margin-bottom:1rem">Track A: Slab Differential Breakdown</h6>
                        <div style="overflow-x:auto">
                            <table class="table sim-table m-0">
                                <thead><tr><th>Recipient</th><th>Type</th><th>Rate</th><th>Amount</th><th>% of Sale</th></tr></thead>
                                <tbody>
                                    <?php foreach ($r['track_a_entries'] as $e): ?>
                                    <tr>
                                        <td style="font-weight:600"><?= htmlspecialchars($e['label']) ?></td>
                                        <td><span style="padding:2px 8px;border-radius:4px;font-size:.7rem;background:<?= $e['type'] === 'direct_sale' ? '#4f8cff22;color:#4f8cff' : ($e['type'] === 'override' ? '#f59e0b22;color:#f59e0b' : '#22c55e22;color:#22c55e') ?>"><?= $e['type'] ?></span></td>
                                        <td><?= $e['rate'] ?>%</td>
                                        <td style="font-weight:600">₹<?= number_format($e['amount']) ?></td>
                                        <td style="color:#8892b0"><?= $r['sale_amount'] > 0 ? number_format(($e['amount'] / $r['sale_amount']) * 100, 2) : 0 ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr style="border-top:2px solid #4f8cff44"><td colspan="3" style="font-weight:700;color:#4f8cff">Track A Total</td><td style="font-weight:700;color:#4f8cff">₹<?= number_format($r['track_a_total']) ?></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 style="color:#22c55e;margin-bottom:1rem">Tracks B + C</h6>
                        <?php foreach ($r['track_b_entries'] as $e): ?>
                        <div style="background:#141829;border:1px solid #2a2f4a;border-radius:8px;padding:12px;margin-bottom:8px">
                            <div style="font-size:.75rem;color:#8892b0"><?= htmlspecialchars($e['label']) ?></div>
                            <div style="font-size:1.1rem;font-weight:700;color:#22c55e">₹<?= number_format($e['amount']) ?></div>
                        </div>
                        <?php endforeach; ?>
                        <?php foreach ($r['track_c_entries'] as $e): ?>
                        <div style="background:#141829;border:1px solid #2a2f4a;border-radius:8px;padding:12px;margin-bottom:8px">
                            <div style="font-size:.75rem;color:#8892b0"><?= htmlspecialchars($e['label']) ?></div>
                            <div style="font-size:1.1rem;font-weight:700;color:#a855f7">₹<?= number_format($e['amount']) ?></div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (!empty($r['monthly_bonuses'])): ?>
                        <h6 style="color:#f59e0b;margin:1rem 0 .5rem">Monthly Bonuses (Estimated)</h6>
                        <?php foreach ($r['monthly_bonuses'] as $bName => $b): ?>
                        <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.82rem;border-bottom:1px solid #1e2340">
                            <span style="color:#8892b0"><?= ucfirst($bName) ?> (<?= $b['rate'] ?>%)</span>
                            <span style="color:#f59e0b;font-weight:600">₹<?= number_format($b['estimated']) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($simMode === 'bulk'): ?>
                <h6 style="color:#a855f7;margin-bottom:1rem">All Ranks at ₹<?= number_format($_POST['sale_amount'] ?? 1500000) ?> — <?= htmlspecialchars($result['plan']['name'] ?? '') ?></h6>
                <div style="overflow-x:auto">
                    <table class="table sim-table m-0">
                        <thead><tr><th>Rank</th><th>Direct Rate</th><th>Track A</th><th>Track B</th><th>Track C</th><th>Total Payout</th><th>Payout %</th></tr></thead>
                        <tbody>
                            <?php foreach ($result['rank_results'] as $rr): ?>
                            <?php if ($rr['success']): ?>
                            <tr>
                                <td style="font-weight:600"><?= htmlspecialchars($rr['seller_rank']) ?></td>
                                <td><?= $rr['seller_rate'] ?>%</td>
                                <td>₹<?= number_format($rr['track_a_total']) ?></td>
                                <td>₹<?= number_format($rr['track_b_total']) ?></td>
                                <td>₹<?= number_format($rr['track_c_total']) ?></td>
                                <td style="font-weight:700;color:#4f8cff">₹<?= number_format($rr['total_distributed']) ?></td>
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
                        <div style="background:#4f8cff15;border:1px solid #4f8cff33;border-radius:10px;padding:16px;text-align:center">
                            <div style="font-size:.75rem;color:#8892b0"><?= htmlspecialchars($simA['plan']['name'] ?? '') ?></div>
                            <div style="font-size:1.4rem;font-weight:700;color:#4f8cff">₹<?= number_format($simA['total_distributed']) ?></div>
                            <div style="font-size:.82rem;color:#8892b0"><?= $simA['payout_ratio'] ?>% payout ratio</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background:#a855f715;border:1px solid #a855f733;border-radius:10px;padding:16px;text-align:center">
                            <div style="font-size:.75rem;color:#8892b0"><?= htmlspecialchars($simB['plan']['name'] ?? '') ?></div>
                            <div style="font-size:1.4rem;font-weight:700;color:#a855f7">₹<?= number_format($simB['total_distributed']) ?></div>
                            <div style="font-size:.82rem;color:#8892b0"><?= $simB['payout_ratio'] ?>% payout ratio</div>
                        </div>
                    </div>
                </div>
                <?php
                $diffTotal = $simB['total_distributed'] - $simA['total_distributed'];
                $diffCap = $simB['global_cap'] - $simA['global_cap'];
                ?>
                <div class="result-card" style="margin-bottom:1.5rem">
                    <div style="font-size:.75rem;color:#8892b0;text-transform:uppercase">Difference (Plan B − Plan A)</div>
                    <div class="result-num" style="font-size:1.8rem">₹<?= number_format($diffTotal) ?></div>
                    <div style="font-size:.82rem;color:<?= $diffTotal > 0 ? '#22c55e' : ($diffTotal < 0 ? '#ef4444' : '#8892b0') ?>"><?= $diffTotal > 0 ? 'Plan B pays MORE' : ($diffTotal < 0 ? 'Plan A pays MORE' : 'Same payout') ?></div>
                </div>

            <?php endif; ?>
        <?php elseif ($result && !($result['success'] ?? false)): ?>
            <div style="background:#ef444415;border:1px solid #ef444433;border-radius:10px;padding:16px;color:#ef4444">
                <i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($result['error'] ?? 'Simulation failed') ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:3rem;color:#8892b0">
                <i class="fas fa-flask" style="font-size:2.5rem;margin-bottom:1rem;display:block;opacity:.3"></i>
                Configure parameters above and click Simulate to run what-if analysis.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function setMode(mode) {
    document.querySelectorAll('.mode-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    document.querySelector('input[name="sim_mode"]').value = mode;
    document.getElementById('simForm').submit();
}
</script>
