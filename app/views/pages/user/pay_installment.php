<?php
$page_title = $page_title ?? __('user_pay_installment_page_title', 'Pay Installment');
$current_page = 'bookings';
$user = $user ?? [];
$booking = $booking ?? null;
$installment = $installment ?? null;
$amount_due = $amount_due ?? 0;
$emi_amount = $emi_amount ?? 0;
$paid_amount = $paid_amount ?? 0;
$late_fee = $late_fee ?? 0;
$penalty = $penalty ?? 0;
$order_id = $order_id ?? null;
$razorpay = $razorpay ?? ['key_id' => '', 'test' => true];
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-credit-card me-2"></i><?= __('user_pay_installment_hero_heading', 'Pay Installment') ?></h2>
            <?php if ($booking): ?>
                <p><?= __('user_pay_installment_hero_subtitle_prefix', 'Secure payment for Installment') ?> #<?= (int)($installment['installment_number'] ?? 0) ?> — <?= __('user_pay_installment_hero_subtitle_plot', 'Plot') ?> <?= htmlspecialchars($booking['plot_number'] ?? '') ?></p>
            <?php else: ?>
                <p><?= __('user_pay_installment_hero_subtitle_default', 'Complete your EMI installment payment.') ?></p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings/<?= (int)($installment['booking_id'] ?? 0) ?>" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_pay_installment_back_to_booking', 'Back to Booking') ?>
            </a>
        </div>
    </div>
</div>

