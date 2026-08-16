<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold"><i class="fas fa-calculator text-primary me-2"></i> Lekha-Jhokha (Expense Tracker)</h2>
            <p class="text-muted">Manage your business expenses, labor payments, and material costs in one place.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/expenses/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Expense
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted small text-uppercase fw-bold mb-3">Total Expenses (Monthly)</h6>
                    <h2 class="fw-bold mb-0 text-danger">₹<?= number_format($total_expenses ?? 0, 2) ?></h2>
                    <div class="mt-3">
                        <span class="text-muted small">Current month</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted small text-uppercase fw-bold mb-3">Pending Payments</h6>
                    <h2 class="fw-bold mb-0 text-warning">₹<?= number_format($upcoming_payments ?? 0, 2) ?></h2>
                    <div class="mt-3">
                        <span class="text-muted small">Awaiting approval</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Expenses</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_expenses)): ?>
                            <?php foreach ($recent_expenses as $expense): ?>
                                <tr>
                                    <td class="ps-3"><?= htmlspecialchars($expense['expense_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($expense['description'] ?? '') ?></td>
                                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($expense['category'] ?? 'General') ?></span></td>
                                    <td class="fw-bold text-danger">₹<?= number_format((float)($expense['amount'] ?? 0), 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($expense['status'] ?? '') === 'approved' ? 'success' : (($expense['status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst(htmlspecialchars($expense['status'] ?? 'pending')) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="/admin/expenses?highlight=<?= (int)($expense['id'] ?? 0) ?>" class="btn btn-sm btn-link text-decoration-none">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                                    No expenses recorded yet. Click "Add Expense" to get started.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
