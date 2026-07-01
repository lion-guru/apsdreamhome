<?php $page_title = $page_title ?? __('finance_bank_reconciliation'); $page_heading = $page_heading ?? __('finance_bank_reconciliation'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-balance-scale me-2 text-primary"></i><?php echo __('finance_bank_reconciliation'); ?></h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newReconModal"><i class="fas fa-plus me-1"></i><?php echo __('finance_new_reconciliation'); ?></button>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th><?php echo __('finance_bank'); ?></th><th><?php echo __('finance_statement_date'); ?></th><th><?php echo __('finance_statement_balance'); ?></th><th><?php echo __('finance_book_balance'); ?></th><th><?php echo __('finance_diff'); ?></th><th><?php echo __('finance_status'); ?></th><th><?php echo __('finance_started'); ?></th><th></th></tr>
                </thead>
                <tbody>
                <?php if (empty($reconciliations)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><?php echo __('finance_no_reconciliations_yet'); ?></td></tr>
                <?php else: foreach ($reconciliations as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['account_name'] ?? '-') ?><br><small class="text-muted"><?= htmlspecialchars($r['bank_name'] ?? '') ?></small></td>
                        <td><?= htmlspecialchars($r['statement_date'] ?? '-') ?></td>
                        <td>₹<?= number_format((float)($r['statement_balance'] ?? 0), 2) ?></td>
                        <td>₹<?= number_format((float)($r['book_balance'] ?? 0), 2) ?></td>
                        <td>₹<?= number_format((float)($r['difference'] ?? 0), 2) ?></td>
                        <td><span class="badge bg-<?= ($r['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= htmlspecialchars($r['status'] ?? 'in_progress') ?></span></td>
                        <td><?= htmlspecialchars($r['created_at'] ?? '-') ?></td>
                        <td><a href="<?= BASE_URL ?>/admin/finance/reconciliation-match/<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-tasks me-1"></i><?php echo __('finance_match'); ?></a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<div class="modal fade" id="newReconModal" tabindex="-1"><div class="modal-dialog">
<form method="post" action="<?= BASE_URL ?>/admin/finance/reconciliation-create" class="modal-content">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <div class="modal-header"><h5 class="modal-title"><?php echo __('finance_start_bank_reconciliation'); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label"><?php echo __('finance_bank_account'); ?> <span class="text-danger">*</span></label>
            <select name="bank_account_id" required class="form-select">
                <option value="">— <?php echo __('finance_select'); ?> —</option>
                <?php foreach (($banks ?? []) as $b): ?>
                    <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name'] . ' — ' . $b['bank_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_statement_date'); ?> <span class="text-danger">*</span></label><input type="date" name="statement_date" required class="form-control" value="<?= date('Y-m-d') ?>"></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_statement_balance'); ?> (₹) <span class="text-danger">*</span></label><input type="number" name="statement_balance" step="0.01" required class="form-control"></div>
        <div class="mb-3"><label class="form-label"><?php echo __('finance_notes'); ?></label><textarea name="notes" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('finance_cancel'); ?></button><button type="submit" class="btn btn-primary"><?php echo __('finance_start'); ?></button></div>
</form></div></div>
