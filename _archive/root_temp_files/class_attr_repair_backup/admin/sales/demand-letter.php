<?php
/** @var array $installment */
/** @var array $booking */
/** @var array|null $letter */
$installment = $installment ?? [];
$booking = $booking ?? [];
$letter = $letter ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-envelope-open-text me-2"></i><?= __('sale_demand_letter') ?></h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-sm btn-link">
            <i class="fas fa-arrow-left me-1"></i><?= __('sale_back_to_booking') ?>
        </a>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_booking_num') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_customer') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($booking['customer_name'] ?? '—')) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_installment_num') ?></div>
                <div class="fw-bold"><?= (int)($installment['installment_number'] ?? 0) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_due_date') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($installment['due_date'] ?? '')) ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small"><?= __('sale_amount_due') ?></div>
                <div class="fw-bold text-danger">&#8377;<?= number_format((float)($installment['amount_due'] ?? 0)) ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small"><?= __('sale_amount_paid') ?></div>
                <div class="fw-bold text-success">&#8377;<?= number_format((float)($installment['amount_paid'] ?? 0)) ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small"><?= __('sale_outstanding') ?></div>
                <div class="fw-bold">&#8377;<?= number_format(max(0, (float)($installment['amount_due'] ?? 0) - (float)($installment['amount_paid'] ?? 0))) ?></div>
            </div>
        </div>

        <?php if ($letter): ?>
            <div class="aps-cp-card border">
                <div class="aps-cp-card-header">
                    <h6 class="m-0"><?= __('sale_letter_num') ?><?= htmlspecialchars((string)($letter['letter_number'] ?? '')) ?> (<?= htmlspecialchars((string)($letter['letter_type'] ?? '')) ?>)</h6>
                </div>
                <div class="aps-cp-card-body">
                    <pre class="m-0" class="style-92067"><?= htmlspecialchars((string)($letter['letter_content'] ?? '')) ?></pre>
                    <hr>
                    <small class="text-muted">
                        <?= __('sale_generated') ?>: <?= htmlspecialchars((string)($letter['generated_date'] ?? '')) ?>
                        <?php if (!empty($letter['sent_at'])): ?>
                            &middot; <?= __('sale_sent') ?>: <?= htmlspecialchars($letter['sent_at'] ?? '') ?>
                        <?php else: ?>
                            &middot; <em><?= __('sale_draft_not_sent') ?></em>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-3">
                <i class="fas fa-info-circle"></i> <?= __('sale_no_demand_letter') ?>
            </div>
        <?php endif; ?>
    </div>
</div>
