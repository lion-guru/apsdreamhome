<?php
// Page title is set in the controller, but can be overridden here if needed
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold">Payment Management</h1>
    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
        <i class="fas fa-plus me-1"></i> Add New Payment
    </button>
</div>

<!-- Payment Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="card-title mb-0 fw-bold text-primary">Payment Filters</h5>
    </div>
    <div class="card-body">
        <form id="paymentFilters" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold" for="dateRange">Date Range</label>
                <input type="text" class="form-control shadow-sm" id="dateRange" name="dateRange">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="paymentStatus">Payment Status</label>
                <select class="form-control shadow-sm" id="paymentStatus" name="status">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="paymentType">Payment Type</label>
                <select class="form-control shadow-sm" id="paymentType" name="type">
                    <option value="">All</option>
                    <option value="booking">Booking</option>
                    <option value="installment">Installment</option>
                    <option value="full">Full Payment</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="card-title mb-0 fw-bold text-primary">All Payments</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="paymentsTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4">Date</th>
                        <th class="border-0">Transaction ID</th>
                        <th class="border-0">Customer</th>
                        <th class="border-0">Type</th>
                        <th class="border-0">Amount</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 px-4">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addPaymentModalLabel">Add New Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body p-4">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="customer">Customer</label>
                                <select class="form-control shadow-sm" id="customer" name="customer_id" required style="width: 100%;">
                                    <option value="">Select Customer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="amount">Amount</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="paymentTypeModal">Payment Type</label>
                                <select class="form-control shadow-sm" id="paymentTypeModal" name="payment_type" required>
                                    <option value="booking">Booking</option>
                                    <option value="installment">Installment</option>
                                    <option value="full">Full Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="paymentMethod">Payment Method</label>
                                <select class="form-control shadow-sm" id="paymentMethod" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="upi">UPI</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold" for="description">Description</label>
                                <textarea class="form-control shadow-sm" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<!-- View Payment Modal -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewPaymentBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- DateRangePicker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#dateRange').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            paymentsTable.draw();
        });

        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            paymentsTable.draw();
        });

        // Initialize select2 for customer selection
        $('#customer').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addPaymentModal'),
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

        // Initialize DataTable
        var paymentsTable = $('#paymentsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/admin/payments/data',
                type: 'GET',
                data: function(d) {
                    d.dateRange = $('#dateRange').val();
                    d.status = $('#paymentStatus').val();
                    d.type = $('#paymentType').val();
                }
            },
            columns: [{
                    data: 'payment_date'
                },
                {
                    data: 'transaction_id'
                },
                {
                    data: 'customer_name'
                },
                {
                    data: 'payment_type'
                },
                {
                    data: 'amount'
                },
                {
                    data: 'status'
                },
                {
                    data: 'actions'
                }
            ],
            order: [
                [0, 'desc']
            ],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Handle filter form submission
        $('#paymentFilters').on('submit', function(e) {
            e.preventDefault();
            paymentsTable.draw();
        });

        // Handle payment form submission
        $('#addPaymentForm').on('submit', function(e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            $.ajax({
                url: '/admin/payments/store',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#addPaymentModal').modal('hide');
                        paymentsTable.draw();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Payment added successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#addPaymentForm')[0].reset();
                        $('#customer').val(null).trigger('change');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message || 'Failed to add payment'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to add payment'
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });

    function viewPayment(id) {
        var modal = new bootstrap.Modal(document.getElementById('viewPaymentModal'));
        modal.show();

        $('#viewPaymentBody').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: '/admin/payments/show/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    var p = response.data;
                    var html = `
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%">Transaction ID</th>
                                <td>${p.transaction_id || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>${new Date(p.payment_date).toLocaleString()}</td>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td>${p.customer_name || 'Unknown'} (${p.customer_mobile || 'N/A'})</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>₹${parseFloat(p.amount).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <th>Payment Type</th>
                                <td>${p.payment_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</td>
                            </tr>
                            <tr>
                                <th>Payment Method</th>
                                <td>${(p.payment_method || 'N/A').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-${getStatusColor(p.status)}">${p.status.toUpperCase()}</span></td>
                            </tr>
                            <tr>
                                <th>Description/Notes</th>
                                <td>${p.notes || p.description || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Created By</th>
                                <td>${p.created_by_name || 'System'}</td>
                            </tr>
                        </table>
                        <div class="text-center mt-3">
                            <a href="/admin/payments/receipt/${p.id}" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-print me-1"></i> Print Receipt
                            </a>
                        </div>
                    `;
                    $('#viewPaymentBody').html(html);
                } else {
                    $('#viewPaymentBody').html('<div class="alert alert-danger">Failed to load payment details.</div>');
                }
            },
            error: function() {
                $('#viewPaymentBody').html('<div class="alert alert-danger">Error fetching data.</div>');
            }
        });
    }

    function getStatusColor(status) {
        switch (status) {
            case 'completed':
                return 'success';
            case 'pending':
                return 'warning';
            case 'failed':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    function deletePayment(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/payments/delete/' + id,
                    method: 'POST',
                    data: {
                        csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#paymentsTable').DataTable().draw();
                            Swal.fire(
                                'Deleted!',
                                'Payment has been deleted.',
                                'success'
                            );
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message || 'Failed to delete payment'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Failed to delete payment.',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>