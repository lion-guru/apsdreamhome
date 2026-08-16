<?php
$plans = $plans ?? [];
$comparison = $comparison ?? null;
$planIdA = $planIdA ?? 0;
$planIdB = $planIdB ?? 0;
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
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
.compare-table th{background:#1e2340;color:#8892b0;font-size:.72rem;text-transform:uppercase;padding:10px 14px;border:none}
.compare-table td{padding:10px 14px;border-top:1px solid #1e2340;color:#e0e0e0;font-size:.85rem}
.compare-table .plan-a{color:#4f8cff}
.compare-table .plan-b{color:#a855f7}
.diff-pos{color:#22c55e;font-weight:600}
.diff-neg{color:#ef4444;font-weight:600}
.diff-zero{color:#8892b0}
.cp-version{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:600;background:#1e2340;color:#a855f7;border:1px solid #a855f733}
</style>

<div class="cp-card">
    <div class="cp-card-header">
        <h5 class="m-0" class="style-43926"><i class="fas fa-columns me-2" class="style-13856"></i>Compare Commission Plans</h5>
        <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="cp-card-body">
        <form method="GET" class="mb-4">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="style-47305">Plan A</label>
                    <select name="plan_a" class="cp-input">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $planIdA == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['plan_name']) ?> v<?= $p['version'] ?> (<?= $p['plan_code'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="style-47305">Plan B</label>
                    <select name="plan_b" class="cp-input">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $planIdB == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['plan_name']) ?> v<?= $p['version'] ?> (<?= $p['plan_code'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="cp-btn cp-btn-primary" class="style-90537"><i class="fas fa-balance-scale me-1"></i>Compare</button>
                </div>
            </div>
        </form>

        <?php if ($comparison): ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="style-731">
                        <div class="style-34740">Plan A</div>
                        <div class="style-69154"><?= htmlspecialchars($comparison['plan_a']['name']) ?></div>
                        <div class="cp-version" class="style-62298">v<?= $comparison['plan_a']['version'] ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="style-71182">
                        <div class="style-34740">Plan B</div>
                        <div class="style-62989"><?= htmlspecialchars($comparison['plan_b']['name']) ?></div>
                        <div class="cp-version" class="style-62298">v<?= $comparison['plan_b']['version'] ?></div>
                    </div>
                </div>
            </div>

            <h6 class="style-36277">Global Parameters</h6>
            <div class="row mb-4">
                <?php foreach (['global' => 'Global Cap', 'track_a' => 'Track A', 'track_b' => 'Track B', 'track_c' => 'Track C', 'royalty' => 'Royalty Pool'] as $key => $label): ?>
                    <?php
                    $valA = $comparison['plan_a']['caps'][$key] ?? 0;
                    $valB = $comparison['plan_b']['caps'][$key] ?? 0;
                    $diff = $valB - $valA;
                    ?>
                    <div class="col-md-2">
                        <div class="style-15110">
                            <div class="style-76820"><?= $label ?></div>
                            <div class="plan-a" class="style-15627"><?= $valA ?>%</div>
                            <div class="style-54323">vs</div>
                            <div class="plan-b" class="style-15627"><?= $valB ?>%</div>
                            <div class="<?= $diff > 0 ? 'diff-pos' : ($diff < 0 ? 'diff-neg' : 'diff-zero') ?>" class="style-40481">
                                <?= $diff > 0 ? '+' : '' ?><?= $diff ?>%
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h6 class="style-36277">Rank-by-Rank Comparison</h6>
            <div class="style-50496">
                <table class="table compare-table m-0">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th class="plan-a">Direct % (A)</th>
                            <th class="plan-b">Direct % (B)</th>
                            <th>Î”</th>
                            <th class="plan-a">Team % (A)</th>
                            <th class="plan-b">Team % (B)</th>
                            <th>Î”</th>
                            <th class="plan-a">Level % (A)</th>
                            <th class="plan-b">Level % (B)</th>
                            <th>Î”</th>
                            <th class="plan-a">Target (A)</th>
                            <th class="plan-b">Target (B)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comparison['levels'] as $lv): ?>
                            <?php
                            $dd = $lv['direct_b'] - $lv['direct_a'];
                            $td = $lv['team_b'] - $lv['team_a'];
                            $ld = $lv['level_b'] - $lv['level_a'];
                            ?>
                            <tr>
                                <td class="style-35725"><?= htmlspecialchars($lv['name_a']) ?> / <?= htmlspecialchars($lv['name_b']) ?></td>
                                <td class="plan-a"><?= $lv['direct_a'] ?>%</td>
                                <td class="plan-b"><?= $lv['direct_b'] ?>%</td>
                                <td class="<?= $dd > 0 ? 'diff-pos' : ($dd < 0 ? 'diff-neg' : 'diff-zero') ?>"><?= $dd > 0 ? '+' : '' ?><?= $dd ?>%</td>
                                <td class="plan-a"><?= $lv['team_a'] ?>%</td>
                                <td class="plan-b"><?= $lv['team_b'] ?>%</td>
                                <td class="<?= $td > 0 ? 'diff-pos' : ($td < 0 ? 'diff-neg' : 'diff-zero') ?>"><?= $td > 0 ? '+' : '' ?><?= $td ?>%</td>
                                <td class="plan-a"><?= $lv['level_a'] ?>%</td>
                                <td class="plan-b"><?= $lv['level_b'] ?>%</td>
                                <td class="<?= $ld > 0 ? 'diff-pos' : ($ld < 0 ? 'diff-neg' : 'diff-zero') ?>"><?= $ld > 0 ? '+' : '' ?><?= $ld ?>%</td>
                                <td class="plan-a">₹<?= number_format($lv['target_a']) ?></td>
                                <td class="plan-b">₹<?= number_format($lv['target_b']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="style-52260">
                <i class="fas fa-columns" class="style-86717"></i>
                Select two plans and click Compare to see side-by-side differences.
            </div>
        <?php endif; ?>
    </div>
</div>
