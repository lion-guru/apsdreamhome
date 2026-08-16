<?php $page_title = $page_title ?? __('finance_expense_approvals'); $page_heading = $page_heading ?? __('finance_expense_approvals'); $status = $status ?? ''; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i><?php echo __('finance_expense_approvals'); ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/expense-form" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?php echo __('finance_submit_expense'); ?></a>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="url" value="/admin/finance/expenses">
                <div class="col-md-3">
                    <label class="form-label small"><?php echo __('finance_status'); ?></label>
                    <select name="status" class="form-select form-select-sm">
                        <option value=""><?php echo __('finance_all'); ?></option>
                        <?php foreach (['pending','approved','rejected'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i><?php echo __('finance_filter'); ?></button></div>
            </form>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th><?php echo __('finance_date'); ?></th><th><?php echo __('finance_category'); ?></th><th><?php echo __('finance_description'); ?></th><th><?php echo __('finance_payment_mode'); ?></th><th><?php echo __('finance_submitted_by'); ?></th><th class="text-end"><?php echo __('finance_amount'); ?></th><th><?php echo __('finance_status'); ?></th><th></th></tr>
                </thead>
                <tbody>
                <?php if (empty($entries)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><?php echo __('finance_no_expenses'); ?></td></tr>
                <?php else: foreach ($entries as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['expense_date'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($e['category'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($e['description'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($e['payment_mode'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($e['submitted_by'] ?? '-') ?></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($e['amount'] ?? 0), 2) ?></td>
                        <td>
                            <?php $st = $e['status'] ?? 'pending'; $bg = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$st] ?? 'secondary'; ?>
                            <span class="badge bg-<?= $bg ?>"><?= htmlspecialchars($st ?? '') ?></span>
                        </td>
                        <td>
                            <?php if (($e['status'] ?? '') === 'pending'): ?>
                            <form method="post" action="<?= BASE_URL ?>/admin/finance/expense-approve" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                                <button class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>
                            </form>
                            <form method="post" action="<?= BASE_URL ?>/admin/finance/expense-reject" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
