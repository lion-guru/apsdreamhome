<?php
$page_title = $page_title ?? 'Pay Token Amount';
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
            <h2><i class="fas fa-credit-card me-2"></i>Pay Token Amount</h2>
            <?php if ($booking): ?>
                <p>Secure payment for Plot <?= htmlspecialchars($booking['plot_number'] ?? '') ?> at <?= htmlspecialchars($booking['colony_name'] ?? '') ?></p>
            <?php else: ?>
                <p>Complete your token payment to confirm your booking.</p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Bookings
            </a>
        </div>
    </div>
</div>

<?php if (!$booking): ?>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h5>Booking Not Found</h5>
                <p>The booking you're looking for doesn't exist or you don't have access to it.</p>
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary">View My Bookings</a>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-map-marker-alt text-primary"></i> Plot Details</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Colony</small>
                        <strong><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Plot Number</small>
                        <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong>
                    </div>
                    <?php if (!empty($booking['block'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Block</small>
                        <strong><?= htmlspecialchars($booking['block']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Area</small>
                        <strong><?= number_format((float)($booking['area_sqft'] ?? 0)) ?> sq ft</strong>
                    </div>
                    <?php if (!empty($booking['dimension_label'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Dimensions</small>
                        <strong><?= htmlspecialchars($booking['dimension_label']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Total Plot Value</small>
                        <strong>₹<?= number_format((float)($booking['plot_price'] ?? $booking['total_plot_value'] ?? 0)) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-money-bill-wave text-success"></i> Payment Summary</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3" style="background: var(--aps-cp-primary-bg, #eef2ff); border-radius: 10px;">
                    <div>
                        <small class="text-muted">Token Amount to Pay</small>
                        <h3 class="mb-0 mt-1 text-primary">₹<?= number_format($token_amount) ?></h3>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Booking #</small>
                        <div class="fw-bold"><?= htmlspecialchars($booking['booking_number'] ?? '') ?></div>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-2">Accepted Payment Methods</small>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border"><i class="fas fa-mobile-alt me-1"></i> UPI</span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-credit-card me-1"></i> Debit Card</span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-credit-card me-1"></i> Credit Card</span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-university me-1"></i> Netbanking</span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-wallet me-1"></i> Wallet</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($order_id)): ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body text-center">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Unable to initialize payment. Please try again or contact support.
                    </div>
                </div>
            </div>
        <?php else: ?>

        <div class="aps-cp-card mb-4" id="payment-card">
            <div class="aps-cp-card-body text-center py-4">
                <?php if ($razorpay['test']): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-flask me-1"></i> Test Mode — No real money will be charged
                    </div>
                <?php endif; ?>

                <button id="pay-btn" class="btn btn-primary btn-lg px-5 py-3" style="font-size: 1.1rem; border-radius: 10px;">
                    <i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($token_amount) ?> Now
                </button>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secured by Razorpay. Your payment information is encrypted end-to-end.
                    </small>
                </div>
            </div>
        </div>

        <?php endif; ?>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-body">
                <h6><i class="fas fa-info-circle me-2 text-info"></i>What Happens Next?</h6>
                <ol class="mb-0 text-muted" style="line-height:2;">
                    <li>After successful payment, your booking status advances to <strong>Agreement Signed</strong>.</li>
                    <li>You'll receive a payment confirmation receipt.</li>
                    <li>Our team will contact you to schedule the agreement signing.</li>
                    <li>EMI schedule will be generated after agreement finalization.</li>
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    var options = {
        key: <?= json_encode($razorpay['key_id']) ?>,
        amount: <?= (int)($token_amount * 100) ?>,
        currency: 'INR',
        name: 'APS Dream Home',
        description: 'Token Payment — Plot <?= htmlspecialchars($booking["plot_number"] ?? "", ENT_QUOTES) ?> at <?= htmlspecialchars($booking["colony_name"] ?? "", ENT_QUOTES) ?>',
        order_id: <?= json_encode($order_id) ?>,
        handler: function(response) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying payment...';

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
                    alert(data.error || 'Payment verification failed. Please contact support.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay <?= number_format($token_amount) ?> Now';
                }
            })
            .catch(function() {
                alert('Unable to verify payment. If you were charged, please contact support.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay <?= number_format($token_amount) ?> Now';
            });
        },
        prefill: {
            name: <?= json_encode(htmlspecialchars($user['name'] ?? '')) ?>,
            email: <?= json_encode(htmlspecialchars($user['email'] ?? '')) ?>,
            contact: <?= json_encode(htmlspecialchars($user['phone'] ?? '')) ?>
        },
        theme: {
            color: '#4f46e5'
        },
        modal: {
            ondismiss: function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($token_amount) ?> Now';
            }
        }
    };

    try {
        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function(response) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($token_amount) ?> Now';
            alert('Payment failed: ' + (response.error?.description || 'Unknown error. Please try again.'));
        });
        rzp.open();
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($token_amount) ?> Now';
        alert('Unable to initialize payment. Please try again later.');
    }
});
</script>
<?php endif; ?>

<?php endif; ?>
