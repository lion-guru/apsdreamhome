<?php $page_title = 'Expenses'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Expenses</h2>
        <a href="<?= BASE_URL ?>/admin/expenses/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Expense</a>
    </div>
    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= $stats['total'] ?></h3><small class="text-muted">Total Expenses</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-primary">₹<?= number_format($stats['total_amount']) ?></h3><small class="text-muted">Total Amount</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-warning"><?= $stats['pending'] ?></h3><small class="text-muted">Pending</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-success"><?= $stats['approved'] ?></h3><small class="text-muted">Approved</small></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($expenses)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No expenses recorded</h5>
                    <a href="<?= BASE_URL ?>/admin/expenses/create" class="btn btn-primary mt-2"><i class="fas fa-plus me-1"></i>Add First Expense</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Category</th><th>Amount</th><th>Description</th><th>Payment</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($expenses as $e): ?>
                            <tr>
                                <td><?= $e['id'] ?></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($e['category'] ?? 'General') ?></span></td>
                                <td><strong>₹<?= number_format($e['amount']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($e['description'] ?? '', 0, 40)) ?></td>
                                <td><?= ucfirst(htmlspecialchars($e['payment_mode'] ?? 'cash')) ?></td>
                                <td><?= $e['expense_date'] ? date('d M Y', strtotime($e['expense_date'])) : 'N/A' ?></td>
                                <td><span class="badge bg-<?= ($e['status'] ?? 'pending')==='approved'?'success':(($e['status'] ?? 'pending')==='rejected'?'danger':'warning') ?>"><?= ucfirst($e['status'] ?? 'pending') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
