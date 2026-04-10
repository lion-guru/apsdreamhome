<?php
$page_title = 'Edit Booking';
$active_page = 'bookings';
include APP_PATH . '/views/admin/layouts/header.php';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Booking</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/bookings/<?= $booking['id'] ?>" class="btn btn-outline-primary">
            <i class="fas fa-eye"></i> View Booking
        </a>
        <a href="/admin/bookings" class="btn btn-secondary ms-2">
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

<!-- Edit Booking Form -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-edit"></i> Edit Booking Information
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/bookings/<?= $booking['id'] ?>/update">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <!-- Booking Overview -->
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Booking Overview</h6>
                <p class="mb-0">
                    <strong>Booking Number:</strong> <?= htmlspecialchars($booking['booking_number'] ?? '') ?><br>
                    <strong>Created:</strong> <?= date('d F Y h:i A', strtotime($booking['created_at'])) ?>
                </p>
            </div>

            <div class="row">
                <!-- Property Selection -->
                <div class="col-md-6 mb-3">
                    <label for="property_id" class="form-label">Property *</label>
                    <select class="form-select" id="property_id" name="property_id" required onchange="updateAmount()">
                        <option value="">Select Property</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?= $property['id'] ?>"
                                <?= $property['id'] == $booking['property_id'] ? 'selected' : ''
                                ?> data-price="<?= $property['price'] ?>">
                                <?= htmlspecialchars($property['title'] ?? '') ?> - ₹<?= number_format(floatval($property['price'] ?? 0), 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Customer Selection -->
                <div class="col-md-6 mb-3">
                    <label for="customer_id" class="form-label">Customer *</label>
                    <select class="form-select" id="customer_id" name="customer_id" required>
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>"
                                <?= $customer['id'] == $booking['customer_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($customer['name'] ?? '') ?> (<?= htmlspecialchars($customer['email'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <!-- Associate Assignment -->
                <div class="col-md-6 mb-3">
                    <label for="associate_id" class="form-label">
                        <i class="fas fa-user-tie"></i> Assign Associate
                        <small class="text-muted">(Optional - for commission tracking)</small>
                    </label>
                    <select class="form-select" id="associate_id" name="associate_id">
                        <option value="">No Associate (Direct Booking)</option>
                        <?php foreach ($associates as $associate): ?>
                            <option value="<?= $associate['id'] ?>"
                                <?= $associate['id'] == $booking['associate_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($associate['name'] ?? '') ?>
                                (<?= htmlspecialchars($associate['email'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        Assign an associate to track commissions and team performance
                    </div>
                </div>

                <!-- Booking Date -->
                <div class="col-md-6 mb-3">
                    <label for="booking_date" class="form-label">Booking Date *</label>
                    <input type="date" class="form-control" id="booking_date" name="booking_date"
                        value="<?= $booking['booking_date'] ?>" required>
                </div>
            </div>

            <div class="row">
                <!-- Total Amount -->
                <div class="col-md-6 mb-3">
                    <label for="total_amount" class="form-label">Total Amount (₹) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="total_amount" name="total_amount"
                            step="0.01" min="0" value="<?= $booking['total_amount'] ?>" required>
                    </div>
                    <div class="form-text">
                        Amount will auto-fill when property is selected
                    </div>
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Booking Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $booking['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="completed" <?= $booking['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Notes -->
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"
                        placeholder="Add any notes about this booking"><?= htmlspecialchars($booking['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Status Change Warning -->
            <div id="statusWarning" class="alert alert-warning d-none">
                <h6><i class="fas fa-exclamation-triangle"></i> Status Change Warning</h6>
                <p class="mb-0">
                    Changing the booking status to <strong id="newStatusText"></strong> will:
                </p>
                <ul id="statusEffects">
                    <!-- Effects will be populated by JavaScript -->
                </ul>
            </div>

            <!-- Commission Preview -->
            <div id="commission_preview" class="alert alert-info">
                <h6><i class="fas fa-calculator"></i> Commission Preview</h6>
                <div id="commission_details">
                    <?php if ($booking['associate_id']): ?>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Booking Amount:</strong> ₹<?= number_format(floatval($booking['total_amount'] ?? 0), 2) ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Commission Rate:</strong> 5%
                            </div>
                            <div class="col-md-4">
                                <strong>Commission:</strong> ₹<?= number_format(floatval($booking['total_amount'] ?? 0) * 0.05, 2) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="mb-0">No commission will be generated (Direct Booking)</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Booking
                    </button>
                    <a href="/admin/bookings/<?= $booking['id'] ?>" class="btn btn-secondary ms-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <a href="/admin/bookings/<?= $booking['id'] ?>/print" class="btn btn-outline-primary ms-2" target="_blank">
                        <i class="fas fa-print"></i> Print Booking
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Booking History -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-history"></i> Booking History
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= date('d M Y h:i A', strtotime($booking['created_at'])) ?></td>
                        <td><span class="badge bg-success">Created</span></td>
                        <td>System</td>
                        <td>Initial booking created</td>
                    </tr>
                    <?php if ($booking['updated_at'] != $booking['created_at']): ?>
                        <tr>
                            <td><?= date('d M Y h:i A', strtotime($booking['updated_at'])) ?></td>
                            <td><span class="badge bg-warning">Updated</span></td>
                            <td>Last Updated</td>
                            <td>Booking details modified</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const propertySelect = document.getElementById('property_id');
        const amountInput = document.getElementById('total_amount');
        const associateSelect = document.getElementById('associate_id');
        const statusSelect = document.getElementById('status');
        const commissionPreview = document.getElementById('commission_details');
        const statusWarning = document.getElementById('statusWarning');
        const newStatusText = document.getElementById('newStatusText');
        const statusEffects = document.getElementById('statusEffects');

        // Auto-fill amount when property is selected
        propertySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');

            if (price) {
                amountInput.value = parseFloat(price).toFixed(2);
                updateCommissionPreview();
            }
        });

        // Update commission preview when associate or amount changes
        associateSelect.addEventListener('change', updateCommissionPreview);
        amountInput.addEventListener('input', updateCommissionPreview);

        // Show status change warning
        statusSelect.addEventListener('change', function() {
            const newStatus = this.value;
            const statusMessages = {
                'confirmed': {
                    'text': 'Confirmed',
                    'effects': [
                        'Send confirmation email to customer',
                        'Trigger commission calculation if associate assigned',
                        'Update property availability status'
                    ]
                },
                'completed': {
                    'text': 'Completed',
                    'effects': [
                        'Mark booking as fully completed',
                        'Generate final commission payments',
                        'Send completion notification to customer'
                    ]
                },
                'cancelled': {
                    'text': 'Cancelled',
                    'effects': [
                        'Cancel all pending commissions',
                        'Release property if applicable',
                        'Send cancellation notification to customer',
                        'Process refunds if any payments made'
                    ]
                }
            };

            const statusInfo = statusMessages[newStatus];
            if (statusInfo && newStatus !== '<?= $booking['status'] ?>') {
                newStatusText.textContent = statusInfo.text;
                statusEffects.innerHTML = statusInfo.effects.map(effect => `<li>${effect}</li>`).join('');
                statusWarning.classList.remove('d-none');
            } else {
                statusWarning.classList.add('d-none');
            }
        });

        function updateCommissionPreview() {
            const associateId = associateSelect.value;
            const amount = parseFloat(amountInput.value) || 0;

            if (associateId && amount > 0) {
                // Calculate commission (5% example)
                const commissionRate = 0.05;
                const commissionAmount = amount * commissionRate;

                commissionPreview.innerHTML = `
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
            } else {
                commissionPreview.innerHTML = '<p class="mb-0">No commission will be generated (Direct Booking)</p>';
            }
        }

        // Form validation before submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const propertyId = propertySelect.value;
            const customerId = document.getElementById('customer_id').value;
            const amount = parseFloat(amountInput.value);
            const bookingDate = document.getElementById('booking_date').value;

            if (!propertyId || !customerId || !amount || amount <= 0 || !bookingDate) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }

            // Confirm status change
            if (!statusWarning.classList.contains('d-none')) {
                if (!confirm('Are you sure you want to change the booking status? This will trigger automated actions.')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>