<?php if (!$installment): ?>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h5><?= __('user_pay_installment_not_found_heading', 'Installment Not Found') ?></h5>
                <p><?= __('user_pay_installment_not_found_description', "The installment you're looking for doesn't exist or you don't have access to it.") ?></p>
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary"><?= __('user_pay_installment_view_bookings', 'View My Bookings') ?></a>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-map-marker-alt text-primary"></i> <?= __('user_pay_installment_booking_summary_header', 'Booking Summary') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_installment_colony', 'Colony') ?></small>
                        <strong><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_installment_plot_number', 'Plot Number') ?></small>
                        <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_installment_booking_number', 'Booking Number') ?></small>
                        <strong><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_installment_total_plot_value', 'Total Plot Value') ?></small>
                        <strong>₹<?= number_format((float)($booking['total_plot_value'] ?? 0)) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-file-invoice text-warning"></i> <?= __('user_pay_installment_details_header', 'Installment Details') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_installment_number', 'Installment Number') ?></small>
                        <strong class="fs-5">#<?= (int)($installment['installment_number'] ?? 0) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_installment_due_date', 'Due Date') ?></small>
                        <strong class="fs-5"><?= date('d M Y', strtotime($installment['due_date'] ?? 'now')) ?></strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="aps-cp-table">
                        <thead>
                            <tr>
                                <th><?= __('user_pay_installment_th_description', 'Description') ?></th>
                                <th class="text-end"><?= __('user_pay_installment_th_amount', 'Amount') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= __('user_pay_installment_emi_amount', 'EMI Amount') ?></td>
                                <td class="text-end">₹<?= number_format($emi_amount) ?></td>
                            </tr>
                            <?php if ($paid_amount > 0): ?>
                            <tr>
                                <td><small class="text-muted">(<?= __('user_pay_installment_previously_paid', 'Previously Paid') ?>)</small></td>
                                <td class="text-end text-danger">- ₹<?= number_format($paid_amount) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($late_fee > 0): ?>
                            <tr>
                                <td><?= __('user_pay_installment_late_fee', 'Late Fee') ?></td>
                                <td class="text-end text-warning">₹<?= number_format($late_fee) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($penalty > 0): ?>
                            <tr>
                                <td><?= __('user_pay_installment_accrued_penalty', 'Accrued Penalty') ?></td>
                                <td class="text-end text-danger">₹<?= number_format($penalty) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr >
                                <td class="fw-bold fs-5"><?= __('user_pay_installment_total_due', 'Total Due') ?></td>
                                <td class="text-end fw-bold fs-5 text-primary">₹<?= number_format($amount_due) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-money-bill-wave text-success"></i> <?= __('user_pay_installment_payment_summary_header', 'Payment Summary') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3">
                    <div>
                        <small class="text-muted"><?= __('user_pay_installment_amount_to_pay', 'Amount to Pay') ?></small>
                        <h3 class="mb-0 mt-1 text-primary">₹<?= number_format($amount_due) ?></h3>
                    </div>
                    <div class="text-end">
                        <small class="text-muted"><?= __('user_pay_installment_installment_number', 'Installment #') ?></small>
                        <div class="fw-bold"><?= (int)($installment['installment_number'] ?? 0) ?></div>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-2"><?= __('user_pay_installment_accepted_methods', 'Accepted Payment Methods') ?></small>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border"><i class="fas fa-mobile-alt me-1"></i> <?= __('user_pay_installment_upi', 'UPI') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-credit-card me-1"></i> <?= __('user_pay_installment_debit_card', 'Debit Card') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-credit-card me-1"></i> <?= __('user_pay_installment_credit_card', 'Credit Card') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-university me-1"></i> <?= __('user_pay_installment_netbanking', 'Netbanking') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-wallet me-1"></i> <?= __('user_pay_installment_wallet', 'Wallet') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $companyVpa = 'apsdreamhome@icici';
        $companyName = 'APS Dream Home Pvt Ltd';
        $instNum = (int)($installment['installment_number'] ?? $installment['id']);
        $bookingNum = htmlspecialchars($booking['booking_number'] ?? ('BK' . ($installment['booking_id'] ?? 0)));
        $upiTxnNote = 'EMI_' . $bookingNum . '_INST' . $instNum;
        $upiUri = 'upi://pay?pa=' . urlencode($companyVpa) . '&pn=' . urlencode($companyName) . '&am=' . number_format($amount_due, 2, '.', '') . '&tn=' . urlencode($upiTxnNote) . '&cu=INR';
        $upiQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($upiUri);
        ?>

        <!-- Payment Modes Navigation Tabs -->
        <ul class="nav nav-pills nav-fill mb-3 bg-white p-2 rounded-4 shadow-sm border" id="paymentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold" id="upi-tab" data-bs-toggle="pill" data-bs-target="#tab-upi" type="button" role="tab" aria-controls="tab-upi" aria-selected="true">
                    <i class="fas fa-qrcode text-primary me-2"></i>UPI & QR Code (Fastest)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold" id="online-tab" data-bs-toggle="pill" data-bs-target="#tab-online" type="button" role="tab" aria-controls="tab-online" aria-selected="false">
                    <i class="fas fa-credit-card text-success me-2"></i>Cards / NetBanking
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold" id="bank-tab" data-bs-toggle="pill" data-bs-target="#tab-bank" type="button" role="tab" aria-controls="tab-bank" aria-selected="false">
                    <i class="fas fa-university text-warning me-2"></i>Bank Transfer (NEFT)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="paymentTabContent">
            <!-- TAB 1: DYNAMIC UPI QR & APPS -->
            <div class="tab-pane fade show active" id="tab-upi" role="tabpanel" aria-labelledby="upi-tab">
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white"><i class="fas fa-bolt text-warning me-2"></i>Scan & Pay via Any UPI App</h5>
                        <span class="badge bg-warning text-dark px-3 py-1 fw-bold">Zero Gateway Fee</span>
                    </div>
                    <div class="aps-cp-card-body text-center p-4">
                        <div class="bg-light p-3 rounded-4 d-inline-block shadow-sm border mb-3">
                            <img src="<?= $upiQrUrl ?>" alt="UPI QR Code" width="200" height="200" class="img-fluid rounded-3">
                            <div class="mt-2 text-muted small fw-bold">Scan with GPay / PhonePe / Paytm / BHIM</div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-bold fs-4 text-primary mb-1">₹<?= number_format($amount_due) ?></div>
                            <small class="text-muted">Exact payable for Installment #<?= $instNum ?> (<?= $bookingNum ?>)</small>
                        </div>

                        <!-- 1-Click UPI Intent for Mobile -->
                        <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                            <a href="<?= $upiUri ?>" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-bold">
                                <i class="fas fa-mobile-alt me-1"></i> Pay via UPI App
                            </a>
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" onclick="copyUpiVpa('<?= $companyVpa ?>')">
                                <i class="fas fa-copy me-1"></i> Copy UPI ID (<span id="upiVpaText"><?= $companyVpa ?></span>)
                            </button>
                        </div>

                        <hr class="my-4">

                        <!-- UTR / Reference Submission Form -->
                        <div class="text-start bg-light p-3 rounded-4 border">
                            <h6 class="fw-bold mb-2 text-dark"><i class="fas fa-receipt text-success me-2"></i>Already Paid via UPI? Submit UTR Reference</h6>
                            <p class="small text-muted mb-3">Once you complete the payment on your UPI app, enter your 12-digit UTR / Reference number below for immediate logging:</p>
                            
                            <form id="upi-utr-form" onsubmit="submitUtrProof(event)">
                                <?= \App\Helpers\SecurityHelper::csrfField(); ?>
                                <input type="hidden" name="payment_method" value="upi_manual">
                                <div class="input-group mb-2">
                                    <input type="text" name="utr_number" id="utr_number" class="form-control" placeholder="Enter 12-digit UPI Ref / UTR No. (e.g. 432156789012)" required pattern="[A-Za-z0-9]{6,30}">
                                    <button class="btn btn-success px-4 fw-bold" type="submit" id="btn-submit-utr">
                                        <i class="fas fa-check me-1"></i> Submit UTR
                                    </button>
                                </div>
                                <small class="text-muted">You will find the 12-digit UTR in your payment details in Google Pay / PhonePe / Paytm.</small>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: RAZORPAY ONLINE -->
            <div class="tab-pane fade" id="tab-online" role="tabpanel" aria-labelledby="online-tab">
                <div class="aps-cp-card mb-4" id="payment-card">
                    <div class="aps-cp-card-body text-center py-4">
                        <?php if (empty($order_id)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Online payment gateway is currently in maintenance. Please use the <strong>UPI & QR Code</strong> or <strong>Bank Transfer</strong> tab to complete your payment instantly.
                            </div>
                        <?php else: ?>
                            <?php if ($razorpay['test']): ?>
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-flask me-1"></i> <?= __('user_pay_installment_test_mode', 'Test Mode — No real money will be charged') ?>
                                </div>
                            <?php endif; ?>

                            <button id="pay-btn" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold shadow">
                                <i class="fas fa-lock me-2"></i><?= __('user_pay_installment_pay_now_prefix', 'Pay') ?> ₹<?= number_format($amount_due) ?> <?= __('user_pay_installment_pay_now_suffix', 'Now') ?>
                            </button>

                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    <?= __('user_pay_installment_secured_by', 'Secured by Razorpay. Your payment information is encrypted end-to-end.') ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 3: BANK NEFT / RTGS -->
            <div class="tab-pane fade" id="tab-bank" role="tabpanel" aria-labelledby="bank-tab">
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <h5><i class="fas fa-university text-warning me-2"></i>Official Bank Details for NEFT / RTGS / IMPS</h5>
                    </div>
                    <div class="aps-cp-card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-3">
                                <tbody>
                                    <tr>
                                        <th class="bg-light" style="width:35%;">Account Beneficiary</th>
                                        <td><strong>APS Dream Home Private Limited</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Bank Name</th>
                                        <td>ICICI Bank Limited</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Account Number</th>
                                        <td>
                                            <span class="font-monospace fw-bold fs-6">002105001234</span>
                                            <button class="btn btn-sm btn-outline-secondary ms-2 py-0 px-2" onclick="navigator.clipboard.writeText('002105001234');alert('Account number copied!')" aria-label="Copy"><i class="fas fa-copy"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">IFSC Code</th>
                                        <td>
                                            <span class="font-monospace fw-bold">ICIC0000021</span>
                                            <button class="btn btn-sm btn-outline-secondary ms-2 py-0 px-2" onclick="navigator.clipboard.writeText('ICIC0000021');alert('IFSC copied!')" aria-label="Copy"><i class="fas fa-copy"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Account Type</th>
                                        <td>Current Account</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Branch</th>
                                        <td>Golghar Main Branch, Gorakhpur (UP)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-warning small mb-0">
                            <strong>Note:</strong> After making the bank transfer, please share the transaction reference (UTR) on WhatsApp: <strong>+91 70074 44842</strong> or submit it in the UPI & QR tab for instant allocation to Installment #<?= $instNum ?>.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function copyUpiVpa(vpa) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(vpa).then(function() {
            alert('UPI ID ' + vpa + ' copied to clipboard!');
        });
    } else {
        alert('UPI ID: ' + vpa);
    }
}

