<?php
/** @var array|null $batch */
/** @var array $payouts */
$batch = $batch ?? null;
$payouts = $payouts ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$statusBadge = function ($s) {
    $map = [
        'draft'             => 'bg-secondary',
        'pending_approval'  => 'bg-warning text-dark',
        'approved'          => 'bg-info',
        'processing'        => 'bg-primary',
        'completed'         => 'bg-success',
        'cancelled'         => 'bg-danger',
    ];
    return $map[$s] ?? 'bg-secondary';
};
$payoutStatusBadge = function ($s) {
    $map = [
        'pending'   => 'bg-warning text-dark',
        'processing'=> 'bg-info',
        'paid'      => 'bg-success',
        'failed'    => 'bg-danger',
        'on_hold'   => 'bg-secondary',
        'cancelled' => 'bg-secondary',
    ];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0">
            <i class="fas fa-money-check-alt me-2"></i>Payout Batch
            <?php if ($batch): ?>
                <span class="badge bg-secondary ms-2"><?= htmlspecialchars((string)($batch['batch_number'] ?? '')) ?></span>
            <?php endif; ?>
        </h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/batches" class="btn btn-link btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card-body">
        <?php if (!$batch): ?>
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>Batch not found.</div>
        <?php else: ?>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="aps-cp-stat bg-primary text-white">
                        <div class="aps-cp-stat-value"><?= (int)($batch['total_associates'] ?? 0) ?></div>
                        <div class="aps-cp-stat-label">Associates</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="aps-cp-stat bg-info text-white">
                        <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($batch['total_gross_amount'] ?? 0) / 1000, 1) ?>K</div>
                        <div class="aps-cp-stat-label">Gross</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="aps-cp-stat bg-warning text-dark">
                        <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($batch['total_tds_amount'] ?? 0) / 1000, 1) ?>K</div>
                        <div class="aps-cp-stat-label">TDS</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="aps-cp-stat bg-success text-white">
                        <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($batch['total_net_amount'] ?? 0) / 1000, 1) ?>K</div>
                        <div class="aps-cp-stat-label">Net</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="me-3">Status: <span class="badge <?= $statusBadge($batch['status'] ?? '') ?>"><?= htmlspecialchars((string)($batch['status'] ?? '')) ?></span></span>
                    <span class="me-3 text-muted small">Period: <?= htmlspecialchars((string)($batch['period_year'] ?? '')) ?>-<?= str_pad((string)($batch['period_month'] ?? ''), 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="me-3 text-muted small">Created: <?= htmlspecialchars((string)($batch['created_at'] ?? '')) ?></span>
                </div>
                <?php if (in_array($batch['status'], ['draft', 'pending_approval'], true)): ?>
                    <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/batches/<?= (int)($batch['id'] ?? 0) ?>/approve">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <button class="btn btn-success btn-sm" type="submit">
                            <i class="fas fa-check me-1"></i>Approve Batch
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover m-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Associate</th>
                            <th>Rank</th>
                            <th class="text-end">Gross</th>
                            <th class="text-end">TDS</th>
                            <th class="text-end">Net</th>
                            <th>Status</th>
                            <th>Paid</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payouts)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-3">No payouts in this batch</td></tr>
                        <?php else: foreach ($payouts as $p): ?>
                            <tr>
                                <td><?= (int)($p['id'] ?? 0) ?></td>
                                <td><?= htmlspecialchars((string)($p['associate_name'] ?? '#'.($p['associate_user_id'] ?? ''))) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars((string)($p['associate_rank'] ?? '—')) ?></span></td>
                                <td class="text-end">&#8377;<?= number_format((float)($p['gross_amount'] ?? 0), 2) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($p['tds_amount'] ?? 0), 2) ?></td>
                                <td class="text-end"><strong>&#8377;<?= number_format((float)($p['net_amount'] ?? 0), 2) ?></strong></td>
                                <td><span class="badge <?= $payoutStatusBadge($p['status'] ?? '') ?>"><?= htmlspecialchars((string)($p['status'] ?? '')) ?></span></td>
                                <td><?= htmlspecialchars((string)($p['paid_date'] ?? '—')) ?></td>
                                <td>
                                    <?php if (in_array($p['status'] ?? '', ['pending', 'processing'], true) && in_array($batch['status'], ['approved', 'processing', 'completed'], true)): ?>
                                        <a class="btn btn-sm btn-outline-success" href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/<?= (int)($p['id'] ?? 0) ?>/mark-paid">
                                            <i class="fas fa-check"></i> Mark Paid
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
