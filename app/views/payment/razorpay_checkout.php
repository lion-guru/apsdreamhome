<?php $pageTitle = 'Razorpay Checkout'; ?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/payment">Payment</a></li>
            <li class="breadcrumb-item active" aria-current="page">Razorpay</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-bolt"></i></div>
                    <h5 class="mb-0">Razorpay Checkout</h5>
                    <small class="text-muted">Secured by Razorpay</small>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($order)): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2"><span>Amount</span><strong>₹<?= number_format($order['amount'] / 100, 2) ?></strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Order ID</span><small><?= htmlspecialchars($order['id'] ?? '') ?></small></div>
                        <div class="d-flex justify-content-between"><span>Description</span><small><?= htmlspecialchars($description ?? 'Payment') ?></small></div>
                    </div>
                    <button id="razorpayBtn" class="btn btn-primary w-100 btn-lg"><i class="fas fa-lock me-1"></i>Pay ₹<?= number_format($order['amount'] / 100, 2) ?></button>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Initializing payment...</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($order)): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('razorpayBtn')?.addEventListener('click', function() {
    var options = {
        key: "<?= htmlspecialchars($razorpayKey ?? '') ?>",
        amount: "<?= $order['amount'] ?? 0 ?>",
        currency: "<?= $order['currency'] ?? 'INR' ?>",
        name: "APS Dream Home",
        description: "<?= htmlspecialchars($description ?? 'Payment') ?>",
        order_id: "<?= $order['id'] ?? '' ?>",
        prefill: { name: "<?= htmlspecialchars($name ?? '') ?>", email: "<?= htmlspecialchars($email ?? '') ?>", contact: "<?= htmlspecialchars($phone ?? '') ?>" },
        handler: function(response) {
            window.location.href = "<?= BASE_URL ?>/payment/success?payment_id=" + response.razorpay_payment_id + "&order_id=" + response.razorpay_order_id;
        },
        modal: { ondismiss: function() { alert('Payment cancelled. Please try again.'); } }
    };
    var rzp = new Razorpay(options);
    rzp.open();
});
</script>
<?php endif; ?>
