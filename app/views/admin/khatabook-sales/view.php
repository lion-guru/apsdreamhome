<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-receipt me-2"></i>Sale Record #<?= htmlspecialchars($sale['id']) ?></h1>
        <a href="<?= BASE_URL ?>/admin/khatabook-sales" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0">Transaction Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-bordered">
                        <tr><th style="width:200px">Transaction Date</th><td><?= htmlspecialchars($sale['transaction_date']) ?></td></tr>
                        <tr><th>Customer Name</th><td><strong><?= htmlspecialchars($sale['customer_name']) ?></strong></td></tr>
                        <tr><th>Phone</th><td><?= htmlspecialchars($sale['customer_phone'] ?? '-') ?></td></tr>
                        <tr><th>Address</th><td><?= nl2br(htmlspecialchars($sale['customer_address'] ?? '-')) ?></td></tr>
                        <tr><th>Item Description</th><td><?= nl2br(htmlspecialchars($sale['item_description'] ?? '-')) ?></td></tr>
                        <tr><th>Quantity</th><td><?= number_format($sale['quantity'], 2) ?></td></tr>
                        <tr><th>Rate</th><td>₹<?= number_format($sale['rate'], 2) ?></td></tr>
                        <tr><th>Total Amount</th><td><h4 class="text-success mb-0">₹<?= number_format($sale['amount'], 2) ?></h4></td></tr>
                        <tr><th>Payment Method</th><td><?= htmlspecialchars($sale['payment_method'] ?? '-') ?></td></tr>
                        <tr><th>Reference No</th><td><?= htmlspecialchars($sale['reference_no'] ?? '-') ?></td></tr>
                        <tr><th>Notes</th><td><?= nl2br(htmlspecialchars($sale['notes'] ?? '-')) ?></td></tr>
                        <tr><th>Import Batch</th><td><code><?= htmlspecialchars($sale['import_batch'] ?? '-') ?></code></td></tr>
                        <tr><th>Imported At</th><td><?= htmlspecialchars($sale['imported_at'] ?? '-') ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/khatabook-sales/delete/<?= $sale['id'] ?>" onsubmit="return confirm('Delete this record?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button class="btn btn-danger w-100 mb-2"><i class="fas fa-trash me-1"></i>Delete Record</button>
                    </form>
                    <a href="<?= BASE_URL ?>/admin/khatabook-sales" class="btn btn-outline-secondary w-100"><i class="fas fa-list me-1"></i>All Records</a>
                </div>
            </div>
        </div>
    </div>
</div>
