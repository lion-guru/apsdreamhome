<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice #<?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></h1>
        <div>
            <span class="badge bg-<?= match($invoice['status']) { 'paid' => 'success', 'overdue' => 'danger', 'sent' => 'info', 'viewed' => 'warning', 'draft' => 'secondary', 'cancelled' => 'dark', default => 'secondary' } ?> fs-6 me-2"><?= strtoupper($invoice['status']) ?></span>
            <a href="<?= BASE_URL ?>/admin/invoices/download/<?= $invoice['id'] ?>" class="btn btn-success"><i class="fas fa-download me-1"></i>Download</a>
            <a href="<?= BASE_URL ?>/admin/invoices" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>"><?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?><?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Invoice Details</h5>
                    <small class="text-muted">Created: <?= htmlspecialchars($invoice['created_at'] ?? '') ?></small>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="text-muted">From:</h6>
                            <div><strong>APS Dream Home</strong></div>
                            <div class="text-muted">Gorakhpur, Uttar Pradesh</div>
                            <div class="text-muted">info@apsdreamhome.com</div>
                            <div class="text-muted">+91 92771 21112</div>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="text-muted">To:</h6>
                            <div><strong><?= htmlspecialchars($invoice['client_name'] ?? '') ?></strong></div>
                            <?php if ($invoice['client_email']): ?><div class="text-muted"><?= htmlspecialchars($invoice['client_email'] ?? '') ?></div><?php endif; ?>
                            <?php if ($invoice['client_phone']): ?><div class="text-muted"><?= htmlspecialchars($invoice['client_phone'] ?? '') ?></div><?php endif; ?>
                            <?php if ($invoice['client_address']): ?><div class="text-muted"><?= nl2br(htmlspecialchars($invoice['client_address'] ?? '')) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-4"><strong>Invoice Date:</strong> <?= htmlspecialchars($invoice['invoice_date'] ?? '') ?></div>
                        <div class="col-sm-4"><strong>Due Date:</strong> <?= htmlspecialchars($invoice['due_date'] ?? '') ?></div>
                        <div class="col-sm-4"><strong>Currency:</strong> <?= htmlspecialchars($invoice['currency'] ?? 'INR') ?></div>
                    </div>

                    <div class="table-responsive"><table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>#</th><th>Item</th><th>Qty</th><th>Rate</th><th>Discount</th><th>Tax</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; foreach (($invoice['items'] ?? []) as $item): $i++; ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= htmlspecialchars($item['item_name'] ?? '') ?><?= !empty($item['item_description']) ? '<br><small class="text-muted">' . htmlspecialchars($item['item_description'] ?? '') . '</small>' : '' ?></td>
                                    <td><?= (int)$item['quantity'] ?></td>
                                    <td>₹<?= number_format($item['unit_price'], 2) ?></td>
                                    <td><?= $item['discount_percent'] > 0 ? $item['discount_percent'] . '%' : '-' ?></td>
                                    <td><?= $item['tax_percent'] > 0 ? $item['tax_percent'] . '%' : '-' ?></td>
                                    <td><strong>₹<?= number_format($item['line_total'] ?? ($item['unit_price'] * $item['quantity']), 2) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="6" class="text-end"><strong>Subtotal:</strong></td><td>₹<?= number_format($invoice['subtotal'] ?? 0, 2) ?></td></tr>
                            <?php if (($invoice['discount_amount'] ?? 0) > 0): ?><tr><td colspan="6" class="text-end"><strong>Discount:</strong></td><td>-₹<?= number_format($invoice['discount_amount'], 2) ?></td></tr><?php endif; ?>
                            <?php if (($invoice['tax_amount'] ?? 0) > 0): ?><tr><td colspan="6" class="text-end"><strong>Tax:</strong></td><td>₹<?= number_format($invoice['tax_amount'], 2) ?></td></tr><?php endif; ?>
                            <tr class="table-active"><td colspan="6" class="text-end"><strong>Total:</strong></td><td><strong>₹<?= number_format($invoice['total_amount'] ?? 0, 2) ?></strong></td></tr>
                            <tr><td colspan="6" class="text-end text-success"><strong>Paid:</strong></td><td><strong class="text-success">₹<?= number_format($invoice['paid_amount'] ?? 0, 2) ?></strong></td></tr>
                            <?php $due = ($invoice['total_amount'] ?? 0) - ($invoice['paid_amount'] ?? 0); ?>
                            <?php if ($due > 0): ?><tr class="table-danger"><td colspan="6" class="text-end"><strong>Due:</strong></td><td><strong>₹<?= number_format($due, 2) ?></strong></td></tr><?php endif; ?>
                        </tfoot>
                    </table></div>

                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="mt-3"><h6>Notes:</h6><p class="text-muted"><?= nl2br(htmlspecialchars($invoice['notes'] ?? '')) ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <?php if (!empty($invoice['payments'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-money-bill me-1"></i>Payments</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive"><table class="table table-sm mb-0">
                            <thead><tr><th>Date</th><th>Amount</th><th>Method</th></tr></thead>
                            <tbody>
                                <?php foreach ($invoice['payments'] as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['payment_date'] ?? $p['created_at']) ?></td>
                                        <td>₹<?= number_format($p['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($p['payment_method'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <a href="<?= BASE_URL ?>/admin/invoices/download/<?= $invoice['id'] ?>" class="btn btn-success w-100 mb-2"><i class="fas fa-download me-1"></i>Download Invoice</a>
                    <form method="POST" action="<?= BASE_URL ?>/admin/invoices/delete/<?= $invoice['id'] ?>" onsubmit="return confirm('Cancel this invoice?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button class="btn btn-danger w-100"><i class="fas fa-ban me-1"></i>Cancel Invoice</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
