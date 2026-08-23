<?php
$plans = $plans ?? [];
$activePlan = $activePlan ?? null;
$stats = $stats ?? [];
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$statusBadge = fn($s) => match($s) {
    'active' => 'bg-success',
    'draft' => 'bg-warning text-dark',
    'inactive' => 'bg-secondary',
    default => 'bg-secondary'
};
?>
<style>
.cp-card{background:#1a1f36;border:1px solid #2a2f4a;border-radius:12px;color:#e0e0e0;margin-bottom:1.5rem;overflow:hidden}
.cp-card-header{background:linear-gradient(135deg,#141829,#1e2340);padding:1rem 1.5rem;border-bottom:1px solid #2a2f4a;display:flex;justify-content:space-between;align-items:center}
.cp-card-body{padding:1.5rem}
.cp-stat{text-align:center;padding:1rem;border-radius:10px;background:#141829;border:1px solid #2a2f4a}
.cp-stat .num{font-size:1.8rem;font-weight:700;background:linear-gradient(135deg,#4f8cff,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.cp-stat .lbl{font-size:.75rem;color:#8892b0;text-transform:uppercase;letter-spacing:.5px;margin-top:4px}
.cp-badge{padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:600}
.cp-version{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:600;background:#1e2340;color:#a855f7;border:1px solid #a855f733}
.table-dark-custom{background:#141829;border-radius:10px;overflow:hidden}
.table-dark-custom th{background:#1e2340;color:#8892b0;font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;padding:10px 14px;border:none}
.table-dark-custom td{padding:10px 14px;border-top:1px solid #1e2340;color:#e0e0e0;font-size:.85rem}
.table-dark-custom tr:hover td{background:#1e234040}
.btn-cp{padding:5px 12px;border-radius:8px;font-size:.78rem;font-weight:500;border:none;cursor:pointer;transition:all .2s}
.btn-cp-primary{background:linear-gradient(135deg,#4f8cff,#6366f1);color:#fff}
.btn-cp-primary:hover{transform:translateY(-1px);box-shadow:0 4px 15px #4f8cff44}
.btn-cp-outline{background:transparent;border:1px solid #4f8cff44;color:#4f8cff}
.btn-cp-outline:hover{background:#4f8cff15}
.btn-cp-success{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff}
.btn-cp-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff}
.btn-cp-warning{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff}
.cp-action-bar{display:flex;gap:6px;flex-wrap:wrap}
.cap-bar{height:6px;border-radius:3px;background:#1e2340;overflow:hidden;margin-top:4px}
.cap-bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#4f8cff,#a855f7)}
</style>

<div class="cp-card">
    <div class="cp-card-header">
        <h5 class="m-0 style-43926"><i class="fas fa-file-invoice-dollar me-2 style-20955"></i>Commission Plan Manager</h5>
        <div>
            <a href="<?= $base ?>/admin/commission-plans/simulator" class="btn-cp btn-cp-outline me-2"><i class="fas fa-flask me-1"></i>Simulator</a>
            <a href="<?= $base ?>/admin/commission-plans/compare" class="btn-cp btn-cp-outline me-2"><i class="fas fa-columns me-1"></i>Compare</a>
            <a href="<?= $base ?>/admin/commission-plans/history" class="btn-cp btn-cp-outline me-2"><i class="fas fa-history me-1"></i>History</a>
            <a href="<?= $base ?>/admin/commission-plans/create" class="btn-cp btn-cp-primary"><i class="fas fa-plus me-1"></i>New Plan</a>
        </div>
    </div>
    <div class="cp-card-body">
        <?php if ($activePlan): ?>
            <div class="style-30392">
                <i class="fas fa-check-circle style-56297"></i>
                <div>
                    <strong class="style-43926">Active Plan:</strong>
                    <span class="style-55803"><?= htmlspecialchars($activePlan['plan_name'] ?? '') ?></span>
                    <span class="cp-version">v<?= $activePlan['version'] ?></span>
                    <span class="cp-badge bg-success ms-2"><?= htmlspecialchars($activePlan['plan_code'] ?? '') ?></span>
                    <span class="style-72550">
                        Global Cap: <?= $activePlan['global_cap_pct'] ?>% |
                        Track A: <?= $activePlan['track_a_pct'] ?>% |
                        Track B: <?= $activePlan['track_b_pct'] ?>% |
                        Track C: <?= $activePlan['track_c_pct'] ?>%
                    </span>
                </div>
            </div>
        <?php else: ?>
            <div class="style-29735">
                <i class="fas fa-exclamation-triangle style-57730"></i>
                <span class="style-62735">No active commission plan. Activate one from the list below.</span>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-2"><div class="cp-stat"><div class="num"><?= $stats['total'] ?? 0 ?></div><div class="lbl">Total Plans</div></div></div>
            <div class="col-md-2"><div class="cp-stat"><div class="num"><?= $stats['active'] ?? 0 ?></div><div class="lbl">Active</div></div></div>
            <div class="col-md-2"><div class="cp-stat"><div class="num"><?= $stats['draft'] ?? 0 ?></div><div class="lbl">Drafts</div></div></div>
            <div class="col-md-2"><div class="cp-stat"><div class="num"><?= $stats['maxVersion'] ?? 0 ?></div><div class="lbl">Max Version</div></div></div>
            <div class="col-md-2"><div class="cp-stat"><div class="num"><?= $stats['totalLevels'] ?? 0 ?></div><div class="lbl">Total Levels</div></div></div>
            <div class="col-md-2"><div class="cp-stat"><div class="num"><?= $stats['totalAudits'] ?? 0 ?></div><div class="lbl">Audit Entries</div></div></div>
        </div>

        <div class="table-dark-custom">
            <table class="table table-dark-custom m-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plan Name</th>
                        <th>Code</th>
                        <th>Version</th>
                        <th>Type</th>
                        <th>Levels</th>
                        <th>Global Cap</th>
                        <th>Track A/B/C</th>
                        <th>Status</th>
                        <th>Effective</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($plans)): ?>
                        <tr><td colspan="11" class="text-center style-10572">No plans found. Create your first plan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($plans as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($p['plan_name'] ?? '') ?></strong></td>
                                <td><code class="style-13856"><?= htmlspecialchars($p['plan_code'] ?? '') ?></code></td>
                                <td><span class="cp-version">v<?= $p['version'] ?></span></td>
                                <td><?= ucfirst(htmlspecialchars($p['plan_type'] ?? '')) ?></td>
                                <td><span class="cp-badge bg-primary"><?= (int)($p['level_count'] ?? 0) ?></span></td>
                                <td>
                                    <strong><?= $p['global_cap_pct'] ?>%</strong>
                                    <div class="cap-bar"><div class="cap-bar-fill style-30453"></div></div>
                                </td>
                                <td class="style-20996">
                                    <?= $p['track_a_pct'] ?> / <?= $p['track_b_pct'] ?> / <?= $p['track_c_pct'] ?>%
                                </td>
                                <td><span class="cp-badge <?= $statusBadge($p['status']) ?>"><?= ucfirst($p['status']) ?></span></td>
                                <td class="style-76409"><?= $p['effective_date'] ?? '—' ?></td>
                                <td>
                                    <div class="cp-action-bar justify-content-end">
                                        <a href="<?= $base ?>/admin/commission-plans/edit/<?= $p['id'] ?>" class="btn-cp btn-cp-outline" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php if ($p['status'] !== 'active'): ?>
                                            <form method="POST" action="<?= $base ?>/admin/commission-plans/activate/<?= $p['id'] ?>" class="style-71727">
                                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                <button type="submit" class="btn-cp btn-cp-success" title="Activate" aria-label="Activate plan"><i class="fas fa-power-off"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= $base ?>/admin/commission-plans/deactivate/<?= $p['id'] ?>" class="style-71727">
                                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                <button type="submit" class="btn-cp btn-cp-warning" title="Deactivate" aria-label="Pause"><i class="fas fa-pause"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($p['status'] !== 'active'): ?>
                                            <form method="POST" action="<?= $base ?>/admin/commission-plans/delete/<?= $p['id'] ?>" class="style-71727" data-aps-confirm="Delete this plan?">
                                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                <button type="submit" class="btn-cp btn-cp-danger" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
