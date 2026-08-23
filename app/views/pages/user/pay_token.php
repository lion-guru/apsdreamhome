<?php
$page_title = $page_title ?? __('user_pay_token_page_title', 'Pay Token Amount');
$current_page = 'bookings';
$user = $user ?? [];
$booking = $booking ?? null;
$token_amount = $token_amount ?? 25000;
$order_id = $order_id ?? null;
$razorpay = $razorpay ?? ['key_id' => '', 'test' => true];
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-credit-card me-2"></i><?= __('user_pay_token_hero_heading', 'Pay Token Amount') ?></h2>
            <?php if ($booking): ?>
                <p><?= __('user_pay_token_hero_subtitle_with_plot', 'Secure payment for Plot') ?> <?= htmlspecialchars($booking['plot_number'] ?? '') ?> <?= __('user_pay_token_hero_subtitle_at', 'at') ?> <?= htmlspecialchars($booking['colony_name'] ?? '') ?></p>
            <?php else: ?>
                <p><?= __('user_pay_token_hero_subtitle_default', 'Complete your token payment to confirm your booking.') ?></p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_pay_token_back_to_bookings', 'Back to Bookings') ?>
            </a>
        </div>
    </div>
</div>

<?php if (!$booking): ?>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h5><?= __('user_pay_token_not_found_heading', 'Booking Not Found') ?></h5>
                <p><?= __('user_pay_token_not_found_description', "The booking you're looking for doesn't exist or you don't have access to it.") ?></p>
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary"><?= __('user_pay_token_not_found_button', 'View My Bookings') ?></a>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-map-marker-alt text-primary"></i> <?= __('user_pay_token_plot_details_header', 'Plot Details') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_token_colony', 'Colony') ?></small>
                        <strong><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_token_plot_number', 'Plot Number') ?></small>
                        <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong>
                    </div>
                    <?php if (!empty($booking['block'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_token_block', 'Block') ?></small>
                        <strong><?= htmlspecialchars($booking['block'] ?? '') ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_token_area', 'Area') ?></small>
                        <strong><?= number_format((float)($booking['area_sqft'] ?? 0)) ?> <?= __('user_pay_token_sq_ft', 'sq ft') ?></strong>
                    </div>
                    <?php if (!empty($booking['dimension_label'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_token_dimensions', 'Dimensions') ?></small>
                        <strong><?= htmlspecialchars($booking['dimension_label'] ?? '') ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_pay_token_total_plot_value', 'Total Plot Value') ?></small>
                        <strong>₹<?= number_format((float)($booking['plot_price'] ?? $booking['total_plot_value'] ?? 0)) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-money-bill-wave text-success"></i> <?= __('user_pay_token_payment_summary_header', 'Payment Summary') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 style-11131">
                    <div>
                        <small class="text-muted"><?= __('user_pay_token_amount_to_pay', 'Token Amount to Pay') ?></small>
                        <h3 class="mb-0 mt-1 text-primary">₹<?= number_format($token_amount) ?></h3>
                    </div>
                    <div class="text-end">
                        <small class="text-muted"><?= __('user_pay_token_booking_number', 'Booking #') ?></small>
                        <div class="fw-bold"><?= htmlspecialchars($booking['booking_number'] ?? '') ?></div>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-2"><?= __('user_pay_token_accepted_methods', 'Accepted Payment Methods') ?></small>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border"><i class="fas fa-mobile-alt me-1"></i> <?= __('user_pay_token_upi', 'UPI') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-credit-card me-1"></i> <?= __('user_pay_token_debit_card', 'Debit Card') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-credit-card me-1"></i> <?= __('user_pay_token_credit_card', 'Credit Card') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-university me-1"></i> <?= __('user_pay_token_netbanking', 'Netbanking') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-wallet me-1"></i> <?= __('user_pay_token_wallet', 'Wallet') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($order_id)): ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body text-center">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= __('user_pay_token_init_error', 'Unable to initialize payment. Please try again or contact support.') ?>
                    </div>
                </div>
            </div>
        <?php else: ?>

        <div class="aps-cp-card mb-4" id="payment-card">
            <div class="aps-cp-card-body text-center py-4">
                <?php if ($razorpay['test']): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-flask me-1"></i> <?= __('user_pay_token_test_mode', 'Test Mode — No real money will be charged') ?>
                    </div>
                <?php endif; ?>

                <button id="pay-btn" class="btn btn-primary btn-lg px-5 py-3 style-4415">
                    <i class="fas fa-lock me-2"></i><?= __('user_pay_token_pay_now_prefix', 'Pay') ?> ₹<?= number_format($token_amount) ?> <?= __('user_pay_token_pay_now_suffix', 'Now') ?>
                </button>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        <?= __('user_pay_token_secured_by', 'Secured by Razorpay. Your payment information is encrypted end-to-end.') ?>
                    </small>
                </div>
            </div>
        </div>

        <?php endif; ?>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-body">
                <h6><i class="fas fa-info-circle me-2 text-info"></i><?= __('user_pay_token_whats_next_header', 'What Happens Next?') ?></h6>
                <ol class="mb-0 text-muted style-74587">
                    <li><?= __('user_pay_token_step_1', 'After successful payment, your booking status advances to') ?> <strong><?= __('user_pay_token_step_1_status', 'Agreement Signed') ?></strong>.</li>
                    <li><?= __('user_pay_token_step_2', "You'll receive a payment confirmation receipt.") ?></li>
                    <li><?= __('user_pay_token_step_3', 'Our team will contact you to schedule the agreement signing.') ?></li>
                    <li><?= __('user_pay_token_step_4', 'EMI schedule will be generated after agreement finalization.') ?></li>
                </ol>
            </div>
        </div>

    </div>
</div>

<?php if (!empty($order_id)): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('pay-btn').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('user_pay_token_js_processing', 'Processing...') ?>';

    var options = {
        key: <?= json_encode($razorpay['key_id']) ?>,
        amount: <?= (int)($token_amount * 100) ?>,
        currency: 'INR',
        name: 'APS Dream Home',
        description: '<?= __('user_pay_token_js_description', 'Token Payment') ?> — Plot <?= htmlspecialchars($booking["plot_number"] ?? "", ENT_QUOTES) ?> at <?= htmlspecialchars($booking["colony_name"] ?? "", ENT_QUOTES) ?>',
        order_id: <?= json_encode($order_id) ?>,
        handler: function(response) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('user_pay_token_js_verifying', 'Verifying payment...') ?>';

            var formData = new FormData();
            formData.append('razorpay_order_id', response.razorpay_order_id);
            formData.append('razorpay_payment_id', response.razorpay_payment_id);
            formData.append('razorpay_signature', response.razorpay_signature);
            formData.append('csrf_token', <?= json_encode($_SESSION['csrf_token'] ?? '') ?>);

            fetch(<?= json_encode(BASE_URL . '/user/bookings/' . (int)$booking['id'] . '/pay-token') ?>, {
                method: 'POST',
                body: formData
            })
            .then(function(resp) { return resp.json(); })
            .then(function(data) {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.error || '<?= __('user_pay_token_js_verify_failed', 'Payment verification failed. Please contact support.') ?>');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i><?= __('user_pay_token_pay_now_prefix', 'Pay') ?> <?= number_format($token_amount) ?> <?= __('user_pay_token_pay_now_suffix', 'Now') ?>';
                }
            })
            .catch(function() {
                alert('<?= __('user_pay_token_js_verify_error', 'Unable to verify payment. If you were charged, please contact support.') ?>');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock me-2"></i><?= __('user_pay_token_pay_now_prefix', 'Pay') ?> <?= number_format($token_amount) ?> <?= __('user_pay_token_pay_now_suffix', 'Now') ?>';
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
                btn.innerHTML = '<i class="fas fa-lock me-2"></i>₹<?= number_format($token_amount) ?> <?= __('user_pay_token_pay_now_suffix', 'Now') ?>';
            }
        }
    };

    try {
        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function(response) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock me-2"></i>₹<?= number_format($token_amount) ?> <?= __('user_pay_token_pay_now_suffix', 'Now') ?>';
            alert('<?= __('user_pay_token_js_payment_failed_prefix', 'Payment failed:') ?> ' + (response.error?.description || '<?= __('user_pay_token_js_payment_failed_unknown', 'Unknown error. Please try again.') ?>'));
        });
        rzp.open();
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-2"></i>₹<?= number_format($token_amount) ?> <?= __('user_pay_token_pay_now_suffix', 'Now') ?>';
        alert('<?= __('user_pay_token_js_init_error', 'Unable to initialize payment. Please try again later.') ?>');
    }
});
</script>
<?php endif; ?>

<?php endif; ?>
