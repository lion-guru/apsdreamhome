<?php
// app/views/admin/payments/create.php
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold">Add New Payment</h1>
    <a href="/admin/payments" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Payments
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form id="addPaymentForm" action="/admin/payments/store" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="customer">Customer <span class="text-danger">*</span></label>
                        <select class="form-control shadow-sm" id="customer" name="customer_id" required style="width: 100%;">
                            <option value="">Select Customer</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="amount">Amount <span class="text-danger">*</span></label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="amount" name="amount" required step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="paymentType">Payment Type <span class="text-danger">*</span></label>
                        <select class="form-control shadow-sm" id="paymentType" name="payment_type" required>
                            <option value="booking">Booking</option>
                            <option value="installment">Installment</option>
                            <option value="full">Full Payment</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="paymentMethod">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-control shadow-sm" id="paymentMethod" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="description">Description</label>
                        <textarea class="form-control shadow-sm" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-light me-md-2" onclick="history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Save Payment</button>
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
    $('#addPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Payment added successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/admin/payments';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message || 'Failed to add payment'
                    });
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add payment'
                });
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
