<?php $page_title = $page_title ?? __('finance_petty_cash'); $page_heading = $page_heading ?? __('finance_petty_cash'); $balance = $balance ?? 0.0; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-coins me-2 text-primary"></i><?php echo __('finance_petty_cash'); ?></h2>
        <div>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#topupModal"><i class="fas fa-plus me-1"></i><?php echo __('finance_topup'); ?></button>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="fas fa-minus me-1"></i><?php echo __('finance_record_expense'); ?></button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label"><?php echo __('finance_current_balance'); ?></div><div class="aps-cp-stat-value text-success">₹<?= number_format((float)$balance, 2) ?></div></div></div></div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th><?php echo __('finance_date'); ?></th><th><?php echo __('finance_type'); ?></th><th><?php echo __('finance_description'); ?></th><th><?php echo __('finance_voucher'); ?></th><th class="text-end"><?php echo __('finance_amount'); ?></th></tr>
                </thead>
                <tbody>
                <?php if (empty($entries)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4"><?php echo __('finance_no_petty_cash_movements'); ?></td></tr>
                <?php else: foreach ($entries as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['transaction_date'] ?? '') ?></td>
                        <td><span class="badge bg-<?= ($p['type'] ?? '') === 'topup' ? 'success' : 'danger' ?>"><?= htmlspecialchars($p['type'] ?? '') ?></span></td>
                        <td><?= htmlspecialchars($p['description'] ?? '-') ?></td>
                        <td><code><?= htmlspecialchars($p['voucher_number'] ?? '-') ?></code></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($p['amount'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="topupModal" tabindex="-1"><div class="modal-dialog">
<form method="post" action="<?= BASE_URL ?>/admin/finance/petty-topup" class="modal-content">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <div class="modal-header"><h5 class="modal-title"><?php echo __('finance_petty_cash_topup'); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label"><?php echo __('finance_date'); ?></label><input type="date" name="topup_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_amount'); ?> (₹)</label><input type="number" name="amount" step="0.01" min="1" class="form-control" required></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_source'); ?></label><input type="text" name="source" class="form-control" placeholder="<?php echo __('finance_account_placeholder'); ?>"></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_remarks'); ?></label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('finance_cancel'); ?></button><button type="submit" class="btn btn-success"><?php echo __('finance_topup'); ?></button></div>
</form></div></div>

<div class="modal fade" id="expenseModal" tabindex="-1"><div class="modal-dialog">
<form method="post" action="<?= BASE_URL ?>/admin/finance/petty-expense" class="modal-content">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <div class="modal-header"><h5 class="modal-title"><?php echo __('finance_petty_cash_expense'); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label"><?php echo __('finance_date'); ?></label><input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_amount'); ?> (₹)</label><input type="number" name="amount" step="0.01" min="1" class="form-control" required></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_category'); ?></label><input type="text" name="category" class="form-control" placeholder="<?php echo __('finance_select_category'); ?>"></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_description'); ?></label><textarea name="description" class="form-control" rows="2" required></textarea></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_paid_to'); ?></label><input type="text" name="paid_to" class="form-control"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('finance_cancel'); ?></button><button type="submit" class="btn btn-danger"><?php echo __('finance_record'); ?></button></div>
</form></div></div>
