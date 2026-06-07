<?php
/** @var array $refunds */
/** @var array $summary */
$refunds = $refunds ?? [];
$summary = $summary ?? ['total' => 0.0, 'pending' => 0, 'processed' => 0];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-3">
    <div class="aps-cp-card-header"><h5 class="m-0"><i class="fas fa-undo me-2"></i>Booking Refunds</h5></div>
    <div class="aps-cp-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="aps-cp-stat bg-info text-white">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)$summary['total']) ?></div>
                    <div class="aps-cp-stat-label">Total Refundable</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="aps-cp-stat bg-warning text-dark">
                    <div class="aps-cp-stat-value"><?= (int)$summary['pending'] ?></div>
                    <div class="aps-cp-stat-label">Pending</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="aps-cp-stat bg-success text-white">
                    <div class="aps-cp-stat-value"><?= (int)$summary['processed'] ?></div>
                    <div class="aps-cp-stat-label">Processed / Approved</div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Charge</th>
                        <th class="text-end">Refund</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($refunds)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No refunds yet</td></tr>
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
