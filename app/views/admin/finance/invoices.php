<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Register</h1>
            <p class="text-muted mb-0">Manage all invoices</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/invoices/manage/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Invoice
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/finance/invoices" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by number or client..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach (['draft','sent','viewed','paid','overdue','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($invoices)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No invoices found</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Client</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($inv['invoice_number'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($inv['invoice_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($inv['due_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($inv['client_name'] ?? '') ?></td>
                                    <td class="text-end">₹<?= number_format($inv['total_amount'] ?? 0, 2) ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match($inv['status']) {
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'sent' => 'info',
                                            'viewed' => 'warning',
                                            'cancelled' => 'dark',
                                            default => 'secondary',
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= strtoupper($inv['status']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/admin/finance/invoices/view/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/finance/invoices/download/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-success" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
