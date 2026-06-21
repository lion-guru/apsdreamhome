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
        <h5 class="m-0"><i class="fas fa-<?= $mode === 'edit' ? 'edit' : 'plus' ?> me-2"></i><?= $mode === 'edit' ? __('sale_edit_booking') : __('sale_new_booking') ?></h5>
    </div>
    <div class="aps-cp-card-body">
        <form method="post" action="<?= htmlspecialchars($action) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_plot_label') ?></label>
                    <select name="plot_id" class="form-select" required <?= $mode === 'edit' ? 'disabled' : '' ?>>
                        <option value=""><?= __('sale_select_plot') ?></option>
                        <?php foreach ($plots as $p): ?>
                            <option value="<?= (int)($p['id'] ?? 0) ?>" <?= ((int)($booking['plot_id'] ?? 0) === (int)($p['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($p['colony_name'] ?? '')) ?> — <?= htmlspecialchars((string)($p['plot_code'] ?? $p['plot_number'] ?? '')) ?> (&#8377;<?= number_format((float)($p['total_price'] ?? 0)) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_customer_label') ?></label>
                    <select name="customer_id" class="form-select" required <?= $mode === 'edit' ? 'disabled' : '' ?>>
                        <option value=""><?= __('sale_select_customer') ?></option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int)($c['id'] ?? 0) ?>" <?= ((int)($booking['customer_id'] ?? 0) === (int)($c['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($c['name'] ?? '')) ?> — <?= htmlspecialchars((string)($c['phone'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_plot_value_label') ?></label>
                    <input type="number" step="0.01" name="total_plot_value" value="<?= htmlspecialchars((string)($booking['total_plot_value'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_token_amount_label') ?></label>
                    <input type="number" step="0.01" name="booking_amount" value="<?= htmlspecialchars((string)($booking['booking_amount'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_agreement_value_label') ?></label>
                    <input type="number" step="0.01" name="agreement_value" value="<?= htmlspecialchars((string)($booking['agreement_value'] ?? $booking['total_plot_value'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __('sale_channel_label') ?></label>
                    <select name="channel" class="form-select" required>
                        <?php foreach (['direct' => __('sale_direct'), 'associate' => __('sale_associate_label'), 'agent' => __('sale_agent'), 'self' => __('sale_self')] as $ch => $label): ?>
                            <option value="<?= $ch ?>" <?= (($booking['channel'] ?? 'direct') === $ch) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __('sale_associate_label') ?></label>
                    <select name="associate_id" class="form-select">
                        <option value=""><?= __('sale_none_select') ?></option>
                        <?php foreach ($associates as $a): ?>
                            <option value="<?= (int)($a['id'] ?? 0) ?>" <?= ((int)($booking['associate_id'] ?? 0) === (int)($a['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($a['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __('sale_sales_manager') ?></label>
                    <select name="sales_manager_id" class="form-select">
                        <option value=""><?= __('sale_none_select') ?></option>
                        <?php foreach ($sales_managers as $sm): ?>
                            <option value="<?= (int)($sm['id'] ?? 0) ?>" <?= ((int)($booking['sales_manager_id'] ?? 0) === (int)($sm['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($sm['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __('sale_override_commission') ?></label>
                    <input type="number" step="0.01" name="commission_pct" value="<?= htmlspecialchars((string)($booking['commission_pct'] ?? '')) ?>" class="form-control" placeholder="<?= __('sale_default') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label"><?= __('sale_notes') ?></label>
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars((string)($booking['notes'] ?? '')) ?></textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i><?= $mode === 'edit' ? __('sale_update_booking') : __('sale_create_booking') ?></button>
                <a class="btn btn-link" href="<?= htmlspecialchars($base) ?>/admin/sales/bookings"><?= __('sale_cancel') ?></a>
            </div>
        </form>
    </div>
</div>
