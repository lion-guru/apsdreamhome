<?php
$page_title = $page_title ?? __('assoc_bp_title', [], 'Book Plot');
$current_page = 'book-plot';
$plots = $plots ?? [];
$colonies = $colonies ?? [];
?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= e($_SESSION['flash_success']) ?>
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
                            <option value="<?= e($p['id'] ?? '') ?>" data-colony="<?= e($p['colony_id'] ?? '') ?>" data-price="<?= e($p['price'] ?? '') ?>" data-area="<?= e($p['area_sqft'] ?? '') ?>">
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
                    <input type="text" name="pan_number" class="form-control" placeholder="ABCDE1234F" pattern="[A-Z]{5}[0-9]{4}[A-Z]" maxlength="10">
                </div>
            </div>

            <h6 class="text-primary mb-3"><i class="fas fa-rupee-sign me-2"></i><?= __('assoc_bp_payment_details', [], 'Payment Details') ?></h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_booking_amount', [], 'Booking Amount') ?> *</label>
                    <input type="number" name="booking_amount" id="bookingAmount" class="form-control" required min="1" step="0.01" value="51000" placeholder="51000">
                    <div class="form-text text-danger fw-bold small mt-1">
                        <i class="fas fa-exclamation-triangle me-1"></i> वैधानिक सूचना: टोकन बुकिंग राशि ₹51,000 पूर्णतः गैर-वापसी योग्य (Non-Refundable / वापस नहीं होगी) है (मास्टर डीड धारा 2.1 व 2.9)।
                    </div>
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
                <div class="card border-gold bg-light">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0"><i class="fas fa-file-contract me-2"></i>Master Deed & Agreement</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">The Master Deed governs all terms of the plot booking. Both the associate and customer must accept these terms. <strong>Booking token amount (₹51,000) is strictly non-refundable.</strong></p>
                        <div class="d-flex gap-2 mb-2 flex-wrap">
                            <a href="<?= BASE_URL ?>/assets/documents/APS Group - Tripartite Master Legal Deed & Agreement (English Ultimate V8).docx" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Download Master Deed (English)
                            </a>
                            <a href="<?= BASE_URL ?>/assets/documents/APS Group - त्रि-पक्षीय मास्टर रियल एस्टेट अनुबंध एवं नियम-शर्तें.docx" target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download me-1"></i> डाउनलोड मास्टर डीड (हिंदी)
                            </a>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="masterDeedAccepted" name="master_deed_accepted" required>
                            <label class="form-check-label" for="masterDeedAccepted">
                                I confirm that I and the customer have read, understood, and unconditionally agree to the <strong>Master Deed & Agreement</strong>, and agree that the <strong>₹51,000 Token Booking Amount is strictly NON-REFUNDABLE (टोकन बुकिंग राशि वापस नहीं होगी)</strong>. *
                            </label>
                            <div class="invalid-feedback">You must accept the Master Deed to proceed.</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold small">Upload Signed Master Deed Copy</label>
                            <input type="file" name="master_deed_upload" class="form-control" accept="image/*,.pdf">
                            <div class="form-text">Scanned or photographed copy of the signed Master Deed (JPG, PNG, or PDF — max 5MB)</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold"><?= __('assoc_bp_notes', [], 'Notes / Special Instructions') ?></label>
                <textarea name="notes" class="form-control" rows="3" placeholder="<?= __('assoc_bp_notes_placeholder', [], 'Any special requests or notes about this booking...') ?>"></textarea>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="agreeTerms" name="terms_consent" required>
                    <label class="form-check-label" for="agreeTerms">
                        <?= __('assoc_bp_terms', [], 'I confirm that all details are correct and I have verified the customer\'s identity.') ?> *<br>
                        <small class="text-muted">I have read, understood, and unconditionally agree to the
                            <a href="<?= BASE_URL ?>/terms-conditions" target="_blank" class="text-primary text-decoration-underline">Master Agreement Terms</a>,
                            <a href="<?= BASE_URL ?>/cancellation-policy" target="_blank" class="text-primary text-decoration-underline">Cancellation Policy</a>, and
                            <a href="<?= BASE_URL ?>/refund-policy" target="_blank" class="text-primary text-decoration-underline">Refund Policy</a>.
                        </small>
                    </label>
                    <div class="invalid-feedback">You must accept the cancellation, refund, and master agreement terms to proceed.</div>
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
