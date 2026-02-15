<?php
// app/views/admin/payments/edit.php
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold">Edit Payment</h1>
    <a href="/admin/payments" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Payments
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form id="editPaymentForm" action="/admin/payments/update/<?php echo $payment['id']; ?>" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="customer">Customer <span class="text-danger">*</span></label>
                        <select class="form-control shadow-sm" id="customer" name="customer_id" required style="width: 100%;">
                            <?php if (!empty($payment['customer_id'])): ?>
                                <option value="<?php echo $payment['customer_id']; ?>" selected>
                                    <?php echo htmlspecialchars($payment['customer_name'] . ' (' . ($payment['customer_mobile'] ?? 'N/A') . ')'); ?>
                                </option>
                            <?php else: ?>
                                <option value="">Select Customer</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="amount">Amount <span class="text-danger">*</span></label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="amount" name="amount" required step="0.01" min="0" value="<?php echo htmlspecialchars($payment['amount']); ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="paymentType">Payment Type <span class="text-danger">*</span></label>
                        <select class="form-control shadow-sm" id="paymentType" name="payment_type" required>
                            <option value="booking" <?php echo ($payment['payment_type'] == 'booking') ? 'selected' : ''; ?>>Booking</option>
                            <option value="installment" <?php echo ($payment['payment_type'] == 'installment') ? 'selected' : ''; ?>>Installment</option>
                            <option value="full" <?php echo ($payment['payment_type'] == 'full_payment' || $payment['payment_type'] == 'full') ? 'selected' : ''; ?>>Full Payment</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="paymentMethod">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-control shadow-sm" id="paymentMethod" name="payment_method" required>
                            <option value="cash" <?php echo ($payment['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                            <option value="bank_transfer" <?php echo ($payment['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                            <option value="cheque" <?php echo ($payment['payment_method'] == 'cheque') ? 'selected' : ''; ?>>Cheque</option>
                            <option value="upi" <?php echo ($payment['payment_method'] == 'upi') ? 'selected' : ''; ?>>UPI</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="description">Description</label>
                        <textarea class="form-control shadow-sm" id="description" name="description" rows="3"><?php echo htmlspecialchars($payment['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i> Transaction ID: <strong><?php echo htmlspecialchars($payment['transaction_id']); ?></strong> | Created on: <?php echo date('d M Y, h:i A', strtotime($payment['payment_date'])); ?>
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-light me-md-2" onclick="history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Update Payment</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize select2 for customer selection
    $('#customer').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '/admin/payments/customers',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: data.more
                    }
                };
            },
            cache: true
        },
        placeholder: 'Search for a customer',
        minimumInputLength: 1
    });

    // Handle form submission
    $('#editPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Payment updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/admin/payments';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message || 'Failed to update payment'
                    });
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update payment'
                });
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
