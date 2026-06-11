<?php
$page_title = $page_title ?? 'Pay Installment';
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
            <h2><i class="fas fa-credit-card me-2"></i>Pay Installment</h2>
            <?php if ($booking): ?>
                <p>Secure payment for Installment #<?= (int)($installment['installment_number'] ?? 0) ?> — Plot <?= htmlspecialchars($booking['plot_number'] ?? '') ?></p>
            <?php else: ?>
                <p>Complete your EMI installment payment.</p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings/<?= (int)($installment['booking_id'] ?? 0) ?>" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Booking
            </a>
        </div>
    </div>
</div>

<?php if (!$installment): ?>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h5>Installment Not Found</h5>
                <p>The installment you're looking for doesn't exist or you don't have access to it.</p>
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary">View My Bookings</a>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-map-marker-alt text-primary"></i> Booking Summary</h5>
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
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Booking Number</small>
                        <strong><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Total Plot Value</small>
                        <strong>₹<?= number_format((float)($booking['total_plot_value'] ?? 0)) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-file-invoice text-warning"></i> Installment Details</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Installment Number</small>
                        <strong class="fs-5">#<?= (int)($installment['installment_number'] ?? 0) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Due Date</small>
                        <strong class="fs-5"><?= date('d M Y', strtotime($installment['due_date'] ?? 'now')) ?></strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="aps-cp-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>EMI Amount</td>
                                <td class="text-end">₹<?= number_format($emi_amount) ?></td>
                            </tr>
                            <?php if ($paid_amount > 0): ?>
                            <tr>
                                <td><small class="text-muted">(Previously Paid)</small></td>
                                <td class="text-end text-danger">- ₹<?= number_format($paid_amount) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($late_fee > 0): ?>
                            <tr>
                                <td>Late Fee</td>
                                <td class="text-end text-warning">₹<?= number_format($late_fee) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($penalty > 0): ?>
                            <tr>
                                <td>Accrued Penalty</td>
                                <td class="text-end text-danger">₹<?= number_format($penalty) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr style="border-top: 2px solid var(--aps-cp-border, #e5e7eb);">
                                <td class="fw-bold fs-5">Total Due</td>
                                <td class="text-end fw-bold fs-5 text-primary">₹<?= number_format($amount_due) ?></td>
                            </tr>
                        </tbody>
                    </table>
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
                        <small class="text-muted">Amount to Pay</small>
                        <h3 class="mb-0 mt-1 text-primary">₹<?= number_format($amount_due) ?></h3>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Installment #</small>
                        <div class="fw-bold"><?= (int)($installment['installment_number'] ?? 0) ?></div>
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
                    <i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($amount_due) ?> Now
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
        amount: <?= (int)($amount_due * 100) ?>,
        currency: 'INR',
        name: 'APS Dream Home',
        description: 'EMI Installment #<?= (int)($installment["installment_number"] ?? 0) ?> — Plot <?= htmlspecialchars($booking["plot_number"] ?? "", ENT_QUOTES) ?>',
        order_id: <?= json_encode($order_id) ?>,
        handler: function(response) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying payment...';

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
                    alert(data.error || 'Payment verification failed. Please contact support.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay <?= number_format($amount_due) ?> Now';
                }
            })
            .catch(function() {
                alert('Unable to verify payment. If you were charged, please contact support.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay <?= number_format($amount_due) ?> Now';
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
                btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($amount_due) ?> Now';
            }
        }
    };

    try {
        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function(response) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($amount_due) ?> Now';
            alert('Payment failed: ' + (response.error?.description || 'Unknown error. Please try again.'));
        });
        rzp.open();
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹<?= number_format($amount_due) ?> Now';
        alert('Unable to initialize payment. Please try again later.');
    }
});
</script>
<?php endif; ?>

<?php endif; ?>
