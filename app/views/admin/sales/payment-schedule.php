<?php
/** @var array $booking */
/** @var array $schedule */
$booking = $booking ?? [];
$schedule = $schedule ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$totalDue  = 0.0; $totalPaid = 0.0;
foreach ($schedule as $s) {
    $totalDue  += (float)($s['amount_due']  ?? 0);
    $totalPaid += (float)($s['amount_paid'] ?? 0);
}
?>
<div class="aps-cp-card mb-3">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0">
            <i class="fas fa-calendar-alt me-2"></i><?= __('sale_payment_schedule') ?> — <?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?>
        </h5>
        <div>
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-sm btn-link"><i class="fas fa-arrow-left me-1"></i><?= __('sale_back') ?></a>
        </div>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_total_due') ?></div>
                <div class="fw-bold">&#8377;<?= number_format($totalDue) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_total_paid') ?></div>
                <div class="fw-bold text-success">&#8377;<?= number_format($totalPaid) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_outstanding') ?></div>
                <div class="fw-bold text-danger">&#8377;<?= number_format(max(0, $totalDue - $totalPaid)) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_installments') ?></div>
                <div class="fw-bold"><?= count($schedule) ?></div>
            </div>
        </div>

        <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/schedule/regenerate" class="row g-2 mb-3 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <div class="col-md-3">
                <label class="form-label small"><?= __('sale_tenure_months') ?></label>
                <input type="number" name="tenure_months" value="12" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small"><?= __('sale_rate_pa') ?></label>
                <input type="number" step="0.01" name="rate_per_annum" value="10.0" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-warning" type="submit"
                        data-aps-confirm="<?= __('sale_regenerate_confirm') ?>">
                    <i class="fas fa-sync me-1"></i><?= __('sale_regenerate') ?>
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= __('sale_due_date') ?></th>
                        <th><?= __('sale_type') ?></th>
                        <th class="text-end"><?= __('sale_principal') ?></th>
                        <th class="text-end"><?= __('sale_interest') ?></th>
                        <th class="text-end"><?= __('sale_total') ?></th>
                        <th class="text-end"><?= __('sale_paid') ?></th>
                        <th><?= __('sale_status') ?></th>
                        <th><?= __('sale_action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schedule)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-3"><?= __('sale_no_schedule') ?></td></tr>
                    <?php else: foreach ($schedule as $i => $s):
                        $rowClass = !empty($s['is_overdue']) ? 'table-danger' : '';
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= (int)($s['installment_number'] ?? ($i + 1)) ?></td>
                            <td><?= htmlspecialchars((string)($s['due_date'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)($s['installment_type'] ?? '')) ?></td>
                            <td class="text-end">&#8377;<?= number_format((float)($s['principal_component'] ?? 0)) ?></td>
                            <td class="text-end">&#8377;<?= number_format((float)($s['interest_component'] ?? 0)) ?></td>
                            <td class="text-end">&#8377;<?= number_format((float)($s['amount_due'] ?? 0)) ?></td>
                            <td class="text-end text-success">&#8377;<?= number_format((float)($s['amount_paid'] ?? 0)) ?></td>
                            <td>
                                <?php
                                $st = $s['status'] ?? 'pending';
                                $cls = ['paid'=>'success', 'overdue'=>'danger', 'partial'=>'warning', 'cleared'=>'success'][$st] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($st ?? '') ?></span>
                            </td>
                            <td>
                                <?php if ($st !== 'paid'): ?>
                                    <a class="btn btn-sm btn-success" href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/installments/<?= (int)($s['id'] ?? 0) ?>/pay"><?= __('sale_pay') ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
