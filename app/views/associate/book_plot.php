<?php
$page_title = $page_title ?? __('assoc_bp_title', [], 'New Doorstep Booking');
$current_page = 'book-plot';
$plots = $plots ?? [];
$colonies = $colonies ?? [];
?>

<div class="container-fluid px-4 py-3">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate/dashboard"><i class="fas fa-home me-1"></i><?= __('nav_dashboard', [], 'Dashboard') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate/my-bookings"><?= __('assoc_book_title', [], 'My Bookings') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= __('assoc_bp_title', [], 'New Doorstep Booking') ?></li>
        </ol>
    </nav>

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
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i><?= __('assoc_bp_title', [], 'New Doorstep Plot Booking') ?></h5>
            <span class="badge bg-white text-primary fw-semibold"><i class="fas fa-shield-alt me-1"></i>Field Maker-Checker Mode</span>
        </div>
        <div class="card-body p-4">
            <!-- Statutory Anti-Fraud & Field Security Notice -->
            <?php if (empty($can_collect_cash)): ?>
            <div class="alert alert-info border-2 border-primary p-3 mb-4 shadow-sm" style="border-left: 5px solid #0d6efd !important; background-color: #f0f7ff;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-shield-alt fa-2x text-primary me-3 mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">सुरक्षित डिजिटल डोरस्टेप बुकिंग (Secure Digital Booking Policy)</h6>
                        <p class="text-muted mb-0 small">ग्राहक से सीधे कंपनी के अधिकृत बैंक खाते (NEFT / RTGS / IMPS) अथवा कंपनी UPI QR कोड पर ही टोकन भुगतान स्वीकार करें। टोकन रसीद तुरंत ग्राहक के साथ शेयर की जाएगी। किसी भी परिस्थिति में नकद (Cash) न लें।</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning border-2 border-warning p-3 mb-4 shadow-sm" style="border-left: 5px solid #f59e0b !important;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-money-bill-wave fa-2x text-warning me-3 mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">अधिकृत नकद संग्रह सीमा (Authorized Cash Collection Limit)</h6>
                        <p class="text-muted mb-0 small">आपके खाते पर दैनिक नकद सीमा <strong>₹<?= number_format($daily_cash_limit ?? 0, 2) ?></strong> मान्य है। नकद प्राप्त होते ही ग्राहक को तत्काल डिजिटल रसीद प्रदान करें।</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/associate/book-plot/submit" method="POST" enctype="multipart/form-data" id="bookingForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <?php if (!empty($prefill_lead_id)): ?>
                <input type="hidden" name="lead_id" value="<?= (int)$prefill_lead_id ?>">
                <div class="alert alert-info py-2 px-3 mb-3 small"><i class="fas fa-link me-2"></i><?= __('assoc_bp_from_lead', [], 'Booking from lead — customer details prefilled. Submitting marks the lead Won.') ?></div>
                <?php endif; ?>

                <!-- Section 1: Plot Selection -->
                <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-map-marked-alt me-2"></i>1. <?= __('assoc_bp_select_plot', [], 'Township & Plot Selection') ?></h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_colony', [], 'Colony / Township') ?> *</label>
                        <select aria-label="Colony filter" name="colony_id" class="form-select" id="colonyFilter" onchange="filterPlots()">
                            <option value=""><?= __('assoc_bp_all_colonies', [], 'All Colonies') ?></option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?> (<?= htmlspecialchars($c['city'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_plot', [], 'Plot') ?> *</label>
                        <select aria-label="Plot id" name="plot_id" id="plotSelect" class="form-select" required>
                            <option value=""><?= __('assoc_bp_select_plot_placeholder', [], '-- Select Available Plot --') ?></option>
                            <?php foreach ($plots as $p):
                                $pPrice = (float)($p['total_price'] ?? $p['price'] ?? 0);
                            ?>
                                <option value="<?= e($p['id'] ?? '') ?>" data-colony="<?= e($p['colony_id'] ?? '') ?>" data-price="<?= e($pPrice) ?>" data-area="<?= e($p['area_sqft'] ?? '') ?>">
                                    <?= __('assoc_bp_plot_option', ['number' => htmlspecialchars($p['plot_number'] ?? ''), 'area' => number_format((float)($p['area_sqft'] ?? 0)), 'price' => number_format($pPrice)], 'Plot #%number% - %area% sq ft - ₹%price%') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <div id="plotInfo" class="alert alert-info d-none mb-0">
                            <div class="row text-center text-md-start g-2">
                                <div class="col-md-4"><strong><?= __('assoc_bp_plot', [], 'Selected Plot') ?>:</strong> <span id="plotNumber" class="fw-bold text-primary">-</span></div>
                                <div class="col-md-4"><strong><?= __('assoc_bp_area', [], 'Plot Area') ?>:</strong> <span id="plotArea" class="fw-bold">-</span> <?= __('assoc_bp_sqft', [], 'sq ft') ?></div>
                                <div class="col-md-4"><strong><?= __('assoc_bp_price', [], 'Total Plot Price') ?>:</strong> <span class="fw-bold text-success" id="plotPrice">₹-</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Customer Details -->
                <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-user-check me-2"></i>2. <?= __('assoc_bp_customer_details', [], 'Customer Identity & Contact Details') ?></h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_customer_name', [], 'Customer Full Name') ?> *</label>
                        <input aria-label="Customer name" type="text" name="customer_name" class="form-control" required placeholder="<?= __('assoc_bp_customer_name_placeholder', [], 'Full name as per Aadhaar / PAN') ?>" value="<?= htmlspecialchars($prefill['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_phone', [], 'Mobile Phone Number') ?> *</label>
                        <input aria-label="Customer phone" type="tel" name="customer_phone" class="form-control" required placeholder="10-digit mobile number" pattern="[0-9]{10}" value="<?= htmlspecialchars($prefill['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_email', [], 'Email Address') ?></label>
                        <input aria-label="Customer email" type="email" name="customer_email" class="form-control" placeholder="customer@email.com" value="<?= htmlspecialchars($prefill['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_address', [], 'Permanent Address') ?></label>
                        <input aria-label="Customer address" type="text" name="customer_address" class="form-control" placeholder="<?= __('assoc_bp_address_placeholder', [], 'Full residential address') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_aadhar', [], 'Aadhaar Number') ?></label>
                        <input aria-label="Aadhar number" type="text" name="aadhar_number" class="form-control" placeholder="12-digit Aadhaar number" maxlength="12">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_pan', [], 'PAN Card Number') ?></label>
                        <input aria-label="Pan number" type="text" name="pan_number" class="form-control text-uppercase" placeholder="10-character PAN" maxlength="10">
                    </div>
                </div>

                <!-- Section 3: Financials & Payment Terms -->
                <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-hand-holding-usd me-2"></i>3. <?= __('assoc_bp_payment_details', [], 'Booking Token & Payment Details') ?></h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_booking_amount', [], 'Token Booking Amount (₹)') ?> *</label>
                        <input aria-label="Booking amount" type="number" name="booking_amount" id="bookingAmount" class="form-control fw-bold fs-5 text-primary" required min="1000" step="1000" placeholder="51000" value="51000">
                        <div class="form-text text-danger small">
                            <i class="fas fa-exclamation-triangle me-1"></i> <strong>वैधानिक सूचना:</strong> टोकन राशि पूर्णतः नॉन-रिफंडेबल (Non-Refundable) है।
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0"><?= __('assoc_bp_payment_mode', [], 'Payment Mode') ?> *</label>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                <i class="fas fa-wallet me-1"></i>Associate Wallet: ₹<?= number_format($wallet_balance ?? 0, 2) ?>
                            </span>
                        </div>
                        <select aria-label="Payment mode" name="payment_mode" class="form-select fw-semibold" required id="paymentModeSelect">
                            <option value="wallet" <?= ($wallet_balance ?? 0) >= 51000 ? 'selected' : '' ?>>
                                💼 Associate Wallet Balance (उपलब्ध: ₹<?= number_format($wallet_balance ?? 0, 2) ?>)
                            </option>
                            <option value="upi" <?= ($wallet_balance ?? 0) < 51000 ? 'selected' : '' ?>>
                                📱 UPI (Google Pay / PhonePe / Paytm to Company QR)
                            </option>
                            <option value="bank_transfer"><?= __('assoc_bp_mode_bank', [], 'Bank Transfer (NEFT / RTGS / IMPS)') ?></option>
                            <option value="cheque"><?= __('assoc_bp_mode_cheque', [], 'Cheque / DD (Favoring APS Dream Home Pvt Ltd)') ?></option>
                            <?php if (!empty($can_collect_cash)): ?>
                                <option value="cash"><?= __('assoc_bp_mode_cash', [], 'Cash (Authorized Collection)') ?></option>
                            <?php else: ?>
                                <option value="cash" disabled><?= __('assoc_bp_mode_cash_disabled', [], 'Cash (Disabled - कंपनी खाते में ऑनलाइन जमा कराएं)') ?></option>
                            <?php endif; ?>
                            <option value="online"><?= __('assoc_bp_mode_online', [], 'Online Portal Payment') ?></option>
                        </select>

                        <!-- Dynamic Payment Mode Guidance Banners -->
                        <div id="mode_box_wallet" class="alert alert-success py-2 px-3 small mt-2 mb-0 border-0" style="background:#dcfce7; color:#166534; display:none;">
                            <i class="fas fa-bolt me-1"></i> <strong>त्वरित आवंटन (Instant Cleared Allotment):</strong> टोकन राशि (₹51,000) आपके एसोसिएट वॉलेट से तुरंत डेबिट होगी और ग्राहक के नाम अधिकृत रसीद (Cleared Receipt) तुरंत जारी हो जाएगी।
                        </div>
                        <div id="mode_box_wallet_low" class="alert alert-warning py-2 px-3 small mt-2 mb-0 border-0" style="background:#fef3c7; color:#92400e; display:none;">
                            <i class="fas fa-exclamation-triangle me-1"></i> <strong>अपर्याप्त वॉलेट बैलेंस:</strong> आपके वॉलेट में ₹<?= number_format($wallet_balance ?? 0, 2) ?> उपलब्ध हैं। कृपया टोकन राशि के बराबर बैलेंस रखें या UPI / Bank Transfer चुनें।
                        </div>
                        <div id="mode_box_upi" class="alert alert-info py-2 px-3 small mt-2 mb-0 border-0" style="background:#e0f2fe; color:#0369a1; display:none;">
                            <i class="fas fa-qrcode me-1"></i> <strong>Company UPI QR:</strong> <code>apsdreamhome@icici</code> पर ग्राहक से UPI कराएं और नीचे 12-अंकों का UTR नंबर दर्ज करें।
                        </div>
                        <div id="mode_box_bank" class="alert alert-primary py-2 px-3 small mt-2 mb-0 border-0" style="background:#e0e7ff; color:#3730a3; display:none;">
                            <i class="fas fa-university me-1"></i> <strong>Bank Account:</strong> APS Dream Home Pvt Ltd | ICICI Bank | A/C: <code>002105001234</code> | IFSC: <code>ICIC0000021</code>
                        </div>
                        <div id="mode_box_cheque" class="alert alert-secondary py-2 px-3 small mt-2 mb-0 border-0" style="background:#f1f5f9; color:#334155; display:none;">
                            <i class="fas fa-money-check me-1"></i> <strong>Cheque / DD:</strong> "APS Dream Home Private Limited" के पक्ष में चेक विवरण नीचे दर्ज करें।
                        </div>
                        <?php if (empty($can_collect_cash)): ?>
                            <div id="mode_box_cash_locked" class="alert alert-danger py-2 px-3 small mt-2 mb-0 border-0" style="background:#fee2e2; color:#991b1b; display:none;">
                                <i class="fas fa-shield-alt me-1"></i> <strong>सुरक्षा निर्देश (Anti-Fraud Policy):</strong> ग्राहक से नकद (Cash) लेना वर्जित है। केवल कंपनी के आधिकारिक बैंक खाते या कंपनी UPI QR कोड पर ही ऑनलाइन भुगतान करवाएं।
                            </div>
                        <?php else: ?>
                            <div id="mode_box_cash_ok" class="alert alert-info py-2 px-3 small mt-2 mb-0 border-0" style="background:#e0f2fe; color:#0369a1; display:none;">
                                <i class="fas fa-info-circle me-1"></i> अधिकृत दैनिक नकद सीमा: <strong>₹<?= number_format($daily_cash_limit ?? 0, 2) ?></strong>। कैश प्राप्त होते ही तुरंत आधिकारिक रसीद जारी करें।
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section 3.1: Transaction Reference -->
                <div class="row g-3 mb-4" id="row_transaction_ref">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" id="label_transaction_ref">Transaction Ref / UTR / Cheque No.</label>
                        <input aria-label="Transaction reference" type="text" name="transaction_ref" id="input_transaction_ref" class="form-control" placeholder="Enter 12-digit UPI UTR or Bank Ref">
                        <div class="form-text small text-muted" id="help_transaction_ref">
                            UPI या बैंक ट्रांसफर का 12-अंकों का UTR / चेक नंबर दर्ज करें।
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="card bg-light border-0 p-3 rounded w-100">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-check text-success fs-4 me-3"></i>
                                <small class="text-muted">कंपनी प्रणाली द्वारा सभी भुगतानों का लेखा-जोखा और रसीद पूर्णतः ऑडिटेबल एवं सुरक्षित है।</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Concession & Agreement Calculation -->
                <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-gift text-danger me-2"></i>4. Festival Concession & Price Calculation (ऑफर व कूपन कोड)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Customer Promo Code (कूपन कोड)</label>
                        <div class="input-group">
                            <input aria-label="Promo code applied" type="text" name="promo_code_applied" id="assoc_promo_code" class="form-control text-uppercase fw-bold" placeholder="e.g. FESTIVE21, SUBHLABH5" value="<?= htmlspecialchars($_GET['promo'] ?? '') ?>">
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
                                <span class="small text-muted">Total Plot Value:</span>
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

                <!-- Section 5: Documents & Notes -->
                <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-upload me-2"></i>5. <?= __('assoc_bp_upload_docs', [], 'Signed Application Form & Customer ID (Optional)') ?></h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_master_deed', [], 'Signed Master Deed / Booking Form Upload') ?></label>
                        <input aria-label="Master deed upload" type="file" name="master_deed_upload" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text small"><?= __('assoc_bp_upload_help', [], 'Upload scanned copy of physical signed booking application (PDF, JPG, PNG up to 5MB)') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?= __('assoc_bp_customer_id_upload', [], 'Customer Photo ID Proof') ?></label>
                        <input aria-label="Customer id upload" type="file" name="customer_id_upload" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text small">Aadhaar card or PAN card copy (optional but recommended)</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold"><?= __('assoc_bp_notes', [], 'Field Notes / Special Instructions') ?></label>
                        <textarea aria-label="Notes" name="notes" class="form-control" rows="2" placeholder="<?= __('assoc_bp_notes_placeholder', [], 'Any payment transaction ref, UTR number, or special requests...') ?>"></textarea>
                    </div>
                </div>

                <!-- Section 6: Legal Declarations -->
                <div class="p-3 bg-light rounded border mb-4">
                    <div class="form-check mb-2">
                        <input aria-label="Agree master deed" class="form-check-input" type="checkbox" id="agreeMasterDeed" name="master_deed_accepted" value="1" required>
                        <label class="form-check-label small fw-semibold" for="agreeMasterDeed">
                            मैंने ग्राहक को मास्टर डीड (Master Deed V8) की शर्तें एवं 0% ब्याज योजना के 3-माह डिफ़ॉल्ट नियम (3-Month Consecutive Default Clause) स्पष्ट रूप से समझा दिए हैं। *
                        </label>
                    </div>
                    <div class="form-check">
                        <input aria-label="Agree terms" class="form-check-input" type="checkbox" id="agreeTerms" name="terms_consent" value="1" required>
                        <label class="form-check-label small" for="agreeTerms">
                            <?= __('assoc_bp_terms', [], 'I confirm that all details are correct and I have verified the customer\'s identity.') ?> *<br>
                            <small class="text-muted">I have read, understood, and unconditionally agree to the
                                <a href="<?= BASE_URL ?>/terms-conditions" target="_blank" class="text-primary text-decoration-underline">Master Agreement Terms</a>,
                                <a href="<?= BASE_URL ?>/cancellation-policy" target="_blank" class="text-primary text-decoration-underline">Cancellation Policy</a>, and
                                <a href="<?= BASE_URL ?>/refund-policy" target="_blank" class="text-primary text-decoration-underline">Refund Policy</a>.
                            </small>
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg px-4" id="btnSubmitBooking">
                        <i class="fas fa-paper-plane me-2"></i><?= __('assoc_bp_submit', [], 'Submit Doorstep Booking') ?>
                    </button>
                    <a href="<?= BASE_URL ?>/associate/my-bookings" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i><?= __('assoc_bp_cancel', [], 'Cancel') ?>
                    </a>
                </div>
            </form>
        </div>
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
        
        const price = Number(selected.dataset.price || 0);
        document.getElementById('plotPrice').textContent = '₹' + price.toLocaleString();
        document.getElementById('bookingAmount').value = '51000';
        document.getElementById('disp_total_price').textContent = '₹' + price.toLocaleString();
        
        const curDisc = parseFloat(document.getElementById('assoc_discount_amount').value) || 0;
        document.getElementById('disp_net_agreement').textContent = '₹' + Math.max(0, price - curDisc).toLocaleString();
        
        // Auto-select colony filter if not set
        if (selected.dataset.colony && !document.getElementById('colonyFilter').value) {
            document.getElementById('colonyFilter').value = selected.dataset.colony;
        }

        var pCodeInput = document.getElementById('assoc_promo_code');
        if (pCodeInput && pCodeInput.value.trim() && btnAssocApply) {
            btnAssocApply.click();
        }
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

// Payment Mode UI Switcher
function updatePaymentModeUI() {
    const sel = document.getElementById('paymentModeSelect');
    if (!sel) return;
    const mode = sel.value;
    const walletBalance = <?= (float)($wallet_balance ?? 0) ?>;
    const bookingAmount = parseFloat(document.getElementById('bookingAmount').value) || 51000;
    
    // Hide all mode boxes
    ['mode_box_wallet', 'mode_box_wallet_low', 'mode_box_upi', 'mode_box_bank', 'mode_box_cheque', 'mode_box_cash_locked', 'mode_box_cash_ok'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });

    const refInput = document.getElementById('input_transaction_ref');
    const refLabel = document.getElementById('label_transaction_ref');
    const refHelp = document.getElementById('help_transaction_ref');

    if (mode === 'wallet') {
        if (walletBalance >= bookingAmount) {
            const wOk = document.getElementById('mode_box_wallet');
            if (wOk) wOk.style.display = 'block';
        } else {
            const wLow = document.getElementById('mode_box_wallet_low');
            if (wLow) wLow.style.display = 'block';
        }
        if (refInput) {
            refInput.value = 'Auto-debited from Associate Wallet';
            refInput.readOnly = true;
            refInput.classList.add('bg-light');
        }
        if (refLabel) refLabel.textContent = 'Wallet Debit Clearance Reference';
        if (refHelp) refHelp.textContent = 'एसोसिएट वॉलेट से टोकन स्वतः डेबिट होकर तुरंत पक्की रसीद बनेगी।';
    } else {
        if (refInput && refInput.readOnly) {
            refInput.value = '';
            refInput.readOnly = false;
            refInput.classList.remove('bg-light');
        }
        if (mode === 'upi') {
            const el = document.getElementById('mode_box_upi');
            if (el) el.style.display = 'block';
            if (refLabel) refLabel.textContent = 'UPI Reference / 12-Digit UTR Number *';
            if (refInput) refInput.placeholder = 'e.g. 432156789012';
            if (refHelp) refHelp.textContent = 'Google Pay / PhonePe / Paytm लेनदेन का 12 अंकों का UTR नंबर दर्ज करें।';
        } else if (mode === 'bank_transfer') {
            const el = document.getElementById('mode_box_bank');
            if (el) el.style.display = 'block';
            if (refLabel) refLabel.textContent = 'NEFT / RTGS / IMPS Reference Number *';
            if (refInput) refInput.placeholder = 'e.g. ICICR24012345678';
            if (refHelp) refHelp.textContent = 'बैंक ट्रांसफर का पावती / यूटीआर नंबर दर्ज करें।';
        } else if (mode === 'cheque') {
            const el = document.getElementById('mode_box_cheque');
            if (el) el.style.display = 'block';
            if (refLabel) refLabel.textContent = 'Cheque / DD Number & Bank *';
            if (refInput) refInput.placeholder = 'e.g. Cheque #123456 - SBI Bank';
            if (refHelp) refHelp.textContent = 'चेक संख्या एवं जारीकर्ता बैंक का नाम दर्ज करें।';
        } else if (mode === 'cash') {
            const elLocked = document.getElementById('mode_box_cash_locked');
            const elOk = document.getElementById('mode_box_cash_ok');
            if (elLocked) elLocked.style.display = 'block';
            if (elOk) elOk.style.display = 'block';
            if (refLabel) refLabel.textContent = 'Cash Receipt Reference / Notes';
            if (refInput) refInput.placeholder = 'e.g. Cash collected against voucher';
            if (refHelp) refHelp.textContent = 'हस्तगत नकद विवरण दर्ज करें।';
        }
    }
}

document.getElementById('paymentModeSelect').addEventListener('change', updatePaymentModeUI);
document.getElementById('bookingAmount').addEventListener('input', updatePaymentModeUI);
updatePaymentModeUI();

// Form submission loading state
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('btnSubmitBooking');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting Booking Draft...';
});
</script>
