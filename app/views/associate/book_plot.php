<?php
$page_title = $page_title ?? __('assoc_bp_title', [], 'Book Plot');
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
        <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i><?= __('assoc_bp_title', [], 'Book Plot for Customer') ?></h5>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/associate/book-plot/submit" method="POST" enctype="multipart/form-data" id="bookingForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <h6 class="text-primary mb-3"><i class="fas fa-map me-2"></i><?= __('assoc_bp_select_plot', [], 'Select Plot') ?></h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_colony', [], 'Colony') ?> *</label>
                    <select class="form-select" id="colonyFilter" onchange="filterPlots()">
                        <option value=""><?= __('assoc_bp_all_colonies', [], 'All Colonies') ?></option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_plot', [], 'Plot') ?> *</label>
                    <select name="plot_id" id="plotSelect" class="form-select" required>
                        <option value=""><?= __('assoc_bp_select_plot_placeholder', [], 'Select Plot') ?></option>
                        <?php foreach ($plots as $p): ?>
                            <option value="<?= $p['id'] ?>" data-colony="<?= $p['colony_id'] ?>" data-price="<?= $p['price'] ?>" data-area="<?= $p['area_sqft'] ?>">
                                <?= __('assoc_bp_plot_option', ['number' => htmlspecialchars($p['plot_number'] ?? ''), 'area' => number_format($p['area_sqft']), 'price' => number_format($p['price'])], 'Plot #%number% - %area% sq ft - ₹%price%') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <div id="plotInfo" class="alert alert-info d-none">
                        <div class="row">
                            <div class="col-md-4"><strong><?= __('assoc_bp_plot', [], 'Plot') ?>:</strong> <span id="plotNumber">-</span></div>
                            <div class="col-md-4"><strong><?= __('assoc_bp_area', [], 'Area') ?>:</strong> <span id="plotArea">-</span> <?= __('assoc_bp_sqft', [], 'sq ft') ?></div>
                            <div class="col-md-4"><strong><?= __('assoc_bp_price', [], 'Price') ?>:</strong> ₹<span id="plotPrice">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i><?= __('assoc_bp_customer_details', [], 'Customer Details') ?></h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_customer_name', [], 'Customer Name') ?> *</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="<?= __('assoc_bp_customer_name_placeholder', [], 'Full name as per ID') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_phone', [], 'Phone Number') ?> *</label>
                    <input type="tel" name="customer_phone" class="form-control" required placeholder="+91 XXXXXXXXXX" pattern="[0-9]{10}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_email', [], 'Email') ?></label>
                    <input type="email" name="customer_email" class="form-control" placeholder="customer@email.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_address', [], 'Address') ?></label>
                    <input type="text" name="customer_address" class="form-control" placeholder="<?= __('assoc_bp_address_placeholder', [], 'Full address') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_aadhar', [], 'Aadhar Number') ?></label>
                    <input type="text" name="aadhar_number" class="form-control" placeholder="<?= __('assoc_bp_aadhar_placeholder', [], '12-digit Aadhar') ?>" pattern="[0-9]{12}" maxlength="12">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_pan', [], 'PAN Number') ?></label>
                    <input type="text" name="pan_number" class="form-control" placeholder="ABCDE1234F" pattern="[A-Z]{5}[0-9]{4}[A-Z]" class="style-73536" maxlength="10">
                </div>
            </div>

            <h6 class="text-primary mb-3"><i class="fas fa-rupee-sign me-2"></i><?= __('assoc_bp_payment_details', [], 'Payment Details') ?></h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_booking_amount', [], 'Booking Amount') ?> *</label>
                    <input type="number" name="booking_amount" id="bookingAmount" class="form-control" required min="1" step="0.01" placeholder="<?= __('assoc_bp_booking_amount_placeholder', [], 'Token/advance amount') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_payment_mode', [], 'Payment Mode') ?> *</label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="cash"><?= __('assoc_bp_mode_cash', [], 'Cash') ?></option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer"><?= __('assoc_bp_mode_bank', [], 'Bank Transfer') ?></option>
                        <option value="cheque"><?= __('assoc_bp_mode_cheque', [], 'Cheque') ?></option>
                        <option value="online"><?= __('assoc_bp_mode_online', [], 'Online Payment') ?></option>
                    </select>
                </div>
            </div>

            <h6 class="text-primary mb-3"><i class="fas fa-upload me-2"></i><?= __('assoc_bp_upload_docs', [], 'Upload Documents (Optional)') ?></h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold"><?= __('assoc_bp_doc_aadhar', [], 'Aadhar Card Copy') ?></label>
                    <input type="file" name="aadhar_doc" class="form-control" accept="image/*,.pdf">
                    <div class="form-text"><?= __('assoc_bp_doc_format', [], 'JPG, PNG or PDF (max 5MB)') ?></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold"><?= __('assoc_bp_doc_pan', [], 'PAN Card Copy') ?></label>
                    <input type="file" name="pan_doc" class="form-control" accept="image/*,.pdf">
                    <div class="form-text"><?= __('assoc_bp_doc_format', [], 'JPG, PNG or PDF (max 5MB)') ?></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold"><?= __('assoc_bp_doc_form', [], 'Hard Copy Form Scan') ?></label>
                    <input type="file" name="form_copy" class="form-control" accept="image/*,.pdf">
                    <div class="form-text"><?= __('assoc_bp_doc_form_desc', [], 'Scanned copy of signed form') ?></div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold"><?= __('assoc_bp_notes', [], 'Notes / Special Instructions') ?></label>
                <textarea name="notes" class="form-control" rows="3" placeholder="<?= __('assoc_bp_notes_placeholder', [], 'Any special requests or notes about this booking...') ?>"></textarea>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                    <label class="form-check-label" for="agreeTerms">
                        <?= __('assoc_bp_terms', [], 'I confirm that all details are correct and I have verified the customer\'s identity.') ?> *
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i><?= __('assoc_bp_submit', [], 'Submit Booking') ?>
                </button>
                <a href="<?= BASE_URL ?>/associate/dashboard" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-times me-2"></i><?= __('assoc_bp_cancel', [], 'Cancel') ?>
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
