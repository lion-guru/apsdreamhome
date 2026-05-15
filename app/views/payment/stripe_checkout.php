<?php $pageTitle = $pageTitle ?? $page_title ?? 'Stripe Checkout'; $clientSecret = $client_secret ?? ''; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <i class="fab fa-stripe fa-4x text-primary mb-3"></i>
                    <h4>Complete Your Payment</h4>
                    <p class="text-muted">Secure payment via Stripe</p>
                    <div id="card-element" class="mb-3 p-3 border rounded"></div>
                    <div id="card-errors" class="text-danger small mb-3"></div>
                    <button id="submit-btn" class="btn btn-primary w-100">
                        <i class="fas fa-lock me-2"></i>Pay with Stripe
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('<?= h($publishable_key ?? $stripe_publishable_key ?? '') ?>');
const elements = stripe.elements();
const card = elements.create('card');
card.mount('#card-element');
card.on('change', function(event) { document.getElementById('card-errors').textContent = event.error ? event.error.message : ''; });
document.getElementById('submit-btn').addEventListener('click', async function() {
    this.disabled = true; this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    const {error, paymentIntent} = await stripe.confirmCardPayment('<?= h($client_secret ?? $clientSecret ?? '') ?>');
    if (error) { document.getElementById('card-errors').textContent = error.message; this.disabled = false; this.innerHTML = '<i class="fas fa-lock me-2"></i>Pay with Stripe'; }
    else { window.location.href = '<?= BASE_URL ?>payment/success?gateway=stripe&payment_id=' + paymentIntent.id + '&order_id=<?= h($order_id ?? '') ?>'; }
});
</script>
