<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Invoice Management</h1>
            <p class="text-muted mb-0">Manage plot booking invoices and payments</p>
        </div>
        <div>
            <a href="/admin/invoices/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Invoice
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Invoices</h6>
                    <h3 class="card-title mb-0"><?= $stats['total_invoices'] ?? 0 ?></h3>
                    <small class="text-white-75">Total Count</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Paid Amount</h6>
                    <h3 class="card-title mb-0">₹<?= number_format($stats['paid_amount'] ?? 0) ?></h3>
                    <small class="text-white-75"><?= $stats['paid_invoices'] ?? 0 ?> invoices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Pending Amount</h6>
                    <h3 class="card-title mb-0">₹<?= number_format($stats['pending_amount'] ?? 0) ?></h3>
                    <small class="text-white-75"> <?= $stats['pending_invoices'] ?? 0 ?> pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Overdue Amount</h6>
                    <h3 class="card-title mb-0">₹<?= number_format($stats['overdue_amount'] ?? 0) ?></h3>
                    <small class="text-white-75"><?= $stats['overdue_invoices'] ?? 0 ?> overdue</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">All Invoices</h6>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Filter by Status
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="/admin/invoices">All</a>
                        <a class="dropdown-item" href="/admin/invoices?status=paid">Paid</a>
                        <a class="dropdown-item" href="/admin/invoices?status=partial">Partial</a>
                        <a class="dropdown-item" href="/admin/invoices?status=overdue">Overdue</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($invoices)): ?>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></strong></td>
                                    <td>
                                        <div><?= htmlspecialchars($invoice['customer_name'] ?? 'N/A') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($invoice['customer_email'] ?? '') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($invoice['property_title'] ?? 'N/A') ?></td>
                                    <td><strong>₹<?= number_format($invoice['amount'] ?? 0) ?></strong></td>
                                    <td><?= date('d M Y', strtotime($invoice['due_date'] ?? 'now')) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'paid' => 'success',
                                            'partial' => 'warning',
                                            'sent' => 'primary',
                                            'overdue' => 'danger',
                                            'cancelled' => 'secondary',
                                            'draft' => 'info'
                                        ];
                                        $class = $statusClass[$invoice['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $class ?>"><?= htmlspecialchars($invoice['status_label'] ?? $invoice['status']) ?></span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($invoice['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/invoices/<?= $invoice['id'] ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/admin/invoices/<?= $invoice['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($invoice['status'] != 'paid'): ?>
                                                <a href="/admin/invoices/<?= $invoice['id'] ?>/mark-paid" class="btn btn-outline-success" title="Mark Paid" onclick="return confirm('Mark this invoice as paid?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-invoice fa-3x mb-3"></i>
                                        <p class="mb-0">No invoices found. Create your first invoice.</p>
                                        <a href="/admin/invoices/create" class="btn btn-primary mt-2">Create Invoice</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>