<?php
/**
 * @var array $booking
 * @var array $razorpay
 * @var string $csrf_token
 */
$booking = $booking ?? [];
$razorpay = $razorpay ?? ['key_id' => '', 'is_test' => true, 'configured' => false];
$csrf = $csrf_token ?? '';
$amountInr = (float)($booking['amount'] ?? 0);
$amountPaise = (int)round($amountInr * 100);
$displayAmount = number_format($amountInr, 2);
$bookingId = (int)($booking['id'] ?? 0);
$customerName = htmlspecialchars($booking['customer_name'] ?? 'Customer', ENT_QUOTES, 'UTF-8');
$orderId = 'ORD_' . strtoupper(bin2hex(random_bytes(5)));
?>
<style>
.checkout-shell { max-width: 760px; margin: 2.5rem auto; padding: 0 1rem; font-family: 'Segoe UI', system-ui, sans-serif; }
.checkout-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08); overflow: hidden; border: 1px solid #e5e7eb; }
.checkout-header { background: linear-gradient(135deg, #2563eb, #1e40af); color: #fff; padding: 1.75rem 2rem; }
.checkout-header h1 { margin: 0; font-size: 1.5rem; font-weight: 600; }
.checkout-header p { margin: 0.35rem 0 0; opacity: 0.9; font-size: 0.9rem; }
.checkout-body { padding: 2rem; }
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 0; border-bottom: 1px dashed #e5e7eb; }
.summary-row:last-child { border-bottom: 0; font-weight: 600; font-size: 1.1rem; color: #0f172a; padding-top: 1.1rem; }
.summary-label { color: #475569; font-size: 0.92rem; }
.summary-value { color: #0f172a; font-weight: 500; }
.pay-btn { display: flex; align-items: center; justify-content: center; gap: 0.6rem; width: 100%; padding: 1rem 1.5rem; background: #f97316; color: #fff; border: 0; border-radius: 10px; font-size: 1.05rem; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-top: 1.5rem; }
.pay-btn:hover { background: #ea580c; }
.pay-btn:disabled { background: #cbd5e1; cursor: not-allowed; }
.security-row { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1.25rem; color: #64748b; font-size: 0.82rem; }
.security-row i { color: #10b981; }
.test-banner { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; padding: 0.65rem 0.9rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem; }
.test-banner strong { font-weight: 600; }
</style>

<div class="checkout-shell">
    <div class="checkout-card">
        <div class="checkout-header">
            <h1><i class="fas fa-lock"></i> <?php echo __('checkout_secure_title', [], 'Secure Checkout'); ?></h1>
            <p>Booking #<?= $bookingId ?> &middot; <?= $customerName ?></p>
        </div>
        <div class="checkout-body">
            <?php if (!empty($razorpay['is_test'])): ?>
                <div class="test-banner">
                    <strong><i class="fas fa-flask"></i> <?php echo __('checkout_test_mode', [], 'Test mode:'); ?></strong>
                    <?php echo __('checkout_test_mode_desc', [], 'No real money will be charged. Use Razorpay test cards'); ?>
                    (<code>4111 1111 1111 1111</code>, any future expiry, any CVV).
                </div>
            <?php endif; ?>

            <div class="summary-row">
                <span class="summary-label"><?php echo __('checkout_booking_number', [], 'Booking Number'); ?></span>
                <span class="summary-value"><?= htmlspecialchars($booking['booking_number'] ?? ('BOK-' . $bookingId), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label"><?php echo __('checkout_description', [], 'Description'); ?></span>
                <span class="summary-value"><?php echo __('checkout_description_value', [], 'Plot/Property booking payment'); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label"><?php echo __('checkout_currency', [], 'Currency'); ?></span>
                <span class="summary-value">INR (&inr;)</span>
            </div>
            <div class="summary-row">
                <span class="summary-label"><?php echo __('checkout_amount_payable', [], 'Amount Payable'); ?></span>
                <span class="summary-value">&inr; <?= $displayAmount ?></span>
            </div>

            <button id="pay-btn" class="pay-btn" type="button">
                <i class="fas fa-shield-alt"></i> Pay &inr; <?= $displayAmount ?> with Razorpay
            </button>

            <div class="security-row">
                <i class="fas fa-lock"></i> <?php echo __('checkout_ssl', [], '256-bit SSL Encrypted'); ?> &middot;
                <i class="fas fa-shield-alt"></i> <?php echo __('checkout_pci', [], 'PCI-DSS Compliant'); ?> &middot;
                <i class="fas fa-check-circle"></i> <?php echo __('checkout_verified', [], 'Verified by Razorpay'); ?>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var btn = document.getElementById('pay-btn');
    var keyId = <?= json_encode($razorpay['key_id'] ?? '') ?>;
    var bookingId = <?= (int)$bookingId ?>;
    var csrf = <?= json_encode($csrf) ?>;
    var amountInr = <?= json_encode($amountInr) ?>;

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating order...';

        fetch('/checkout/process/' + bookingId, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrf
            },
            body: JSON.stringify({ csrf_token: csrf })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (result) {
            if (!result.ok || !result.body.success) {
                throw new Error(result.body.error || 'Order creation failed');
            }
            var order = result.body;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening checkout...';

            var options = {
                key: order.key_id || keyId,
                amount: order.amount_paise,
                currency: order.currency,
                name: 'APS Dream Home',
                description: 'Booking #' + bookingId,
                image: '<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo.png',
                order_id: order.order_id,
                handler: function (response) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/checkout/verify';
                    form.innerHTML =
                        '<input name="razorpay_order_id" value="' + response.razorpay_order_id + '">' +
                        '<input name="razorpay_payment_id" value="' + response.razorpay_payment_id + '">' +
                        '<input name="razorpay_signature" value="' + response.razorpay_signature + '">' +
                        '<input name="csrf_token" value="' + csrf + '">';
                    document.body.appendChild(form);
                    form.submit();
                },
                prefill: {
                    name: <?= json_encode($customerName) ?>,
                    email: <?= json_encode($booking['customer_email'] ?? '') ?>,
                    contact: <?= json_encode($booking['customer_phone'] ?? '') ?>
                },
                notes: { booking_id: String(bookingId) },
                theme: { color: '#2563eb' },
                modal: {
                    ondismiss: function () {
                        window.location.href = '/checkout/failed?reason=cancelled&order=' + encodeURIComponent(order.order_id);
                    }
                }
            };
            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response) {
                window.location.href = '/checkout/failed?reason=declined&order=' + encodeURIComponent(order.order_id);
            });
            rzp.open();
        })
        .catch(function (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-shield-alt"></i> Pay &#8377; ' + amountInr.toFixed(2) + ' with Razorpay';
            alert('Could not initiate payment: ' + err.message);
        });
    });
})();
</script>
