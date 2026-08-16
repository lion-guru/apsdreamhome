<?php
/** @var array $batches */
$batches = $batches ?? [];
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
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-money-check-alt me-2"></i>Payout Batches</h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/batches/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New Batch
        </a>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive"><table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Batch Number</th>
                    <th>Period</th>
                    <th>Associates</th>
                    <th class="text-end">Gross</th>
                    <th class="text-end">TDS (5%)</th>
                    <th class="text-end">Net</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">
                        No payout batches yet — <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/batches/create">create the first one</a>.
                    </td></tr>
                <?php else: foreach ($batches as $b): ?>
                    <tr>
                        <td><?= (int)($b['id'] ?? 0) ?></td>
                        <td><strong><?= htmlspecialchars((string)($b['batch_number'] ?? '')) ?></strong></td>
                        <td>
                            <?= htmlspecialchars((string)($b['period_year'] ?? '')) ?>-
                            <?= str_pad((string)($b['period_month'] ?? ''), 2, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td><?= (int)($b['total_associates'] ?? 0) ?></td>
                        <td class="text-end">&#8377;<?= number_format((float)($b['total_gross_amount'] ?? 0), 2) ?></td>
                        <td class="text-end">&#8377;<?= number_format((float)($b['total_tds_amount'] ?? 0), 2) ?></td>
                        <td class="text-end">&#8377;<?= number_format((float)($b['total_net_amount'] ?? 0), 2) ?></td>
                        <td><span class="badge <?= $statusBadge($b['status'] ?? '') ?>"><?= htmlspecialchars((string)($b['status'] ?? '')) ?></span></td>
                        <td><?= htmlspecialchars((string)($b['created_at'] ?? '')) ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/batches/<?= (int)($b['id'] ?? 0) ?>">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table></div>
    </div>
</div>
