<?php $page_title = $page_title ?? __('finance_cash_book'); $page_heading = $page_heading ?? __('finance_cash_book'); $from = $from ?? date('Y-m-01'); $to = $to ?? date('Y-m-t'); $bank_id = $bank_id ?? null; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-book me-2 text-primary"></i><?php echo __('finance_cash_book'); ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/transaction-form" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?php echo __('finance_record_cash_transaction'); ?></a>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <input type="hidden" name="url" value="/admin/finance/cash-book">
                <div class="col-md-3"><label class="form-label small"><?php echo __('finance_from'); ?></label><input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>" class="form-control form-control-sm"></div>
                <div class="col-md-3"><label class="form-label small"><?php echo __('finance_to'); ?></label><input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>" class="form-control form-control-sm"></div>
                <div class="col-md-3">
                    <label class="form-label small"><?php echo __('finance_bank_account'); ?></label>
                    <select name="bank_account_id" class="form-select form-select-sm">
                        <option value=""><?php echo __('finance_all'); ?></option>
                        <?php foreach (($banks ?? []) as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= $bank_id == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['account_name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i><?php echo __('finance_filter'); ?></button></div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label"><?php echo __('finance_receipts'); ?></div><div class="aps-cp-stat-value text-success">₹<?= number_format((float)($summary['receipt'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label"><?php echo __('finance_payments'); ?></div><div class="aps-cp-stat-value text-danger">₹<?= number_format((float)($summary['payment'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label"><?php echo __('finance_contra'); ?></div><div class="aps-cp-stat-value text-secondary">₹<?= number_format((float)($summary['contra'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label"><?php echo __('finance_net'); ?></div><div class="aps-cp-stat-value <?= ((float)($summary['net'] ?? 0)) >= 0 ? 'text-primary' : 'text-danger' ?>">₹<?= number_format((float)($summary['net'] ?? 0), 0) ?></div></div></div></div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th><?php echo __('finance_date'); ?></th><th><?php echo __('finance_voucher'); ?></th><th><?php echo __('finance_type'); ?></th><th><?php echo __('finance_party'); ?></th><th><?php echo __('finance_mode'); ?></th><th><?php echo __('finance_narration'); ?></th><th class="text-end"><?php echo __('finance_amount'); ?></th></tr>
                </thead>
                <tbody>
                <?php if (empty($entries)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4"><?php echo __('finance_no_transactions_in_period'); ?></td></tr>
                <?php else: foreach ($entries as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['transaction_date'] ?? '') ?></td>
                        <td><code><?= htmlspecialchars($e['voucher_number'] ?? '-') ?></code></td>
                        <td><span class="badge bg-<?= ($e['transaction_type'] ?? '') === 'receipt' ? 'success' : 'danger' ?>"><?= htmlspecialchars($e['transaction_type'] ?? '') ?></span></td>
                        <td><?= htmlspecialchars($e['party_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($e['payment_mode'] ?? '-') ?></td>
                        <td><small><?= htmlspecialchars($e['narration'] ?? '') ?></small></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($e['amount'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
