<?php
$page_title = 'Add New Booking';
$active_page = 'bookings';
include APP_PATH . '/views/admin/layouts/header.php';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Booking</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/bookings" class="btn btn-secondary">
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
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-plus-circle"></i> Booking Information
        </h5>
    </div>
    <div class="card-body">
        <form action="/admin/bookings/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="row">
                <!-- Property Selection -->
                <div class="col-md-6 mb-3">
                    <label for="property_id" class="form-label">Property *</label>
                    <select class="form-select" id="property_id" name="property_id" required>
                        <option value="">Select Property</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?= $property['id'] ?>">
                                <?= htmlspecialchars($property['title'] ?? '') ?> - ₹<?= number_format(floatval($property['price'] ?? 0), 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Customer Selection -->
                <div class="col-md-6 mb-3">
                    <label for="customer_type" class="form-label">Customer Type *</label>
                    <select class="form-select" id="customer_type" name="customer_type" required onchange="toggleCustomerFields()">
                        <option value="existing">Existing Customer</option>
                        <option value="new">New Customer</option>
                    </select>
                </div>
            </div>

            <!-- Existing Customer Section -->
            <div id="existing_customer_section" class="row">
                <div class="col-md-6 mb-3">
                    <label for="customer_id" class="form-label">Select Customer *</label>
                    <select class="form-select" id="customer_id" name="customer_id">
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>">
                                <?= htmlspecialchars($customer['name'] ?? '') ?> (<?= htmlspecialchars($customer['email'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- New Customer Section -->
            <div id="new_customer_section" class="row" style="display: none;">
                <div class="col-md-6 mb-3">
                    <label for="new_customer_name" class="form-label">Customer Name *</label>
                    <input type="text" class="form-control" id="new_customer_name" name="new_customer_name"
                        placeholder="Enter customer name" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="new_customer_email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="new_customer_email" name="new_customer_email"
                        placeholder="customer@example.com">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="new_customer_phone" class="form-label">Phone Number *</label>
                    <input type="tel" class="form-control" id="new_customer_phone" name="new_customer_phone"
                        placeholder="+91 9876543210" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="new_customer_address" class="form-label">Address</label>
                    <textarea class="form-control" id="new_customer_address" name="new_customer_address"
                        rows="2" placeholder="Enter customer address"></textarea>
                </div>

                <!-- Associate Assignment for New Customer -->
                <div class="col-md-6 mb-3">
                    <label for="new_customer_associate_id" class="form-label">
                        <i class="fas fa-user-tie"></i> Assign Associate
                        <small class="text-muted">(Optional - for commission tracking)</small>
                    </label>
                    <select class="form-select" id="new_customer_associate_id" name="associate_id">
                        <option value="">No Associate (Direct Booking)</option>
                        <?php foreach ($associates as $associate): ?>
                            <option value="<?= $associate['id'] ?>">
                                <?= htmlspecialchars($associate['name']) ?>
                                <?php if (!empty($associate['mlm_rank'])): ?>
                                    - <?= htmlspecialchars($associate['mlm_rank']) ?>
                                <?php endif; ?>
                                (<?= htmlspecialchars($associate['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        Assign an associate to track commissions and team performance
                    </div>
                </div>

                <!-- Booking Date for New Customer -->
                <div class="col-md-6 mb-3">
                    <label for="new_booking_date" class="form-label">Booking Date *</label>
                    <input type="date" class="form-control" id="new_booking_date" name="booking_date"
                        value="<?= date('Y-m-d') ?>" required>
                </div>

                <!-- Booking Amount for New Customer -->
                <div class="col-md-6 mb-3">
                    <label for="new_amount" class="form-label">Booking Amount (₹) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="new_amount" name="amount"
                            step="0.01" min="0" required placeholder="0.00">
                    </div>
                </div>

                <!-- Status for New Customer -->
                <div class="col-md-6 mb-3">
                    <label for="new_status" class="form-label">Booking Status</label>
                    <select class="form-select" id="new_status" name="status">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Commission Preview -->
            <div id="commission_preview" class="alert alert-info d-none">
                <h6><i class="fas fa-calculator"></i> Commission Preview</h6>
                <div id="commission_details"></div>
            </div>

            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Booking
                    </button>
                    <a href="/admin/bookings" class="btn btn-secondary ms-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const propertySelect = document.getElementById('property_id');
        const amountInput = document.getElementById('amount');
        const associateSelect = document.getElementById('associate_id');
        const commissionPreview = document.getElementById('commission_preview');
        const commissionDetails = document.getElementById('commission_details');

        // Auto-fill amount when property is selected
        propertySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const priceText = selectedOption.textContent;
            const priceMatch = priceText.match(/₹([\d,]+\.?\d*)/);

            if (priceMatch) {
                const price = parseFloat(priceMatch[1].replace(/,/g, ''));
                amountInput.value = price.toFixed(2);
                updateCommissionPreview();
            }
        });

        // Update commission preview when associate or amount changes
        associateSelect.addEventListener('change', updateCommissionPreview);
        amountInput.addEventListener('input', updateCommissionPreview);

        function updateCommissionPreview() {
            const associateId = associateSelect.value;
            const amount = parseFloat(amountInput.value) || 0;

            if (associateId && amount > 0) {
                // Calculate commission (5% example)
                const commissionRate = 0.05;
                const commissionAmount = amount * commissionRate;

                commissionDetails.innerHTML = `
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Booking Amount:</strong> ₹${amount.toFixed(2)}
                        </div>
                        <div class="col-md-4">
                            <strong>Commission Rate:</strong> ${(commissionRate * 100)}%
                        </div>
                        <div class="col-md-4">
                            <strong>Commission:</strong> ₹${commissionAmount.toFixed(2)}
                        </div>
                    </div>
                `;
                commissionPreview.classList.remove('d-none');
            } else {
                commissionPreview.classList.add('d-none');
            }
        }

        // Handle new customer fields for new customer section
        const newCustomerSection = document.getElementById('new_customer_section');
        const newAmountInput = document.getElementById('new_amount');
        const newAssociateSelect = document.getElementById('new_customer_associate_id');

        // Auto-fill amount for new customer section
        propertySelect.addEventListener('change', function() {
            if (newAmountInput) {
                const selectedOption = this.options[this.selectedIndex];
                const priceText = selectedOption.textContent;
                const priceMatch = priceText.match(/₹([\d,]+\.?\d*)/);

                if (priceMatch) {
                    const price = parseFloat(priceMatch[1].replace(/,/g, ''));
                    newAmountInput.value = price.toFixed(2);
                }
            }
        });
    });

    // Toggle customer fields based on customer type
    function toggleCustomerFields() {
        const customerType = document.getElementById('customer_type').value;
        const existingSection = document.getElementById('existing_customer_section');
        const newSection = document.getElementById('new_customer_section');

        if (customerType === 'existing') {
            existingSection.style.display = 'block';
            newSection.style.display = 'none';
            // Make existing customer fields required
            document.getElementById('customer_id').setAttribute('required', 'required');
        } else {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            // Remove required from existing customer fields
            document.getElementById('customer_id').removeAttribute('required');
        }
    }

    // Form validation before submission
    document.querySelector('form').addEventListener('submit', function(e) {
        const customerType = document.getElementById('customer_type').value;

        if (customerType === 'new') {
            const customerName = document.getElementById('new_customer_name').value.trim();
            const customerPhone = document.getElementById('new_customer_phone').value.trim();

            if (!customerName || !customerPhone) {
                e.preventDefault();
                alert('Please fill in all required customer details (Name and Phone)');
                return false;
            }

            // Validate phone number
            const phoneRegex = /^[+]?[\d\s\-\(\)]+$/;
            if (!phoneRegex.test(customerPhone)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                return false;
            }
        }
    });
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>