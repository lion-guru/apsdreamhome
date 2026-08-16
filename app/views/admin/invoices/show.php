<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Invoice <?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></h1>
            <p class="text-muted mb-0">
                <?php
                $sc = ['paid' => 'success', 'sent' => 'primary', 'viewed' => 'info', 'overdue' => 'danger', 'cancelled' => 'secondary', 'draft' => 'warning'];
                $cls = $sc[$invoice['status'] ?? ''] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $cls ?> fs-6"><?= strtoupper($invoice['status'] ?? '') ?></span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/invoices/manage" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            <a href="<?= BASE_URL ?>/admin/invoices/<?= $invoice['id'] ?>/pdf" class="btn btn-success" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>Download PDF
            </a>
            <?php if (($invoice['status'] ?? '') !== 'paid'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/invoices/manage/<?= $invoice['id'] ?>/mark-paid" class="d-inline" onsubmit="return confirm('Mark this invoice as paid?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button class="btn btn-success"><i class="fas fa-check me-1"></i>Mark Paid</button>
                </form>
            <?php endif; ?>
            <?php if (($invoice['status'] ?? '') === 'draft'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/invoices/manage/<?= $invoice['id'] ?>/send" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button class="btn btn-info text-white"><i class="fas fa-paper-plane me-1"></i>Send</button>
                </form>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/invoices/manage/<?= $invoice['id'] ?>/edit" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        </div>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card aps-cp-card mb-4">
                <div class="card-header aps-cp-card-header d-flex justify-content-between">
                    <span><i class="fas fa-file-invoice me-2"></i>Invoice Details</span>
                    <small class="text-muted">Created: <?= date('d M Y H:i', strtotime($invoice['created_at'] ?? 'now')) ?></small>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="text-muted">From:</h6>
                            <div class="fw-bold"><?= htmlspecialchars($company['company_name'] ?? 'APS Dream Home') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($company['address'] ?? '') ?></div>
                            <?php if (!empty($company['gstin'])): ?>
                                <div class="text-muted small">GSTIN: <?= htmlspecialchars($company['gstin'] ?? '') ?></div>
                            <?php endif; ?>
                            <div class="text-muted small"><?= htmlspecialchars($company['phone'] ?? '') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($company['email'] ?? '') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="text-muted">To:</h6>
                            <div class="fw-bold"><?= htmlspecialchars($invoice['client_name'] ?? '') ?></div>
                            <?php if (!empty($invoice['client_email'])): ?>
                                <div class="text-muted small"><?= htmlspecialchars($invoice['client_email'] ?? '') ?></div>
                            <?php endif; ?>
                            <?php if (!empty($invoice['client_phone'])): ?>
                                <div class="text-muted small"><?= htmlspecialchars($invoice['client_phone'] ?? '') ?></div>
                            <?php endif; ?>
                            <?php if (!empty($invoice['client_address'])): ?>
                                <div class="text-muted small"><?= nl2br(htmlspecialchars($invoice['client_address'] ?? '')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-4 g-3">
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Invoice Date</small>
                            <strong><?= htmlspecialchars($invoice['invoice_date'] ?? '') ?></strong>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Due Date</small>
                            <strong><?= htmlspecialchars($invoice['due_date'] ?? '') ?></strong>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted d-block">GST Type</small>
                            <strong><?= strtoupper($invoice['gst_type'] ?? '') ?></strong>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Place of Supply</small>
                            <strong><?= htmlspecialchars($invoice['place_of_supply'] ?? '') ?></strong>
                        </div>
                    </div>

                    <?php if (!empty($invoice['gstin'])): ?>
                        <div class="row mb-3 g-3">
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Client GSTIN</small>
                                <strong><?= htmlspecialchars($invoice['gstin'] ?? '') ?></strong>
                            </div>
                            <?php if (!empty($invoice['hsn_code'])): ?>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">HSN Code</small>
                                    <strong><?= htmlspecialchars($invoice['hsn_code'] ?? '') ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($invoice['e_invoice_number'])): ?>
                                <div class="col-sm-4">
                                    <small class="text-muted d-block">E-Invoice #</small>
                                    <strong><?= htmlspecialchars($invoice['e_invoice_number'] ?? '') ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card aps-cp-card mb-4">
                <div class="card-header aps-cp-card-header">
                    <span><i class="fas fa-list me-2"></i>Line Items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Disc</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 0; foreach (($invoice['items'] ?? []) as $item): $i++; ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td>
                                            <div class="fw-medium"><?= htmlspecialchars($item['item_name'] ?? '') ?></div>
                                            <?php if (!empty($item['item_description'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($item['item_description'] ?? '') ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= (int)$item['quantity'] ?></td>
                                        <td class="text-end">₹<?= number_format($item['unit_price'], 2) ?></td>
                                        <td class="text-end"><?= ($item['discount_percent'] ?? 0) > 0 ? $item['discount_percent'] . '%' : '-' ?></td>
                                        <td class="text-end"><?= ($item['tax_percent'] ?? 0) > 0 ? $item['tax_percent'] . '%' : '-' ?></td>
                                        <td class="text-end fw-bold">₹<?= number_format($item['line_total'] ?? 0, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($invoice['items'])): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-3">No line items</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card aps-cp-card mb-4">
                <div class="card-header aps-cp-card-header">
                    <span><i class="fas fa-calculator me-2"></i>Amount Summary</span>
                </div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td>Subtotal</td><td class="text-end">₹<?= number_format($invoice['subtotal'] ?? 0, 2) ?></td></tr>
                        <?php if (($invoice['discount_amount'] ?? 0) > 0): ?>
                            <tr><td class="text-danger">Discount</td><td class="text-end text-danger">-₹<?= number_format($invoice['discount_amount'], 2) ?></td></tr>
                        <?php endif; ?>
                        <?php if (($invoice['gst_type'] ?? '') === 'cgst_sgst'): ?>
                            <tr><td class="text-muted">CGST (<?= number_format($invoice['gst_rate'] ?? 0) / 2 ?>%)</td><td class="text-end">₹<?= number_format($invoice['cgst_amount'] ?? 0, 2) ?></td></tr>
                            <tr><td class="text-muted">SGST (<?= number_format($invoice['gst_rate'] ?? 0) / 2 ?>%)</td><td class="text-end">₹<?= number_format($invoice['sgst_amount'] ?? 0, 2) ?></td></tr>
                        <?php elseif (($invoice['gst_type'] ?? '') === 'igst'): ?>
                            <tr><td class="text-muted">IGST (<?= number_format($invoice['gst_rate'] ?? 0) ?>%)</td><td class="text-end">₹<?= number_format($invoice['igst_amount'] ?? 0, 2) ?></td></tr>
                        <?php endif; ?>
                        <?php if (($invoice['tax_amount'] ?? 0) > 0): ?>
                            <tr><td>Total Tax</td><td class="text-end text-primary fw-bold">₹<?= number_format($invoice['tax_amount'], 2) ?></td></tr>
                        <?php endif; ?>
                        <tr class="table-active"><td class="fw-bold">Total Amount</td><td class="text-end fw-bold fs-5">₹<?= number_format($invoice['total_amount'] ?? 0, 2) ?></td></tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($invoice['payment_terms']) || !empty($invoice['notes'])): ?>
                <div class="card aps-cp-card mb-4">
                    <div class="card-header aps-cp-card-header">
                        <span><i class="fas fa-sticky-note me-2"></i>Notes & Terms</span>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (!empty($invoice['payment_terms'])): ?>
                            <div class="mb-2"><strong class="text-muted small">Payment Terms:</strong>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($invoice['payment_terms'] ?? '')) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($invoice['notes'])): ?>
                            <div><strong class="text-muted small">Notes:</strong>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($invoice['notes'] ?? '')) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card aps-cp-card mb-4">
                <div class="card-header aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-2"></i>Invoice Info</span>
                </div>
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted d-block">Invoice #<?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></small>
                    <small class="text-muted d-block">Created: <?= date('d M Y H:i', strtotime($invoice['created_at'] ?? 'now')) ?></small>
                    <small class="text-muted d-block">Updated: <?= date('d M Y H:i', strtotime($invoice['updated_at'] ?? 'now')) ?></small>
                    <?php if (!empty($invoice['paid_at'])): ?>
                        <small class="text-success d-block">Paid: <?= date('d M Y H:i', strtotime($invoice['paid_at'])) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($invoice['sent_at'])): ?>
                        <small class="text-info d-block">Sent: <?= date('d M Y H:i', strtotime($invoice['sent_at'])) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($invoice['booking_id'])): ?>
                <div class="card aps-cp-card mb-4">
                    <div class="card-body text-center">
                        <small class="text-muted d-block">Linked Booking</small>
                        <strong>#<?= (int)$invoice['booking_id'] ?></strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
