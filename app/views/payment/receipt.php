<?php $pageTitle = $pageTitle ?? $page_title ?? 'Payment Receipt'; $receipt = $receipt_data ?? []; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
                    <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Payment Receipt</h4>
                    <button onclick="window.print()" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i>Print</button>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h5>APS Dream Home</h5>
                        <p class="text-muted small">Payment Confirmation</p>
                    </div>
                    <div class="table-responsive"><table class="table table-bordered table-responsive">
                        <tr><th class="bg-light" class="style-83841">Order ID</th><td>#<?= h($receipt['order_id'] ?? 'N/A') ?></td></tr>
                        <tr><th class="bg-light">Customer</th><td><?= h($receipt['customer_name'] ?? 'N/A') ?></td></tr>
                        <tr><th class="bg-light">Property</th><td><?= h($receipt['property_title'] ?? 'N/A') ?></td></tr>
                        <tr><th class="bg-light">Amount</th><td>₹<?= number_format($receipt['amount'] ?? 0) ?></td></tr>
                        <tr><th class="bg-light">Payment Date</th><td><?= h($receipt['payment_date'] ?? date('Y-m-d H:i:s')) ?></td></tr>
                        <tr><th class="bg-light">Transaction ID</th><td><code><?= h($receipt['transaction_id'] ?? 'N/A') ?></code></td></tr>
                        <tr><th class="bg-light">Payment Method</th><td><?= h(ucfirst($receipt['payment_method'] ?? 'N/A')) ?></td></tr>
                    </table></div>
                    <div class="text-center mt-4">
                        <a href="<?= BASE_URL ?>payment/history" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to History</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
