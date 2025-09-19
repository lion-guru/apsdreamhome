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
        ajax: {
            url: 'ajax/get_customers.php',
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
            url: 'ajax/get_payments.php',
            type: 'POST',
            data: function(d) {
                d.dateRange = $('#dateRange').val();
                d.status = $('#paymentStatus').val();
                d.type = $('#paymentType').val();
            }
        },
        columns: [
            { data: 'date' },
            { data: 'transaction_id' },
            { data: 'customer' },
            { data: 'type' },
            { data: 'amount' },
            { data: 'status' },
            { data: 'actions' }
        ],
        order: [[0, 'desc']]
    });

    // Handle filter form submission
    $('#paymentFilters').on('submit', function(e) {
        e.preventDefault();
        paymentsTable.draw();
    });

    // Handle payment form submission
    $('#addPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/add_payment.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addPaymentModal').modal('hide');
                    paymentsTable.draw();
                    showAlert('success', 'Payment added successfully');
                    $('#addPaymentForm')[0].reset();
                } else {
                    showAlert('error', response.message || 'Failed to add payment');
                }
            },
            error: function() {
                showAlert('error', 'Failed to add payment');
            }
        });
    });
});

function viewPayment(id) {
    window.location.href = 'view_payment.php?id=' + id;
}

function editPayment(id) {
    window.location.href = 'edit_payment.php?id=' + id;
}

function deletePayment(id) {
    if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
        $.ajax({
            url: 'ajax/delete_payment.php',
            method: 'POST',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    $('#paymentsTable').DataTable().draw();
                    showAlert('success', 'Payment deleted successfully');
                } else {
                    showAlert('error', response.message || 'Failed to delete payment');
                }
            },
            error: function() {
                showAlert('error', 'Failed to delete payment');
            }
        });
    }
}

function showAlert(type, message) {
    toastr[type](message);
}
