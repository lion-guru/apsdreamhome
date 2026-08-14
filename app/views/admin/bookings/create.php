<?php
$page_title = 'Add New Booking';
$active_page = 'bookings';
?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Booking</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/bookings" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Bookings
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<!-- Booking Form -->
<div class="card aps-cp-card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
        <h5 class="card-title mb-0 text-primary fw-bold">
            <i class="fas fa-file-signature me-2"></i> Booking Information
        </h5>
    </div>
    <div class="card-body">
        <form action="<?php echo BASE_URL; ?>/admin/bookings" method="POST" id="bookingForm" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="row g-4">
                <!-- Plot Selection -->
                <div class="col-md-6">
                    <div class="form-floating">
                        <select class="form-select" id="property_id" name="plot_id" required>
                            <option value="" selected disabled>Select a plot...</option>
                            <?php foreach ($plots ?? $properties ?? [] as $plot): ?>
                                <option value="<?= $plot['id'] ?>" data-price="<?= $plot['total_price'] ?? $plot['price'] ?? 0 ?>">
                                    <?= htmlspecialchars($plot['colony_name'] ?? '') ?> - Plot <?= htmlspecialchars($plot['plot_number'] ?? $plot['title'] ?? '') ?> 
                                    (â‚¹<?= number_format(floatval($plot['total_price'] ?? $plot['price'] ?? 0), 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="property_id">Property / Plot <span class="text-danger">*</span></label>
                        <div class="invalid-feedback">Please select a property.</div>
                    </div>
                </div>

                <!-- Associate / Agent Assignment -->
                <div class="col-md-6">
                    <div class="form-floating">
                        <select class="form-select" id="associate_id" name="associate_id">
                            <option value="">No Associate (Direct Booking)</option>
                            <?php 
                            // Try to get associates, fallback to users if needed
                            $agentList = $associates ?? $users ?? [];
                            foreach ($agentList as $associate): 
                                // Only show users who are agents/associates if we're using the generic users array
                                if (isset($associate['role']) && !in_array(strtolower($associate['role']), ['agent', 'associate', 'telecaller'])) continue;
                            ?>
                                <option value="<?= $associate['id'] ?>">
                                    <?= htmlspecialchars($associate['name']) ?> 
                                    <?php if (!empty($associate['role'])): ?>
                                        (<?= ucfirst(htmlspecialchars($associate['role'])) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="associate_id">Assign Associate / Agent</label>
                        <div id="associate_resolution" class="form-text mt-1 text-primary fw-bold" class="style-54390">
                            <i class="fas fa-check-circle"></i> <span id="associate_name_display"></span>
                        </div>
                        <div class="form-text text-muted small mt-1" id="associate_help_text">
                            <i class="fas fa-info-circle"></i> Assign an associate to track commissions
                        </div>
                    </div>
                </div>

                <!-- Customer Type -->
                <div class="col-md-12">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <label class="form-label fw-bold mb-3">Customer Selection <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="customer_type" id="type_existing" value="existing" checked onchange="toggleCustomerFields()">
                                    <label class="form-check-label" for="type_existing">
                                        Existing Customer
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="customer_type" id="type_new" value="new" onchange="toggleCustomerFields()">
                                    <label class="form-check-label" for="type_new">
                                        New Customer
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Customer Section -->
                <div id="existing_customer_section" class="col-md-12">
                    <div class="form-floating">
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="" selected disabled>Select an existing customer...</option>
                            <?php foreach ($customers ?? $users ?? [] as $customer): ?>
                                <?php if (isset($customer['role']) && !in_array(strtolower($customer['role']), ['customer', 'user'])) continue; ?>
                                <option value="<?= $customer['id'] ?>">
                                    <?= htmlspecialchars($customer['name'] ?? '') ?> 
                                    (<?= htmlspecialchars($customer['phone'] ?? $customer['email'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="customer_id">Select Customer <span class="text-danger">*</span></label>
                        <div class="invalid-feedback">Please select a customer.</div>
                    </div>
                </div>

                <!-- New Customer Section -->
                <div id="new_customer_section" class="col-md-12" class="style-54390">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="new_customer_name" name="new_customer_name" placeholder="John Doe">
                                <label for="new_customer_name">Full Name <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="new_customer_phone" name="new_customer_phone" placeholder="9876543210">
                                <label for="new_customer_phone">Phone Number <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="new_customer_email" name="new_customer_email" placeholder="john@example.com">
                                <label for="new_customer_email">Email Address <span class="text-muted">(Optional)</span></label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="col-md-12 mt-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Financial & Status Details</h6>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="booking_date" name="booking_date" value="<?= date('Y-m-d') ?>" required>
                        <label for="booking_date">Booking Date <span class="text-danger">*</span></label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="total_plot_value" name="total_plot_value" step="0.01" min="0" required placeholder="0.00" readonly>
                        <label for="total_plot_value">Total Plot Value (â‚¹) <span class="text-danger">*</span></label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="booking_amount" name="booking_amount" step="0.01" min="0" required placeholder="0.00">
                        <label for="booking_amount">Booking/Token Amount Paid (â‚¹) <span class="text-danger">*</span></label>
                    </div>
                </div>

                <!-- Commission Preview -->
                <div class="col-md-12">
                    <div id="commission_preview" class="alert alert-success d-none shadow-sm border-0">
                        <h6 class="alert-heading fw-bold mb-2"><i class="fas fa-calculator me-1"></i> Commission Estimation</h6>
                        <div id="commission_details" class="small"></div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="col-md-12">
                    <div class="form-floating">
                        <textarea class="form-control" id="notes" name="notes" class="style-48147" placeholder="Any special remarks or terms..."></textarea>
                        <label for="notes">Booking Notes / Remarks (Optional)</label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-5 pt-3 border-top text-end">
                <a href="<?= BASE_URL ?>/admin/bookings" class="btn btn-light me-2 px-4 shadow-sm">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="fas fa-check-circle me-1"></i> Create Booking
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const propertySelect = document.getElementById('property_id');
        const totalValueInput = document.getElementById('total_plot_value');
        const bookingAmountInput = document.getElementById('booking_amount');
        const associateSelect = document.getElementById('associate_id');
        const commissionPreview = document.getElementById('commission_preview');
        const commissionDetails = document.getElementById('commission_details');

        // Auto-fill total value when property is selected
        propertySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const price = parseFloat(selectedOption.dataset.price || 0);
                totalValueInput.value = price.toFixed(2);
                // Pre-fill token amount as 10%
                bookingAmountInput.value = (price * 0.1).toFixed(2);
                updateCommissionPreview();
            } else {
                totalValueInput.value = '';
                bookingAmountInput.value = '';
                commissionPreview.classList.add('d-none');
            }
        });

        // Update commission preview when associate or amount changes
        associateSelect.addEventListener('change', function() {
            updateCommissionPreview();
            resolveAssociateName();
        });
        totalValueInput.addEventListener('input', updateCommissionPreview);

        function resolveAssociateName() {
            const associateId = associateSelect.value;
            const resDiv = document.getElementById('associate_resolution');
            const nameDisplay = document.getElementById('associate_name_display');
            const helpText = document.getElementById('associate_help_text');

            if (!associateId) {
                resDiv.style.display = 'none';
                helpText.style.display = 'block';
                return;
            }

            fetch('<?= BASE_URL ?>/api/user/resolve-sponsor?ref=' + associateId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        nameDisplay.textContent = data.name + ' (' + data.role + ')';
                        resDiv.style.display = 'block';
                        helpText.style.display = 'none';
                    } else {
                        resDiv.style.display = 'none';
                        helpText.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error resolving sponsor:', error));
        }

        function updateCommissionPreview() {
            const associateId = associateSelect.value;
            const amount = parseFloat(totalValueInput.value) || 0;

            if (associateId && amount > 0) {
                // Calculate estimated commission (Example: 5% of total value)
                const commissionRate = 0.05;
                const commissionAmount = amount * commissionRate;

                commissionDetails.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <span class="text-muted d-block">Plot Value</span>
                            <strong>â‚¹${amount.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block">Est. Base Rate</span>
                            <strong>${(commissionRate * 100)}%</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block">Est. Commission</span>
                            <strong class="text-success">â‚¹${commissionAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong>
                        </div>
                    </div>
                    <div class="mt-2 text-muted" class="style-11723">
                        * Note: Actual commission will be calculated automatically by the MLM Engine based on the Associate's rank and current plan.
                    </div>
                `;
                commissionPreview.classList.remove('d-none');
            } else {
                commissionPreview.classList.add('d-none');
            }
        }
        
        // Form Validation Bootstrap
        const form = document.getElementById('bookingForm');
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Custom validation for New Customer
            const isNew = document.getElementById('type_new').checked;
            if (isNew) {
                const name = document.getElementById('new_customer_name');
                const phone = document.getElementById('new_customer_phone');
                
                if (!name.value.trim()) {
                    name.classList.add('is-invalid');
                    isValid = false;
                } else {
                    name.classList.remove('is-invalid');
                }
                
                if (!phone.value.trim() || !/^\d{10}$/.test(phone.value.replace(/\D/g,''))) {
                    phone.classList.add('is-invalid');
                    isValid = false;
                } else {
                    phone.classList.remove('is-invalid');
                }
            }
            
            if (!form.checkValidity() || !isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Toggle customer fields based on customer type
    function toggleCustomerFields() {
        const isNew = document.getElementById('type_new').checked;
        const existingSection = document.getElementById('existing_customer_section');
        const newSection = document.getElementById('new_customer_section');
        const customerId = document.getElementById('customer_id');

        if (isNew) {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            customerId.removeAttribute('required');
            document.getElementById('new_customer_name').setAttribute('required', 'required');
            document.getElementById('new_customer_phone').setAttribute('required', 'required');
        } else {
            existingSection.style.display = 'block';
            newSection.style.display = 'none';
            customerId.setAttribute('required', 'required');
            document.getElementById('new_customer_name').removeAttribute('required');
            document.getElementById('new_customer_phone').removeAttribute('required');
        }

        // Initialize Select2 for searchable dropdowns
        if (typeof jQuery !== 'undefined') {
            $('#property_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select a plot...'
            });
            $('#associate_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'No Associate (Direct Booking)',
                allowClear: true
            });
            $('#customer_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select an existing customer...'
            });

            // Re-bind change events for Select2 elements
            $('#property_id').on('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                totalValueInput.value = price.toFixed(2);
                updateCommissionPreview();
            });

            $('#associate_id').on('change', function() {
                updateCommissionPreview();
                resolveAssociateName();
            });
        }
    });
</script>
<!-- jQuery and Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
