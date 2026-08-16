<?php $inv = $invoice ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>GST: <?= htmlspecialchars($inv['invoice_number'] ?? '') ?></h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/gst" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <?php if (!empty($inv['e_invoice_number'])): ?>
                <span class="badge bg-success fs-6 ms-2">E-Invoice: <?= htmlspecialchars($inv['e_invoice_number'] ?? '') ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Invoice Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Invoice #</th><td><strong><?= htmlspecialchars($inv['invoice_number'] ?? '') ?></strong></td></tr>
                        <tr><th>Date</th><td><?= htmlspecialchars($inv['invoice_date'] ?? $inv['created_at'] ?? '') ?></td></tr>
                        <tr><th>Due Date</th><td><?= htmlspecialchars($inv['due_date'] ?? '—') ?></td></tr>
                        <tr><th>Client</th><td><?= htmlspecialchars($inv['client_name'] ?? $inv['user_name'] ?? '') ?></td></tr>
                        <tr><th>Client GSTIN</th><td><code><?= htmlspecialchars($inv['gstin'] ?? '—') ?></code></td></tr>
                        <tr><th>Total Amount</th><td><strong>₹<?= number_format($inv['total_amount'] ?? 0, 2) ?></strong></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-<?= match($inv['status'] ?? 'draft') { 'paid' => 'success', 'sent' => 'info', 'draft' => 'secondary', 'cancelled' => 'danger', default => 'secondary' } ?>"><?= ucfirst($inv['status'] ?? 'draft') ?></span></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-calculator me-2"></i>GST Breakdown</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>GST Type</th><td><span class="badge bg-info"><?= strtoupper(str_replace('_', '/', $inv['gst_type'] ?? '—')) ?></span></td></tr>
                        <tr><th>GST Rate</th><td><?= ($inv['gst_rate'] ?? '—') ?>%</td></tr>
                        <tr><th>CGST Amount</th><td>₹<?= number_format($inv['cgst_amount'] ?? 0, 2) ?></td></tr>
                        <tr><th>SGST Amount</th><td>₹<?= number_format($inv['sgst_amount'] ?? 0, 2) ?></td></tr>
                        <tr><th>IGST Amount</th><td>₹<?= number_format($inv['igst_amount'] ?? 0, 2) ?></td></tr>
                        <tr><th>HSN Code</th><td><code><?= htmlspecialchars($inv['hsn_code'] ?? '—') ?></code></td></tr>
                        <tr><th>Place of Supply</th><td><?= htmlspecialchars($inv['place_of_supply'] ?? '—') ?></td></tr>
                    </table></div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>E-Invoicing</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>E-Invoice Number</th><td><?= htmlspecialchars($inv['e_invoice_number'] ?? '—') ?></td></tr>
                        <tr><th>E-Way Bill</th><td><?= htmlspecialchars($inv['e_way_bill'] ?? '—') ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
