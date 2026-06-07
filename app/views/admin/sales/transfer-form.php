<?php
/** @var array $booking */
/** @var array $customers */
$booking = $booking ?? [];
$customers = $customers ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5 class="m-0 text-warning"><i class="fas fa-exchange-alt me-2"></i>Transfer Booking — <?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?></h5>
    </div>
    <div class="aps-cp-card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i>
            Transferring a booking reassigns it to a new customer. Pending payment schedule and history
            are preserved. A transfer record will be created for compliance.
        </div>
        <form method="post" action="<?= htmlspecialchars($base) ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/transfer">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Current Customer</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars((string)($booking['customer_name'] ?? '')) ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Customer *</label>
                    <select name="new_customer_id" class="form-select" required>
                        <option value="">— Select —</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int)($c['id'] ?? 0) ?>"><?= htmlspecialchars((string)($c['name'] ?? '')) ?> — <?= htmlspecialchars((string)($c['phone'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Reason *</label>
                    <input type="text" name="reason" class="form-control" required placeholder="e.g. Resale to family member, Buyer replacement, etc.">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transfer Charge (&#8377;)</label>
                    <input type="number" step="0.01" name="transfer_charge" value="0" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Internal Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional context for the audit log..."></textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-warning" type="submit" onclick="return confirm('Initiate transfer? Booking will be reassigned.');">
                    <i class="fas fa-exchange-alt me-1"></i>Initiate Transfer
                </button>
                <a class="btn btn-link" href="<?= htmlspecialchars($base) ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>">Back</a>
            </div>
        </form>
    </div>
</div>
