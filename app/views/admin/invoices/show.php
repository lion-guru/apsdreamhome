<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Invoice Details</h1>
            <p class="text-muted mb-0"><?= $invoice['invoice_number'] ?? '' ?></p>
        </div>
        <div>
            <a href="/admin/invoices" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Invoices
            </a>
            <?php if ($invoice['status'] != 'paid'): ?>
                <a href="/admin/invoices/<?= $invoice['id'] ?>/mark-paid" class="btn btn-success ms-2" onclick="return confirm('Mark this invoice as paid?')">
                    <i class="fas fa-check me-2"></i>Mark as Paid
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Invoice Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Invoice Number:</strong>
                            <p class="mb-1"><?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p class="mb-1">
                                <span class="badge bg-<?= $invoice['status'] == 'paid' ? 'success' : 'warning' ?>">
                                    <?= htmlspecialchars($invoice['status_label'] ?? $invoice['status']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Customer:</strong>
                            <p class="mb-1"><?= htmlspecialchars($invoice['customer_name'] ?? 'N/A') ?></p>
                            <small class="text-muted"><?= htmlspecialchars($invoice['customer_email'] ?? '') ?></small>
                            <br>
                            <small class="text-muted"><?= htmlspecialchars($invoice['customer_phone'] ?? '') ?></small>
                        </div>
                        <div class="col-md-6">
                            <strong>Property:</strong>
                            <p class="mb-1"><?= htmlspecialchars($invoice['property_title'] ?? 'N/A') ?></p>
                            <small class="text-muted"><?= htmlspecialchars($invoice['property_location'] ?? '') ?></small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Amount:</strong>
                            <h4 class="text-success mb-1">₹<?= number_format($invoice['amount'] ?? 0) ?></h4>
                        </div>
                        <div class="col-md-6">
                            <strong>Due Date:</strong>
                            <p class="mb-1"><?= date('d M Y', strtotime($invoice['due_date'] ?? 'now')) ?></p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Installment:</strong>
                        <p class="mb-1"><?= $invoice['installment_number'] ?? 1 ?> / <?= $invoice['total_installments'] ?? 1 ?></p>
                    </div>

                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p class="mb-1"><?= htmlspecialchars($invoice['description'] ?? 'No description provided') ?></p>
                    </div>

                    <div class="row text-muted">
                        <div class="col-md-6">
                            <small>Created: <?= date('d M Y H:i', strtotime($invoice['created_at'] ?? 'now')) ?></small>
                        </div>
                        <div class="col-md-6">
                            <small>Updated: <?= date('d M Y H:i', strtotime($invoice['updated_at'] ?? 'now')) ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($payment_history)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payment_history as $payment): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($payment['payment_date'] ?? 'now')) ?></td>
                                            <td>₹<?= number_format($payment['amount'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars($payment['payment_method'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $payment['status'] == 'completed' ? 'success' : 'warning' ?>">
                                                    <?= htmlspecialchars($payment['status'] ?? '') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No payment history available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/admin/invoices/<?= $invoice['id'] ?>/edit" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Edit Invoice
                        </a>
                        <?php if ($invoice['status'] != 'paid'): ?>
                            <a href="/admin/invoices/<?= $invoice['id'] ?>/send" class="btn btn-outline-info">
                                <i class="fas fa-envelope me-2"></i>Send to Customer
                            </a>
                        <?php endif; ?>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-print me-2"></i>Print Invoice
                        </button>
                        <button class="btn btn-outline-warning">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
