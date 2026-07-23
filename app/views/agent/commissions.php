<?php
$commissions = $commissions ?? [];
$total_earned = $total_earned ?? 0;
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color:#15803d;font-weight:700;"><i class="fas fa-coins me-2"></i>My Commissions</h4>
        <p class="text-muted mb-0">Track your earnings and commission history</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div style="width:56px;height:56px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <i class="fas fa-wallet fa-lg" style="color:#15803d;"></i>
                </div>
                <h3 style="color:#15803d;font-weight:700;">₹<?= number_format($total_earned) ?></h3>
                <p class="text-muted mb-0">Total Earned</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div style="width:56px;height:56px;border-radius:50%;background:#dbeafe;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <i class="fas fa-file-invoice fa-lg" style="color:#2563eb;"></i>
                </div>
                <h3 style="color:#2563eb;font-weight:700;"><?= count($commissions) ?></h3>
                <p class="text-muted mb-0">Total Entries</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div style="width:56px;height:56px;border-radius:50%;background:#fef3c7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <i class="fas fa-chart-line fa-lg" style="color:#d97706;"></i>
                </div>
                <h3 style="color:#d97706;font-weight:700;">₹<?= number_format($total_earned > 0 ? round($total_earned / max(count($commissions), 1)) : 0) ?></h3>
                <p class="text-muted mb-0">Avg per Entry</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($commissions)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:#fef3c7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="fas fa-hand-holding-usd fa-2x" style="color:#d97706;"></i>
        </div>
        <h5 class="text-muted">No commissions yet</h5>
        <p class="text-muted mb-0">Your commission history will appear here</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f0fdf4;">
                    <tr>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Type</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Amount</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Description</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commissions as $c): ?>
                    <tr>
                        <td class="px-3">
                            <?php
                            $type = $c['type'] ?? 'unknown';
                            $typeIcons = [
                                'direct_sale' => ['fas fa-tag', 'bg-success'],
                                'override' => ['fas fa-layer-group', 'bg-info'],
                                'level_bonus' => ['fas fa-level-up-alt', 'bg-primary'],
                                'generation_bonus' => ['fas fa-users', 'bg-purple'],
                                'matching_bonus' => ['fas fa-exchange-alt', 'bg-warning text-dark'],
                                'rank_bonus' => ['fas fa-trophy', 'bg-danger'],
                                'royalty_pool' => ['fas fa-crown', 'bg-dark'],
                            ];
                            $icon = $typeIcons[$type] ?? ['fas fa-question-circle', 'bg-secondary'];
                            ?>
                            <span class="badge <?= $icon[1] ?> me-1"><i class="<?= $icon[0] ?>"></i></span>
                            <?= ucfirst(str_replace('_', ' ', $type)) ?>
                        </td>
                        <td class="px-3 fw-bold" style="color:#15803d;">₹<?= number_format($c['amount'] ?? 0) ?></td>
                        <td class="px-3"><small class="text-muted"><?= htmlspecialchars($c['description'] ?? '-') ?></small></td>
                        <td class="px-3"><small class="text-muted"><?= date('d M Y', strtotime($c['created_at'] ?? 'now')) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
