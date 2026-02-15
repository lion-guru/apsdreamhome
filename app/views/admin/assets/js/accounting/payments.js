$(document).ready(function() {
    // Global AJAX setup for CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        }
    });

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
        minimumInputLength: 1,
        dropdownParent: $('#addPaymentModal')
    });

    // Initialize DataTable
    var paymentsTable = $('#paymentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/get_payments.php',
            type: 'GET',
            data: function(d) {
                d.dateRange = $('#dateRange').val();
                d.status = $('#paymentStatus').val();
                d.type = $('#paymentType').val();
            }
        },
        columns: [
            { data: 'payment_date' },
            { data: 'transaction_id' },
            { data: 'customer_name' },
            { data: 'payment_type' },
            { data: 'amount' },
            { data: 'status' },
            { data: 'actions' }
        ],
        order: [[0, 'desc']],
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
            url: 'ajax/add_payment.php',
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
    window.location.href = 'accounting_view_payment.php?id=' + id;
}

function editPayment(id) {
    window.location.href = 'accounting_edit_payment.php?id=' + id;
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
                url: 'ajax/delete_transaction.php',
                method: 'POST',
                data: { 
                    id: id
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete payment'
                    });
                }
            });
        }
    });
}
