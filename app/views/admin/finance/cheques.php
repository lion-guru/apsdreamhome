<?php $page_title = $page_title ?? 'Cheque Register'; $page_heading = $page_heading ?? 'Cheque / DD Register'; $status = $status ?? ''; $bank_id = $bank_id ?? ''; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-money-check me-2 text-primary"></i>Cheque / DD Register</h2>
        <a href="<?= BASE_URL ?>/admin/finance/cheque-issue" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Issue Cheque</a>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="url" value="/admin/finance/cheques">
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['issued','pending','cleared','bounced','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Bank</label>
                    <select name="bank_account_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (($banks ?? []) as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= $bank_id == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['account_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
            </form>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Cheque Register</h5></div>
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Cheque #</th><th>Bank</th><th>Payee</th><th>Purpose</th><th class="text-end">Amount</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                <?php if (empty($cheques)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No cheques in register</td></tr>
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
                                <button type="submit" name="status" value="cleared" class="btn btn-sm btn-outline-success" title="Mark cleared"><i class="fas fa-check"></i></button>
                                <button type="submit" name="status" value="bounced" class="btn btn-sm btn-outline-danger" title="Mark bounced" onclick="this.form.reason=prompt('Bounce reason:')||''"><i class="fas fa-times"></i></button>
                                <input type="hidden" name="reason" value="">
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Bounce Log</h5></div>
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Cheque #</th><th>Reason</th><th>Bank Charges</th><th>Date</th></tr></thead>
                <tbody>
                <?php if (empty($bounce_log)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No bounced cheques</td></tr>
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
