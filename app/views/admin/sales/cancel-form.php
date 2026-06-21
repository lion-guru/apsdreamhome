<?php
/** @var array $booking */
$booking = $booking ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5 class="m-0 text-danger"><i class="fas fa-ban me-2"></i><?= __('sale_cancel_booking') ?> — <?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?></h5>
    </div>
    <div class="aps-cp-card-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <?= __('sale_cancel_warning') ?>
        </div>
        <form method="post" action="<?= htmlspecialchars($base) ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/cancel">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_reason') ?> *</label>
                    <select name="reason" class="form-select" required>
                        <?php foreach (['Customer request', 'EMI default', 'Title issue', 'Payment failure', 'Force majeure', 'Other'] as $r): ?>
                            <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_cancellation_charge') ?></label>
                    <input type="number" step="0.01" name="cancellation_charge" value="0" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label"><?= __('sale_notes') ?></label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional context for the audit log..."></textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-danger" type="submit" onclick="return confirm('Cancel this booking? Plot will be released.');">
                    <i class="fas fa-ban me-1"></i><?= __('sale_cancel_booking') ?>
                </button>
                <a class="btn btn-link" href="<?= htmlspecialchars($base) ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>"><?= __('sale_back') ?></a>
            </div>
        </form>
    </div>
</div>
