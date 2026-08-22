<?php
$page_title = $page_title ?? 'Record Collection - APS Dream Home';
$today = $today ?? date('Y-m-d');
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container-fluid px-4">
    <div class="mb-4">
        <a href="<?php echo e($base); ?>/associate/collections" class="text-decoration-none text-success small">
            <i class="fas fa-arrow-left me-1"></i>Back to Collections
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-hand-holding-usd text-success me-2"></i>Record Cash Collection</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?php echo e($base); ?>/associate/collections/store" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">

                        <div class="mb-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Rajesh Kumar">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" required step="0.01" min="1" placeholder="e.g. 50000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Collection Date</label>
                                <input type="date" name="collection_date" class="form-control" value="<?php echo htmlspecialchars($today ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference Number (Cheque/UTR No.)</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Optional — cheque number or UTR">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Receipt Photo</label>
                            <input type="file" name="receipt_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Upload a photo of the payment receipt or customer signature (max 5MB)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any notes about this collection"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle me-1"></i>Submit Collection
                            </button>
                            <a href="<?php echo e($base); ?>/associate/collections" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
