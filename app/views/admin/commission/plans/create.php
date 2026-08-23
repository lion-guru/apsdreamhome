<?php
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$ranks = [
    ['Associate', 1, 5.00, 2.00, 0.00, 0.00, 0.00, 0.00, 1000000],
    ['Sr. Associate', 2, 7.00, 3.00, 2.00, 5.00, 0.00, 0.00, 3500000],
    ['BDM', 3, 10.00, 4.00, 3.00, 8.00, 1.00, 0.00, 7000000],
    ['Sr. BDM', 4, 12.00, 5.00, 4.00, 10.00, 2.00, 1.00, 15000000],
    ['Vice President', 5, 15.00, 6.00, 5.00, 12.00, 3.00, 2.00, 30000000],
    ['President', 6, 18.00, 7.00, 6.00, 15.00, 4.00, 3.00, 50000000],
    ['Site Manager', 7, 20.00, 8.00, 7.00, 18.00, 5.00, 5.00, 999999999],
];
?>
<style>
.cp-card{background:#1a1f36;border:1px solid #2a2f4a;border-radius:12px;color:#e0e0e0;margin-bottom:1.5rem}
.cp-card-header{background:linear-gradient(135deg,#141829,#1e2340);padding:1rem 1.5rem;border-bottom:1px solid #2a2f4a;display:flex;justify-content:space-between;align-items:center}
.cp-card-body{padding:1.5rem}
.cp-input{background:#0f1225;border:1px solid #2a2f4a;border-radius:8px;color:#e0e0e0;padding:8px 12px;width:100%;font-size:.85rem}
.cp-input:focus{border-color:#4f8cff;outline:none;box-shadow:0 0 0 2px #4f8cff33}
.cp-label{color:#8892b0;font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;display:block}
.cp-btn{padding:8px 20px;border-radius:8px;font-size:.85rem;font-weight:500;border:none;cursor:pointer;transition:all .2s}
.cp-btn-primary{background:linear-gradient(135deg,#4f8cff,#6366f1);color:#fff}
.cp-btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 15px #4f8cff44}
.cp-btn-outline{background:transparent;border:1px solid #4f8cff44;color:#4f8cff;text-decoration:none;display:inline-block}
.rank-table th{background:#1e2340;color:#8892b0;font-size:.72rem;text-transform:uppercase;padding:8px 10px;border:none}
.rank-table td{padding:8px 10px;border-top:1px solid #1e2340}
.rank-table input{background:#0f1225;border:1px solid #2a2f4a;border-radius:6px;color:#e0e0e0;padding:6px 8px;width:100%;font-size:.82rem;text-align:right}
.rank-table input:focus{border-color:#4f8cff;outline:none}
</style>

<div class="cp-card">
    <div class="cp-card-header">
        <h5 class="m-0 style-43926"><i class="fas fa-plus-circle me-2 style-13856"></i>Create Commission Plan</h5>
        <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="cp-card-body">
        <form method="POST" action="<?= $base ?>/admin/commission-plans/store">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="cp-label">Plan Name *</label>
                    <input type="text" name="plan_name" class="cp-input" required placeholder="e.g. Standard Commission Plan">
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Plan Code *</label>
                    <input type="text" name="plan_code" class="cp-input" required placeholder="e.g. STD" class="style-36130" maxlength="20">
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Plan Type</label>
                    <select name="plan_type" class="cp-input">
                        <option value="hybrid">Hybrid</option>
                        <option value="binary">Binary</option>
                        <option value="unilevel">Unilevel</option>
                        <option value="matrix">Matrix</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Effective Date</label>
                    <input type="date" name="effective_date" class="cp-input" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="cp-label">Description</label>
                    <input type="text" name="description" class="cp-input" placeholder="Optional description">
                </div>
            </div>

            <h6 class="style-36277"><i class="fas fa-cog me-1"></i>Global Commission Parameters</h6>
            <div class="row mb-4">
                <div class="col-md-2">
                    <label class="cp-label">Global Cap %</label>
                    <input type="number" name="global_cap_pct" class="cp-input" value="20" step="0.5" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Track A (Slab Diff) %</label>
                    <input type="number" name="track_a_pct" class="cp-input" value="15" step="0.5" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Track B (Rollup) %</label>
                    <input type="number" name="track_b_pct" class="cp-input" value="3" step="0.5" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Track C (Milestone) %</label>
                    <input type="number" name="track_c_pct" class="cp-input" value="2" step="0.5" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="cp-label">Royalty Pool %</label>
                    <input type="number" name="royalty_pool_pct" class="cp-input" value="2" step="0.5" min="0" max="100">
                </div>
                <div class="col-md-1">
                    <label class="cp-label">Override G1 %</label>
                    <input type="number" name="same_level_override_gen1" class="cp-input" value="2" step="0.5" min="0">
                </div>
                <div class="col-md-1">
                    <label class="cp-label">Override G2 %</label>
                    <input type="number" name="same_level_override_gen2" class="cp-input" value="1" step="0.5" min="0">
                </div>
            </div>

            <h6 class="style-36277"><i class="fas fa-layer-group me-1"></i>Rank Levels (Default 7)</h6>
            <div class="style-15107">
                <table class="table rank-table m-0">
                    <thead>
                        <tr>
                            <th>#</th><th>Rank Name</th><th>Direct %</th><th>Team %</th>
                            <th>Level Bonus %</th><th>Matching %</th><th>Leadership %</th>
                            <th>Performance %</th><th>GBV Threshold (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranks as $r): ?>
                        <tr>
                            <td class="style-53581"><?= $r[1] ?></td>
                            <td class="style-93158"><?= $r[0] ?></td>
                            <td><input type="number" value="<?= $r[2] ?>" step="0.5" min="0" max="100"></td>
                            <td><input type="number" value="<?= $r[3] ?>" step="0.5" min="0" max="100"></td>
                            <td><input type="number" value="<?= $r[4] ?>" step="0.5" min="0" max="100"></td>
                            <td><input type="number" value="<?= $r[5] ?>" step="0.5" min="0" max="100"></td>
                            <td><input type="number" value="<?= $r[6] ?>" step="0.5" min="0" max="100"></td>
                            <td><input type="number" value="<?= $r[7] ?>" step="0.5" min="0" max="100"></td>
                            <td><input type="number" value="<?= $r[8] ?>" step="100000" min="0" class="style-19004"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="cp-btn cp-btn-primary"><i class="fas fa-save me-1"></i>Create Plan</button>
                <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
