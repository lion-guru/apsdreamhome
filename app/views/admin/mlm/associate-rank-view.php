<?php
/** @var array|null $associate */
/** @var array $rankInfo */
/** @var array $history */
/** @var array $payouts */
/** @var array $clawbackLog */
/** @var array $rankBenefits */
$associate = $associate ?? null;
$rankInfo = $rankInfo ?? [];
$history = $history ?? [];
$payouts = $payouts ?? [];
$clawbackLog = $clawbackLog ?? [];
$rankBenefits = $rankBenefits ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0">
            <i class="fas fa-user-tie me-2"></i>
            <?= htmlspecialchars((string)($associate['name'] ?? 'Associate #' . ($associate['id'] ?? ''))) ?>
            <small class="text-muted ms-2">UID <?= (int)($associate['user_id'] ?? 0) ?></small>
        </h5>
        <div>
            <a href="<?= htmlspecialchars($base) ?>/admin/mlm/associate-ranks" class="btn btn-link btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/mlm/associate-ranks/<?= (int)($associate['id'] ?? 0) ?>/promote" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-arrow-up me-1"></i>Manual Promote</button>
            </form>
        </div>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="aps-cp-stat bg-primary text-white">
                    <div class="aps-cp-stat-value"><?= ucfirst((string)($rankInfo['current_rank'] ?? 'associate')) ?></div>
                    <div class="aps-cp-stat-label">Current Rank</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="aps-cp-stat bg-info text-white">
                    <div class="aps-cp-stat-value"><?= ucfirst((string)($rankInfo['next_rank'] ?? '—')) ?></div>
                    <div class="aps-cp-stat-label">Next Rank</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="aps-cp-stat bg-success text-white">
                    <div class="aps-cp-stat-value"><?= (int)($rankInfo['leg_count'] ?? 0) / max(1, (int)($rankInfo['next_legs_req'] ?? 1)) * 100 > 100 ? '100' : number_format((float)($rankInfo['leg_count'] ?? 0)) ?></div>
                    <div class="aps-cp-stat-label">Legs (req <?= (int)($rankInfo['next_legs_req'] ?? 0) ?>)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="aps-cp-stat bg-warning text-dark">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($rankInfo['lifetime_sales'] ?? 0) / 1000, 0) ?>K</div>
                    <div class="aps-cp-stat-label">Lifetime Sales (req &#8377;<?= number_format((float)($rankInfo['next_vol_req'] ?? 0) / 1000, 0) ?>K)</div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small text-muted">Progress to <?= htmlspecialchars(ucfirst((string)($rankInfo['next_rank'] ?? '—'))) ?></label>
            <div class="progress" class="style-67065">
                <div class="progress-bar bg-success" role="progressbar" class="style-28910">
                    <?= number_format((float)($rankInfo['progress_pct'] ?? 0), 1) ?>%
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" id="rankTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-history"><i class="fas fa-history me-1"></i>History</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payouts"><i class="fas fa-money-bill me-1"></i>Payouts</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-clawback"><i class="fas fa-undo me-1"></i>Clawback</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-history">
                <div class="table-responsive"><table class="table table-sm">
                    <thead>
                        <tr><th>Date</th><th>From</th><th>To</th><th class="text-end">Volume</th><th>Legs</th><th>Type</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No rank promotions yet</td></tr>
                        <?php else: foreach ($history as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($h['promoted_at'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(ucfirst((string)($h['from_rank'] ?? '—'))) ?></td>
                                <td><strong><?= htmlspecialchars(ucfirst((string)($h['to_rank'] ?? ''))) ?></strong></td>
                                <td class="text-end">&#8377;<?= number_format((float)($h['qualifying_volume_at_promotion'] ?? 0)) ?></td>
                                <td><?= (int)($h['leg_count_at_promotion'] ?? 0) ?></td>
                                <td><span class="badge bg-<?= (int)($h['is_manual'] ?? 0) === 1 ? 'primary' : 'secondary' ?>"><?= (int)($h['is_manual'] ?? 0) === 1 ? 'Manual' : 'Auto' ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div>

            <div class="tab-pane fade" id="tab-payouts">
                <div class="table-responsive"><table class="table table-sm">
                    <thead>
                        <tr><th>Batch</th><th>Period</th><th class="text-end">Gross</th><th class="text-end">TDS</th><th class="text-end">Net</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payouts)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No payouts yet</td></tr>
                        <?php else: foreach ($payouts as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($p['batch_number'] ?? '')) ?></td>
                                <td><?= (int)($p['period_year'] ?? 0) ?>-<?= str_pad((string)($p['period_month'] ?? ''), 2, '0', STR_PAD_LEFT) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($p['gross_amount'] ?? 0), 2) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($p['tds_amount'] ?? 0), 2) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($p['net_amount'] ?? 0), 2) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars((string)($p['status'] ?? '')) ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div>

            <div class="tab-pane fade" id="tab-clawback">
                <div class="table-responsive"><table class="table table-sm">
                    <thead>
                        <tr><th>Date</th><th>EMI Installment</th><th class="text-end">Original</th><th class="text-end">Clawback</th><th>Status</th><th>Recovered</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clawbackLog)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No clawbacks for this associate</td></tr>
                        <?php else: foreach ($clawbackLog as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($c['created_at'] ?? '')) ?></td>
                                <td>#<?= (int)($c['emi_installment_id'] ?? 0) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($c['original_amount'] ?? 0), 2) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($c['clawback_amount'] ?? 0), 2) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= htmlspecialchars((string)($c['status'] ?? '')) ?></span></td>
                                <td>&#8377;<?= number_format((float)($c['recovered_amount'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
