<?php
/** @var array $commissions */
/** @var array $summary */
$commissions = $commissions ?? [];
$summary = $summary ?? ['total' => 0.0, 'pending' => 0.0, 'paid' => 0.0, 'count' => 0];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-3">
    <div class="aps-cp-card-header"><h5 class="m-0"><i class="fas fa-percent me-2"></i><?= __('sale_sales_commissions') ?></h5></div>
    <div class="aps-cp-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-primary text-white">
                    <div class="aps-cp-stat-value"><?= (int)($summary['count']) ?></div>
                    <div class="aps-cp-stat-label"><?= __('sale_records') ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-info text-white">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)$summary['total']) ?></div>
                    <div class="aps-cp-stat-label"><?= __('sale_total_label') ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-success text-white">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)$summary['paid']) ?></div>
                    <div class="aps-cp-stat-label"><?= __('sale_paid_approved') ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-warning text-dark">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)$summary['pending']) ?></div>
                    <div class="aps-cp-stat-label"><?= __('sale_pending_label') ?></div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th><?= __('sale_booking_num') ?></th>
                        <th><?= __('sale_beneficiary') ?></th>
                        <th><?= __('sale_type') ?></th>
                        <th><?= __('sale_lvl') ?></th>
                        <th class="text-end"><?= __('sale_pct') ?></th>
                        <th class="text-end"><?= __('sale_amount') ?></th>
                        <th><?= __('sale_status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($commissions)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4"><?= __('sale_no_commission_records') ?></td></tr>
                    <?php else: foreach ($commissions as $c):
                        $st = $c['status'] ?? 'pending';
                        $cls = ['paid' => 'success', 'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger'][$st] ?? 'secondary';
                    ?>
                        <tr>
                            <td>
                                <a href="<?= htmlspecialchars($base) ?>/admin/sales/bookings/<?= (int)($c['booking_id'] ?? 0) ?>">
                                    <?= htmlspecialchars((string)($c['booking_number'] ?? '')) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars((string)($c['beneficiary_name'] ?? '—')) ?></td>
                            <td><?= htmlspecialchars((string)($c['commission_type'] ?? '')) ?></td>
                            <td>L<?= (int)($c['level'] ?? 0) ?></td>
                            <td class="text-end"><?= number_format((float)($c['percentage'] ?? 0), 2) ?>%</td>
                            <td class="text-end">&#8377;<?= number_format((float)($c['amount'] ?? 0)) ?></td>
                            <td><span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($st) ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
