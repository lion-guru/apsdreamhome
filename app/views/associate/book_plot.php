<?php
/**
 * Associate Book Plot Form
 */
$page_title = $page_title ?? 'Book Plot';
$current_page = 'book-plot';
$plots = $plots ?? [];
$colonies = $colonies ?? [];
?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Book Plot for Customer</h5>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/associate/book-plot/submit" method="POST" enctype="multipart/form-data" id="bookingForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <!-- Plot Selection -->
            <h6 class="text-primary mb-3"><i class="fas fa-map me-2"></i>Select Plot</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Colony *</label>
                    <select class="form-select" id="colonyFilter" onchange="filterPlots()">
                        <option value="">All Colonies</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Plot *</label>
                    <select name="plot_id" id="plotSelect" class="form-select" required>
                        <option value="">Select Plot</option>
                        <?php foreach ($plots as $p): ?>
                            <option value="<?= $p['id'] ?>" data-colony="<?= $p['colony_id'] ?>" data-price="<?= $p['price'] ?>" data-area="<?= $p['area_sqft'] ?>">
                                Plot #<?= htmlspecialchars($p['plot_number']) ?> - <?= number_format($p['area_sqft']) ?> sq ft - ₹<?= number_format($p['price']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <div id="plotInfo" class="alert alert-info d-none">
                        <div class="row">
                            <div class="col-md-4"><strong>Plot:</strong> <span id="plotNumber">-</span></div>
                            <div class="col-md-4"><strong>Area:</strong> <span id="plotArea">-</span> sq ft</div>
                            <div class="col-md-4"><strong>Price:</strong> ₹<span id="plotPrice">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Details -->
            <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Customer Details</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="Full name as per ID">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Phone Number *</label>
                    <input type="tel" name="customer_phone" class="form-control" required placeholder="+91 XXXXXXXXXX" pattern="[0-9]{10}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="customer_email" class="form-control" placeholder="customer@email.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Address</label>
                    <input type="text" name="customer_address" class="form-control" placeholder="Full address">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Aadhar Number</label>
                    <input type="text" name="aadhar_number" class="form-control" placeholder="12-digit Aadhar" pattern="[0-9]{12}" maxlength="12">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control" placeholder="ABCDE1234F" pattern="[A-Z]{5}[0-9]{4}[A-Z]" style="text-transform: uppercase;" maxlength="10">
                </div>
            </div>

            <!-- Payment Details -->
            <h6 class="text-primary mb-3"><i class="fas fa-rupee-sign me-2"></i>Payment Details</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Booking Amount *</label>
                    <input type="number" name="booking_amount" id="bookingAmount" class="form-control" required min="1" step="0.01" placeholder="Token/advance amount">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Payment Mode *</label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online Payment</option>
                    </select>
                </div>
            </div>

            <!-- Document Upload -->
            <h6 class="text-primary mb-3"><i class="fas fa-upload me-2"></i>Upload Documents (Optional)</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Aadhar Card Copy</label>
                    <input type="file" name="aadhar_doc" class="form-control" accept="image/*,.pdf">
                    <div class="form-text">JPG, PNG or PDF (max 5MB)</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">PAN Card Copy</label>
                    <input type="file" name="pan_doc" class="form-control" accept="image/*,.pdf">
                    <div class="form-text">JPG, PNG or PDF (max 5MB)</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Hard Copy Form Scan</label>
                    <input type="file" name="form_copy" class="form-control" accept="image/*,.pdf">
                    <div class="form-text">Scanned copy of signed form</div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="form-label fw-bold">Notes / Special Instructions</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Any special requests or notes about this booking..."></textarea>
            </div>

            <!-- Terms -->
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                    <label class="form-check-label" for="agreeTerms">
                        I confirm that all details are correct and I have verified the customer's identity. *
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>Submit Booking
                </button>
                <a href="<?= BASE_URL ?>/associate/dashboard" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function filterPlots() {
    const colonyId = document.getElementById('colonyFilter').value;
    const options = document.querySelectorAll('#plotSelect option[data-colony]');
    options.forEach(opt => {
        opt.style.display = (!colonyId || opt.dataset.colony === colonyId) ? '' : 'none';
    });
}

document.getElementById('plotSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (selected.value) {
        document.getElementById('plotInfo').classList.remove('d-none');
        document.getElementById('plotNumber').textContent = selected.text.split(' - ')[0];
        document.getElementById('plotArea').textContent = selected.dataset.area ? Number(selected.dataset.area).toLocaleString() : '-';
        document.getElementById('plotPrice').textContent = selected.dataset.price ? Number(selected.dataset.price).toLocaleString() : '-';
        document.getElementById('bookingAmount').value = selected.dataset.price || '';
    } else {
        document.getElementById('plotInfo').classList.add('d-none');
    }
});
</script>
