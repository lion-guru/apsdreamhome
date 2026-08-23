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
                            <tr class="style-33948">
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
                <div class="d-flex justify-content-between align-items-center mb-3 p-3" class="style-11131">
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

        <?php if (empty($order_id)): ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body text-center">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= __('user_pay_installment_init_error', 'Unable to initialize payment. Please try again or contact support.') ?>
                    </div>
                </div>
            </div>
        <?php else: ?>

        <div class="aps-cp-card mb-4" id="payment-card">
            <div class="aps-cp-card-body text-center py-4">
                <?php if ($razorpay['test']): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-flask me-1"></i> <?= __('user_pay_installment_test_mode', 'Test Mode — No real money will be charged') ?>
                    </div>
                <?php endif; ?>

                <button id="pay-btn" class="btn btn-primary btn-lg px-5 py-3" class="style-4415">
                    <i class="fas fa-lock me-2"></i><?= __('user_pay_installment_pay_now_prefix', 'Pay') ?> ₹<?= number_format($amount_due) ?> <?= __('user_pay_installment_pay_now_suffix', 'Now') ?>
                </button>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        <?= __('user_pay_installment_secured_by', 'Secured by Razorpay. Your payment information is encrypted end-to-end.') ?>
                    </small>
                </div>
            </div>
        </div>

        <?php endif; ?>

    </div>
</div>

<?php if (!empty($order_id)): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('pay-btn').addEventListener('click', function() {
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
</script>
<?php endif; ?>

<?php endif; ?>