function submitUtrProof(e) {
    e.preventDefault();
    var form = document.getElementById('upi-utr-form');
    var utrInput = document.getElementById('utr_number');
    var btn = document.getElementById('btn-submit-utr');
    var utrVal = utrInput.value.trim();

    if (utrVal.length < 6) {
        alert('Please enter a valid UPI Reference / UTR Number.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';

    var formData = new FormData(form);

    fetch(<?= json_encode(BASE_URL . '/user/installments/' . (int)$installment['id'] . '/pay') ?>, {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert(data.error || 'Failed to submit payment reference. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Submit UTR';
        }
    })
    .catch(function(err) {
        alert('Network error submitting UTR. Please contact support.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check me-1"></i> Submit UTR';
    });
}
</script>

<?php if (!empty($order_id)): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var payBtn = document.getElementById('pay-btn');
if (payBtn) {
    payBtn.addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('user_pay_installment_js_processing', 'Processing...') ?>';

        var options = {
            key: <?= json_encode($razorpay['key_id']) ?>,
            amount: <?= (int)($amount_due * 100) ?>,
            currency: 'INR',
            name: 'APS Dream Home',
            description: '<?= __('user_pay_installment_js_description_prefix', 'EMI Installment') ?> #<?= (int)($installment["installment_number"] ?? 0) ?> — <?= __('user_pay_installment_js_description_plot', 'Plot') ?> <?= htmlspecialchars($booking["plot_number"] ?? "", ENT_QUOTES) ?>',
            order_id: <?= json_encode($order_id) ?>,
            handler: function(response) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('user_pay_installment_js_verifying', 'Verifying payment...') ?>';

                var formData = new FormData();
                formData.append('razorpay_order_id', response.razorpay_order_id);
                formData.append('razorpay_payment_id', response.razorpay_payment_id);
                formData.append('razorpay_signature', response.razorpay_signature);
                formData.append('csrf_token', <?= json_encode($_SESSION['csrf_token'] ?? '') ?>);

                fetch(<?= json_encode(BASE_URL . '/user/installments/' . (int)$installment['id'] . '/pay') ?>, {
                    method: 'POST',
                    body: formData
                })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.error || '<?= __('user_pay_installment_js_verify_failed', 'Payment verification failed. Please contact support.') ?>');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-lock me-2"></i><?= __('user_pay_installment_pay_now_prefix', 'Pay') ?> <?= number_format($amount_due) ?> <?= __('user_pay_installment_pay_now_suffix', 'Now') ?>';
                    }
                })
                .catch(function() {
                    alert('<?= __('user_pay_installment_js_verify_error', 'Unable to verify payment. If you were charged, please contact support.') ?>');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i><?= __('user_pay_installment_pay_now_prefix', 'Pay') ?> <?= number_format($amount_due) ?> <?= __('user_pay_installment_pay_now_suffix', 'Now') ?>';
                });
            },
            prefill: {
                name: <?= json_encode(htmlspecialchars($user['name'] ?? '')) ?>,
                email: <?= json_encode(htmlspecialchars($user['email'] ?? '')) ?>,
                contact: <?= json_encode(htmlspecialchars($user['phone'] ?? '')) ?>
            },
            theme: {
                color: '#0d9488'
            },
            modal: {
                ondismiss: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i>₹<?= number_format($amount_due) ?> <?= __('user_pay_installment_pay_now_suffix', 'Now') ?>';
                }
            }
        };

        try {
            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function(response) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock me-2"></i>₹<?= number_format($amount_due) ?> <?= __('user_pay_installment_pay_now_suffix', 'Now') ?>';
                alert('<?= __('user_pay_installment_js_payment_failed_prefix', 'Payment failed:') ?> ' + (response.error?.description || '<?= __('user_pay_installment_js_payment_failed_unknown', 'Unknown error. Please try again.') ?>'));
            });
            rzp.open();
        } catch(e) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock me-2"></i>₹<?= number_format($amount_due) ?> <?= __('user_pay_installment_pay_now_suffix', 'Now') ?>';
            alert('<?= __('user_pay_installment_js_init_error', 'Unable to initialize payment. Please try again later.') ?>');
        }
    });
}
</script>
<?php endif; ?>

<?php endif; ?>
