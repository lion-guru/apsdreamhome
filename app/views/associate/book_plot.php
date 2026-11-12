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
        <?php if (empty($can_offline_book)): ?>
        <div class="alert alert-warning border-2 border-warning p-3 mb-4 shadow-sm" style="border-left: 5px solid #f59e0b !important;">
            <div class="d-flex align-items-start">
                <i class="fas fa-lock fa-2x text-warning me-3 mt-1"></i>
                <div>
                    <h5 class="fw-bold text-dark mb-1">ऑफलाइन बुकिंग अधिकार प्रतिबंधित (Offline Booking Disabled)</h5>
                    <p class="text-muted mb-2">आपके एसोसिएट खाते पर वर्तमान में सीधे ऑफलाइन प्लॉट बुक करने की अनुमति सक्रिय नहीं है। कृपया ग्राहक को ऑनलाइन लीड के रूप में दर्ज करें ताकि मुख्य कार्यालय द्वारा इसे सत्यापित व बुक किया जा सके।</p>
                    <a href="<?= BASE_URL ?>/associate/leads/create" class="btn btn-sm btn-primary">
                        <i class="fas fa-user-plus me-1"></i> नई लीड जोड़ें (Add Customer as Online Lead)
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/associate/book-plot/submit" method="POST" enctype="multipart/form-data" id="bookingForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <?php if (!empty($prefill_lead_id)): ?>
            <input type="hidden" name="lead_id" value="<?= (int)$prefill_lead_id ?>">
            <div class="alert alert-info"><i class="fas fa-link me-2"></i><?= __('assoc_bp_from_lead', [], 'Booking from lead — customer details prefilled. Submitting marks the lead Won.') ?></div>
            <?php endif; ?>

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
                    <input type="text" name="customer_name" class="form-control" required placeholder="<?= __('assoc_bp_customer_name_placeholder', [], 'Full name as per ID') ?>" value="<?= htmlspecialchars($prefill['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_phone', [], 'Phone Number') ?> *</label>
                    <input type="tel" name="customer_phone" class="form-control" required placeholder="+91 XXXXXXXXXX" pattern="[0-9]{10}" value="<?= htmlspecialchars($prefill['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= __('assoc_bp_email', [], 'Email') ?></label>
                    <input type="email" name="customer_email" class="form-control" placeholder="customer@email.com" value="<?= htmlspecialchars($prefill['email'] ?? '') ?>">
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
                        <?php if (!empty($can_collect_cash)): ?>
                            <option value="cash"><?= __('assoc_bp_mode_cash', [], 'Cash') ?> (अधिकृत दैनिक सीमा: ₹<?= number_format($daily_cash_limit ?? 0, 2) ?>)</option>
                        <?php else: ?>
                            <option value="cash" disabled><?= __('assoc_bp_mode_cash', [], 'Cash') ?> (प्रतिबंधित / Disabled - कंपनी खाते में ऑनलाइन जमा कराएं)</option>
                        <?php endif; ?>
                        <option value="upi" selected>UPI (Google Pay / PhonePe / Paytm to Company QR)</option>
                        <option value="bank_transfer"><?= __('assoc_bp_mode_bank', [], 'Bank Transfer (NEFT / RTGS / IMPS)') ?></option>
                        <option value="cheque"><?= __('assoc_bp_mode_cheque', [], 'Cheque / DD (Favoring APS Dream Home Pvt Ltd)') ?></option>
                        <option value="online"><?= __('assoc_bp_mode_online', [], 'Online Payment') ?></option>
                    </select>
                    <?php if (empty($can_collect_cash)): ?>
                        <div class="alert alert-danger py-2 px-3 small mt-2 mb-0 border-0" style="background:#fee2e2; color:#991b1b;">
                            <i class="fas fa-shield-alt me-1"></i> <strong>सुरक्षा निर्देश (Anti-Fraud Policy):</strong> ग्राहक से नकद (Cash) लेना वर्जित है। केवल कंपनी के आधिकारिक बैंक खाते या कंपनी UPI QR कोड पर ही ऑनलाइन भुगतान करवाएं।
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info py-2 px-3 small mt-2 mb-0 border-0" style="background:#e0f2fe; color:#0369a1;">
                            <i class="fas fa-info-circle me-1"></i> अधिकृत दैनिक नकद सीमा: <strong>₹<?= number_format($daily_cash_limit ?? 0, 2) ?></strong>। कैश प्राप्त होते ही तुरंत आधिकारिक रसीद जारी करें।
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <h6 class="text-primary mb-3"><i class="fas fa-gift text-danger me-2"></i>Festival Concession / Promo Code (त्योहारी ऑफर व कूपन कोड)</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Customer Promo Code (कूपन कोड)</label>
                    <div class="input-group">
                        <input type="text" name="promo_code_applied" id="assoc_promo_code" class="form-control text-uppercase fw-bold" placeholder="e.g. FESTIVE21, SUBHLABH5">
                        <button type="button" class="btn btn-primary" id="btn_assoc_apply_promo">
                            <i class="fas fa-check me-1"></i>Apply
                        </button>
                    </div>
                    <input type="hidden" name="promotional_offer_id" id="assoc_promo_offer_id" value="0">
                    <input type="hidden" name="discount_amount" id="assoc_discount_amount" value="0">
                    <div id="assoc_promo_msg" class="small mt-1 text-muted">
                        Enter customer coupon code to apply festive concession (e.g. <code>FESTIVE21</code> for ₹21,000 flat off, <code>SUBHLABH5</code> for 5% off).
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border p-3 rounded h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Plot Value:</span>
                            <strong class="text-dark" id="disp_total_price">₹0</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-success">Festive Concession:</span>
                            <strong class="text-success" id="disp_discount_amount">- ₹0</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="fw-bold text-primary">Final Agreement Value:</span>
                            <strong class="fw-bold text-primary fs-5" id="disp_net_agreement">₹0</strong>
                        </div>
                    </div>
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
                <button type="submit" class="btn btn-primary btn-lg" <?= empty($can_offline_book) ? 'disabled title="ऑफ़लाइन बुकिंग अधिकार अक्षम है"' : '' ?>>
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
        document.getElementById('bookingAmount').value = '51000';
        const price = Number(selected.dataset.price || 0);
        document.getElementById('disp_total_price').textContent = '₹' + price.toLocaleString();
        const curDisc = parseFloat(document.getElementById('assoc_discount_amount').value) || 0;
        document.getElementById('disp_net_agreement').textContent = '₹' + Math.max(0, price - curDisc).toLocaleString();
    } else {
        document.getElementById('plotInfo').classList.add('d-none');
        document.getElementById('disp_total_price').textContent = '₹0';
        document.getElementById('disp_net_agreement').textContent = '₹0';
    }
});

