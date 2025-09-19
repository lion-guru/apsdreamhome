function foreclosePlan(emiPlanId) {
    Swal.fire({
        title: 'Foreclose EMI Plan',
        text: 'Are you sure you want to foreclose this EMI plan? A 2% foreclosure charge will be applied on the remaining principal.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, foreclose it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show payment method selection
            Swal.fire({
                title: 'Select Payment Method',
                input: 'select',
                inputOptions: {
                    'cash': 'Cash',
                    'bank_transfer': 'Bank Transfer',
                    'cheque': 'Cheque',
                    'upi': 'UPI'
                },
                inputPlaceholder: 'Select payment method',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Please select a payment method'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'ajax/foreclose_emi.php',
                        method: 'POST',
                        data: {
                            emi_plan_id: emiPlanId,
                            payment_method: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Foreclosed!',
                                    'EMI plan has been foreclosed successfully.',
                                    'success'
                                ).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Failed to foreclose EMI plan',
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'Failed to foreclose EMI plan',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    });
}

$(document).ready(function() {
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

    // Initialize select2 for property selection
    $('#property').select2({
        ajax: {
            url: 'ajax/get_properties.php',
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
        placeholder: 'Search for a property',
        minimumInputLength: 1
    });

    // Initialize DataTable
    var emiPlansTable = $('#emiPlansTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/get_emi_plans.php',
            type: 'POST'
        },
        columns: [
            { data: 'customer' },
            { data: 'property' },
            { data: 'total_amount' },
            { data: 'emi_amount' },
            { data: 'tenure' },
            { data: 'start_date' },
            { data: 'status' },
            { data: 'actions' }
        ],
        order: [[5, 'desc']]
    });

    // Load EMI statistics
    loadEMIStats();
    
    // Refresh stats every 5 minutes
    setInterval(loadEMIStats, 300000);

    // Calculate EMI on form input change
    $('#totalAmount, #interestRate, #tenureMonths, #downPayment').on('change', calculateEMI);

    // Handle EMI plan form submission
    $('#addEMIPlanForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/add_emi_plan.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addEMIPlanModal').modal('hide');
                    emiPlansTable.draw();
                    showAlert('success', 'EMI plan created successfully');
                    $('#addEMIPlanForm')[0].reset();
                    loadEMIStats();
                } else {
                    showAlert('error', response.message || 'Failed to create EMI plan');
                }
            },
            error: function() {
                showAlert('error', 'Failed to create EMI plan');
            }
        });
    });
});

function loadEMIStats() {
    $.ajax({
        url: 'ajax/get_emi_stats.php',
        method: 'GET',
        success: function(response) {
            if(response.success) {
                $('#activeEMICount').text(response.data.active_count);
                $('#monthlyEMICollection').text(response.data.monthly_collection);
                $('#pendingEMICount').text(response.data.pending_count);
                $('#overdueEMICount').text(response.data.overdue_count);
            }
        },
        error: function() {
            console.error('Failed to load EMI statistics');
        }
    });
}

function calculateEMI() {
    var totalAmount = parseFloat($('#totalAmount').val()) || 0;
    var downPayment = parseFloat($('#downPayment').val()) || 0;
    var interestRate = parseFloat($('#interestRate').val()) || 0;
    var tenureMonths = parseInt($('#tenureMonths').val()) || 0;

    if (totalAmount > 0 && interestRate > 0 && tenureMonths > 0) {
        var principal = totalAmount - downPayment;
        var monthlyRate = interestRate / (12 * 100);
        var emi = principal * monthlyRate * Math.pow(1 + monthlyRate, tenureMonths) / (Math.pow(1 + monthlyRate, tenureMonths) - 1);
        $('#calculatedEMI').text('₹' + emi.toFixed(2));
    } else {
        $('#calculatedEMI').text('₹0.00');
    }
}

function viewEMIPlan(id) {
    window.location.href = 'view_emi_plan.php?id=' + id;
}

function editEMIPlan(id) {
    window.location.href = 'edit_emi_plan.php?id=' + id;
}

function deleteEMIPlan(id) {
    if (confirm('Are you sure you want to delete this EMI plan? This action cannot be undone.')) {
        $.ajax({
            url: 'ajax/delete_emi_plan.php',
            method: 'POST',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    $('#emiPlansTable').DataTable().draw();
                    showAlert('success', 'EMI plan deleted successfully');
                    loadEMIStats();
                } else {
                    showAlert('error', response.message || 'Failed to delete EMI plan');
                }
            },
            error: function() {
                showAlert('error', 'Failed to delete EMI plan');
            }
        });
    }
}

function showAlert(type, message) {
    toastr[type](message);
}
