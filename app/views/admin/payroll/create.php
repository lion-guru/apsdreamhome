<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-plus me-2"></i>Add Payroll Record</h1>
        <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/payroll/store">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users ?? [] as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?> (<?= htmlspecialchars($emp['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Basic Salary (₹)</label>
                        <input type="number" name="basic_salary" class="form-control" step="0.01" value="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">HRA (₹)</label>
                        <input type="number" name="hra" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Allowance (₹)</label>
                        <input type="number" name="allowance" class="form-control" step="0.01" value="0">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Deduction (₹)</label>
                        <input type="number" name="deduction" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Payroll</button>
            </form>
        </div>
    </div>
</div>
