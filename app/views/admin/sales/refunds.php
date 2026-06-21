<?php
/** @var array $refunds */
/** @var array $summary */
$refunds = $refunds ?? [];
$summary = $summary ?? ['total' => 0.0, 'pending' => 0, 'processed' => 0];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-3">
    <div class="aps-cp-card-header"><h5 class="m-0"><i class="fas fa-undo me-2"></i><?= __('sale_booking_refunds') ?></h5></div>
    <div class="aps-cp-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="aps-cp-stat bg-info text-white">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)$summary['total']) ?></div>
                    <div class="aps-cp-stat-label"><?= __('sale_total_refundable') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="aps-cp-stat bg-warning text-dark">
                    <div class="aps-cp-stat-value"><?= (int)$summary['pending'] ?></div>
                    <div class="aps-cp-stat-label"><?= __('sale_pending_label') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="aps-cp-stat bg-success text-white">
                    <div class="aps-cp-stat-value"><?= (int)$summary['processed'] ?></div>
                    <div class="aps-cp-stat-label"><?= __('sale_processed_approved') ?></div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th><?= __('sale_booking_num') ?></th>
                        <th><?= __('sale_customer') ?></th>
                        <th><?= __('sale_reason') ?></th>
                        <th class="text-end"><?= __('sale_paid') ?></th>
                        <th class="text-end"><?= __('sale_charge') ?></th>
                        <th class="text-end"><?= __('sale_refund') ?></th>
                        <th><?= __('sale_status') ?></th>
                        <th><?= __('sale_created') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($refunds)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><?= __('sale_no_refunds') ?></td></tr>
                    <?php else: foreach ($refunds as $r):
                        $st = $r['status'] ?? 'pending';
                        $cls = ['processed'=>'success','approved'=>'success','paid'=>'success','pending'=>'warning','rejected'=>'danger'][$st] ?? 'secondary';
                    ?>
                        <tr>
                            <td>
                                <a href="<?= htmlspecialchars($base) ?>/admin/sales/bookings/<?= (int)($r['booking_id'] ?? 0) ?>">
                                    <?= htmlspecialchars((string)($r['booking_number'] ?? '')) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars((string)($r['customer_name'] ?? '—')) ?></td>
                            <td><?= htmlspecialchars((string)($r['reason'] ?? '')) ?></td>
                            <td class="text-end">&#8377;<?= number_format((float)($r['total_paid'] ?? 0)) ?></td>
                            <td class="text-end">&#8377;<?= number_format((float)($r['cancellation_charge'] ?? 0)) ?></td>
                            <td class="text-end text-danger">&#8377;<?= number_format((float)($r['refund_amount'] ?? 0)) ?></td>
                            <td><span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($st) ?></span></td>
                            <td><?= htmlspecialchars((string)($r['created_at'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
