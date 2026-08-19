<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Salary Payouts</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus me-1"></i>New Batch</button>
    </div>
    <div class="row g-3 mb-4">
        <?php foreach ($batches ?? [] as $b): ?>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="mb-1"><?= htmlspecialchars($b['payout_batch_id'] ?? '') ?></h6>
                    <div class="d-flex justify-content-between">
                        <span>Total: <strong>₹<?= number_format($b['total_amount'] ?? 0, 2) ?></strong></span>
                        <span>users: <?= (int)($b['total'] ?? 0) ?></span>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success me-1"><?= (int)($b['processed_count'] ?? 0) ?> Processed</span>
                        <span class="badge bg-danger"><?= (int)($b['failed_count'] ?? 0) ?> Failed</span>
                    </div>
                    <small class="text-muted"><?= htmlspecialchars($b['payout_date'] ?? '') ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; if (empty($batches ?? [])): ?>
        <div class="col-12"><div class="alert alert-info">No payout batches created yet</div></div>
        <?php endif; ?>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Payouts</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr>
                        <th>#</th><th>Batch</th><th>Employee</th><th>Amount</th><th>Date</th><th>Method</th><th>Reference</th><th>Status</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($payouts ?? [])): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No payouts yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($payouts as $po): ?>
                            <tr>
                                <td><?= $po['id'] ?></td>
                                <td><?= htmlspecialchars($po['payout_batch_id'] ?? '-') ?></td>
                                <td><strong><?= htmlspecialchars($po['employee_name'] ?? '') ?></strong></td>
                                <td>₹<?= number_format($po['amount'] ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($po['payout_date'] ?? '') ?></td>
                                <td><?= ucfirst(str_replace('_',' ', $po['payment_method'] ?? 'bank_transfer')) ?></td>
                                <td><?= htmlspecialchars($po['reference_no'] ?? '-') ?></td>
                                <td><span class="badge bg-<?= match($po['status']??'pending') { 'processed'=>'success', 'pending'=>'warning', 'failed'=>'danger', default=>'secondary' } ?>"><?= ucfirst($po['status'] ?? 'pending') ?></span></td>
                                <td>
                                    <?php if (($po['status'] ?? '') === 'pending'): ?>
                                    <form method="post" action="<?= BASE_URL ?>/admin/salary/payouts/process/<?= $po['id'] ?>" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="text" name="reference_no" placeholder="REF-..." class="form-control form-control-sm d-inline" class="style-50190">
                                        <button type="submit" class="btn btn-sm btn-outline-success" aria-label="Confirm"><i class="fas fa-check"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/payouts/create">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-1"></i>Create Payout Batch</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    <table class="table table-bordered">
                        <thead class="table-light"><tr><th>Employee</th><th>Amount (₹)</th></tr></thead>
                        <tbody id="payoutRows">
                            <tr>
                                <td>
                                    <select name="employee_ids[]" class="form-select" required>
                                        <option value="">Select</option>
                                        <?php foreach ($users ?? [] as $e): ?>
                                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" name="amounts[]" class="form-control" placeholder="0.00" required></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="var t=document.getElementById('payoutRows');var r=t.rows[0].cloneNode(true);r.querySelectorAll('input').forEach(i=>i.value='');r.querySelectorAll('select').forEach(s=>s.selectedIndex=0);t.appendChild(r);"><i class="fas fa-plus me-1"></i>Add Row</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>
