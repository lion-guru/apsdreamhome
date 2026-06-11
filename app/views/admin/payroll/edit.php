<?php $payroll = $payroll ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>Edit Payroll Record #<?= $payroll['id'] ?? '' ?></h1>
        <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/payroll/update/<?= $payroll['id'] ?? 0 ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users ?? [] as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= ($emp['id'] == ($payroll['employee_id'] ?? 0)) ? 'selected' : '' ?>><?= htmlspecialchars($emp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="<?= htmlspecialchars($payroll['payment_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Basic Salary (₹)</label>
                        <input type="number" name="basic_salary" class="form-control" step="0.01" value="<?= $payroll['basic_salary'] ?? 0 ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">HRA (₹)</label>
                        <input type="number" name="hra" class="form-control" step="0.01" value="<?= $payroll['hra'] ?? 0 ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Allowance (₹)</label>
                        <input type="number" name="allowance" class="form-control" step="0.01" value="<?= $payroll['allowance'] ?? 0 ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Deduction (₹)</label>
                        <input type="number" name="deduction" class="form-control" step="0.01" value="<?= $payroll['deduction'] ?? 0 ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="pending" <?= ($payroll['payment_status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= ($payroll['payment_status'] ?? '') == 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="advance" <?= ($payroll['payment_status'] ?? '') == 'advance' ? 'selected' : '' ?>>Advance</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($payroll['notes'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
            </form>
        </div>
    </div>
</div>
