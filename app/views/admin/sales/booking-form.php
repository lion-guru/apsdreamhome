<?php
/** @var string $mode */
/** @var array $booking */
/** @var array $plots */
/** @var array $customers */
/** @var array $associates */
/** @var array $sales_managers */
$mode = $mode ?? 'create';
$booking = $booking ?? [];
$plots = $plots ?? [];
$customers = $customers ?? [];
$associates = $associates ?? [];
$sales_managers = $sales_managers ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$action = $mode === 'edit'
    ? $base . '/admin/sales/bookings/' . (int)($booking['id'] ?? 0) . '/update'
    : $base . '/admin/sales/bookings/store';
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5 class="m-0"><i class="fas fa-<?= $mode === 'edit' ? 'edit' : 'plus' ?> me-2"></i><?= $mode === 'edit' ? 'Edit' : 'New' ?> Booking</h5>
    </div>
    <div class="aps-cp-card-body">
        <form method="post" action="<?= htmlspecialchars($action) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Plot *</label>
                    <select name="plot_id" class="form-select" required <?= $mode === 'edit' ? 'disabled' : '' ?>>
                        <option value="">— Select plot —</option>
                        <?php foreach ($plots as $p): ?>
                            <option value="<?= (int)($p['id'] ?? 0) ?>" <?= ((int)($booking['plot_id'] ?? 0) === (int)($p['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($p['colony_name'] ?? '')) ?> — <?= htmlspecialchars((string)($p['plot_code'] ?? $p['plot_number'] ?? '')) ?> (&#8377;<?= number_format((float)($p['total_price'] ?? 0)) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Customer *</label>
                    <select name="customer_id" class="form-select" required <?= $mode === 'edit' ? 'disabled' : '' ?>>
                        <option value="">— Select customer —</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int)($c['id'] ?? 0) ?>" <?= ((int)($booking['customer_id'] ?? 0) === (int)($c['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($c['name'] ?? '')) ?> — <?= htmlspecialchars((string)($c['phone'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Plot Value (&#8377;) *</label>
                    <input type="number" step="0.01" name="total_plot_value" value="<?= htmlspecialchars((string)($booking['total_plot_value'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Token / Booking Amount (&#8377;) *</label>
                    <input type="number" step="0.01" name="booking_amount" value="<?= htmlspecialchars((string)($booking['booking_amount'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Agreement Value (&#8377;) *</label>
                    <input type="number" step="0.01" name="agreement_value" value="<?= htmlspecialchars((string)($booking['agreement_value'] ?? $booking['total_plot_value'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Channel *</label>
                    <select name="channel" class="form-select" required>
                        <?php foreach (['direct', 'associate', 'agent', 'self'] as $ch): ?>
                            <option value="<?= $ch ?>" <?= (($booking['channel'] ?? 'direct') === $ch) ? 'selected' : '' ?>><?= ucfirst($ch) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Associate</label>
                    <select name="associate_id" class="form-select">
                        <option value="">— None —</option>
                        <?php foreach ($associates as $a): ?>
                            <option value="<?= (int)($a['id'] ?? 0) ?>" <?= ((int)($booking['associate_id'] ?? 0) === (int)($a['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($a['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sales Manager</label>
                    <select name="sales_manager_id" class="form-select">
                        <option value="">— None —</option>
                        <?php foreach ($sales_managers as $sm): ?>
                            <option value="<?= (int)($sm['id'] ?? 0) ?>" <?= ((int)($booking['sales_manager_id'] ?? 0) === (int)($sm['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($sm['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Override Commission %</label>
                    <input type="number" step="0.01" name="commission_pct" value="<?= htmlspecialchars((string)($booking['commission_pct'] ?? '')) ?>" class="form-control" placeholder="Default">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars((string)($booking['notes'] ?? '')) ?></textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i><?= $mode === 'edit' ? 'Update' : 'Create' ?> Booking</button>
                <a class="btn btn-link" href="<?= htmlspecialchars($base) ?>/admin/sales/bookings">Cancel</a>
            </div>
        </form>
    </div>
</div>