const btnAssocApply = document.getElementById('btn_assoc_apply_promo');
if (btnAssocApply) {
    btnAssocApply.addEventListener('click', function() {
        const promoInput = document.getElementById('assoc_promo_code');
        const code = promoInput ? promoInput.value.trim() : '';
        const msgDiv = document.getElementById('assoc_promo_msg');
        const offerIdField = document.getElementById('assoc_promo_offer_id');
        const discField = document.getElementById('assoc_discount_amount');
        const plotSelect = document.getElementById('plotSelect');
        const selectedPlot = plotSelect ? plotSelect.options[plotSelect.selectedIndex] : null;
        const plotPrice = selectedPlot && selectedPlot.dataset.price ? parseFloat(selectedPlot.dataset.price) : 0;
        const colonyId = document.getElementById('colonyFilter') ? document.getElementById('colonyFilter').value : '';

        if (!code) {
            if (msgDiv) msgDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Please enter a promo code</span>';
            return;
        }

        btnAssocApply.disabled = true;
        btnAssocApply.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Checking...';

        fetch('<?= BASE_URL ?>/api/promo/validate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'promo_code=' + encodeURIComponent(code) + '&plot_value=' + encodeURIComponent(plotPrice) + '&colony_id=' + encodeURIComponent(colonyId)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btnAssocApply.disabled = false;
            btnAssocApply.innerHTML = '<i class="fas fa-check me-1"></i>Apply';

            if (data.valid) {
                if (offerIdField) offerIdField.value = data.offer_id;
                if (discField) discField.value = data.discount_amount;
                if (msgDiv) msgDiv.innerHTML = '<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>' + data.message + '</span>';
                document.getElementById('disp_discount_amount').textContent = '- ₹' + Number(data.discount_amount).toLocaleString();
                document.getElementById('disp_net_agreement').textContent = '₹' + Number(Math.max(0, plotPrice - data.discount_amount)).toLocaleString();
            } else {
                if (offerIdField) offerIdField.value = '0';
                if (discField) discField.value = '0';
                if (msgDiv) msgDiv.innerHTML = '<span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>' + (data.message || 'Invalid promo code') + '</span>';
                document.getElementById('disp_discount_amount').textContent = '- ₹0';
                document.getElementById('disp_net_agreement').textContent = '₹' + Number(plotPrice).toLocaleString();
            }
        })
        .catch(function(err) {
            btnAssocApply.disabled = false;
            btnAssocApply.innerHTML = '<i class="fas fa-check me-1"></i>Apply';
            if (msgDiv) msgDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Failed to validate promo code</span>';
        });
    });
}
</script>
