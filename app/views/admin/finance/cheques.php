<?php $page_title = $page_title ?? __('finance_cheque_register'); $page_heading = $page_heading ?? __('finance_cheque_dd_register'); $status = $status ?? ''; $bank_id = $bank_id ?? ''; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-money-check me-2 text-primary"></i><?php echo __('finance_cheque_dd_register'); ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/cheque-issue" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?php echo __('finance_issue_cheque'); ?></a>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="url" value="/admin/finance/cheques">
                <div class="col-md-3">
                    <label class="form-label small"><?php echo __('finance_status'); ?></label>
                    <select name="status" class="form-select form-select-sm">
                        <option value=""><?php echo __('finance_all'); ?></option>
                        <?php foreach (['issued','pending','cleared','bounced','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small"><?php echo __('finance_bank'); ?></label>
                    <select name="bank_account_id" class="form-select form-select-sm">
                        <option value=""><?php echo __('finance_all'); ?></option>
                        <?php foreach (($banks ?? []) as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= $bank_id == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['account_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i><?php echo __('finance_filter'); ?></button></div>
            </form>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i><?php echo __('finance_cheque_register'); ?></h5></div>
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th><?php echo __('finance_date'); ?></th><th><?php echo __('finance_cheque_hash'); ?></th><th><?php echo __('finance_bank'); ?></th><th><?php echo __('finance_payee'); ?></th><th><?php echo __('finance_purpose'); ?></th><th class="text-end"><?php echo __('finance_amount'); ?></th><th><?php echo __('finance_status'); ?></th><th></th></tr>
                </thead>
                <tbody>
                <?php if (empty($cheques)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><?php echo __('finance_no_cheques_in_register'); ?></td></tr>
                <?php else: foreach ($cheques as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['cheque_date'] ?? '') ?></td>
                        <td><code><?= htmlspecialchars($c['cheque_number'] ?? '-') ?></code></td>
                        <td><?= htmlspecialchars($c['account_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($c['payee_name'] ?? '-') ?></td>
                        <td><small><?= htmlspecialchars($c['purpose'] ?? '-') ?></small></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($c['amount'] ?? 0), 2) ?></td>
                        <td>
                            <?php $st = $c['status'] ?? 'issued'; $bg = ['issued'=>'primary','pending'=>'warning','cleared'=>'success','bounced'=>'danger','cancelled'=>'secondary'][$st] ?? 'secondary'; ?>
                            <span class="badge bg-<?= $bg ?>"><?= htmlspecialchars($st) ?></span>
                        </td>
                        <td>
                            <?php if (in_array($st, ['issued','pending'])): ?>
                            <form method="post" action="<?= BASE_URL ?>/admin/finance/cheque-status" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                <button type="submit" name="status" value="cleared" class="btn btn-sm btn-outline-success" title="<?php echo __('finance_mark_cleared'); ?>"><i class="fas fa-check"></i></button>
                                <button type="submit" name="status" value="bounced" class="btn btn-sm btn-outline-danger" title="<?php echo __('finance_mark_bounced'); ?>" onclick="this.form.reason=prompt('<?php echo __('finance_bounce_reason'); ?>:')||''"><i class="fas fa-times"></i></button>
                                <input type="hidden" name="reason" value="">
                            </form>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/admin/finance/cheques/<?= (int)$c['id'] ?>/print" target="_blank" class="btn btn-sm btn-outline-primary ms-1" title="Print Cheque"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-danger"></i><?php echo __('finance_bounce_log'); ?></h5></div>
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th><?php echo __('finance_cheque_hash'); ?></th><th><?php echo __('finance_reason'); ?></th><th><?php echo __('finance_bank_charges'); ?></th><th><?php echo __('finance_date'); ?></th></tr></thead>
                <tbody>
                <?php if (empty($bounce_log)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4"><?php echo __('finance_no_bounced_cheques'); ?></td></tr>
                <?php else: foreach ($bounce_log as $b): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($b['cheque_number'] ?? '-') ?></code></td>
                        <td><?= htmlspecialchars($b['reason'] ?? '-') ?></td>
                        <td>₹<?= number_format((float)($b['bank_charges'] ?? 0), 2) ?></td>
                        <td><?= htmlspecialchars($b['bounce_date'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
