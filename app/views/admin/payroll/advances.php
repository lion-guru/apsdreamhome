<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Salary Advances</h1>
        <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addAdvanceModal"><i class="fas fa-plus me-1"></i>Add Advance</button>
            <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Payroll</a>
        </div>
    </div>
    <div class="modal fade" id="addAdvanceModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/payroll/advances/add">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header"><h5 class="modal-title">Add Salary Advance</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users ?? [] as $u): ?>
                            <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Amount (₹) <span class="text-danger">*</span></label><input type="number" step="0.01" min="1" name="advance_amount" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Repay EMI (₹)</label><input type="number" step="0.01" min="0" name="advance_repay_emi" class="form-control" value="0"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Reason</label><input type="text" name="advance_reason" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Approved By (Admin ID, blank = you)</label><input type="number" name="advance_approved_by" class="form-control" placeholder="defaults to current admin"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Advance</button>
                </div>
            </form>
        </div></div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Amount (₹)</th>
                            <th>Reason</th>
                            <th>Approved By</th>
                            <th>Repay EMI</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($advances ?? [])): ?>
                            <tr><td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                <h5>No Advances</h5>
                                <p class="mb-3">No salary advance records found.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($advances as $a): ?>
                                <tr>
                                    <td><?= $a['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($a['employee_name'] ?? '') ?></strong></td>
                                    <td class="text-warning">₹<?= number_format($a['advance_amount'] ?? 0, 2) ?></td>
                                    <td><?= htmlspecialchars($a['advance_reason'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($a['approved_by_name'] ?? ($a['advance_approved_by'] ? ('Admin #' . $a['advance_approved_by']) : '-')) ?></td>
                                    <td>₹<?= number_format($a['advance_repay_emi'] ?? 0, 2) ?></td>
                                    <td><?= htmlspecialchars($a['payment_date'] ?? date('Y-m-d', strtotime($a['created_at'] ?? ''))) ?></td>
                                    <td><span class="badge bg-warning text-dark">Advance</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
