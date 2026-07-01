<?php
/** @var array $payouts */
$payouts = $payouts ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$statusBadge = function ($s) {
    $map = [
        'pending'    => 'bg-warning text-dark',
        'processing' => 'bg-info',
        'paid'       => 'bg-success',
        'failed'     => 'bg-danger',
        'on_hold'    => 'bg-secondary',
        'cancelled'  => 'bg-secondary',
    ];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-money-bill-wave me-2"></i>Payouts</h5>
        <a href="<?= htmlspecialchars($base) ?>/admin/mlm/payouts/batches" class="btn btn-link btn-sm">View Batches</a>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive"><table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Batch</th>
                    <th>Associate</th>
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
                    <tr><td colspan="9" class="text-center text-muted py-3">No payouts yet</td></tr>
                <?php else: foreach ($payouts as $p): ?>
                    <tr>
                        <td><?= (int)($p['id'] ?? 0) ?></td>
                        <td><a href="<?= htmlspecialchars($base) ?>/admin/mlm/payouts/batches/<?= (int)($p['payout_batch_id'] ?? 0) ?>"><?= htmlspecialchars((string)($p['batch_number'] ?? '')) ?></a></td>
                        <td><?= htmlspecialchars((string)($p['associate_name'] ?? '#'.($p['associate_user_id'] ?? ''))) ?></td>
                        <td class="text-end">&#8377;<?= number_format((float)($p['gross_amount'] ?? 0), 2) ?></td>
                        <td class="text-end">&#8377;<?= number_format((float)($p['tds_amount'] ?? 0), 2) ?></td>
                        <td class="text-end"><strong>&#8377;<?= number_format((float)($p['net_amount'] ?? 0), 2) ?></strong></td>
                        <td><span class="badge <?= $statusBadge($p['status'] ?? '') ?>"><?= htmlspecialchars((string)($p['status'] ?? '')) ?></span></td>
                        <td><?= htmlspecialchars((string)($p['paid_date'] ?? '—')) ?></td>
                        <td>
                            <?php if (in_array($p['status'] ?? '', ['pending','processing'], true)): ?>
                                <a class="btn btn-sm btn-outline-success" href="<?= htmlspecialchars($base) ?>/admin/mlm/payouts/<?= (int)($p['id'] ?? 0) ?>/mark-paid">
                                    <i class="fas fa-check"></i> Mark Paid
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table></div>
    </div>
</div>
