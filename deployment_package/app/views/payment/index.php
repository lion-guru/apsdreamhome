<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Payment' ?> - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .property-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover, .payment-method.selected {
            border-color: #007bff;
            background: #f8f9ff;
        }
        .payment-method i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #007bff;
        }
        .amount-display {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
        .btn-pay {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 25px;
            width: 100%;
            transition: transform 0.2s ease;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container payment-container">
        <div class="text-center mb-4">
            <h2><i class="fas fa-credit-card text-primary"></i> Secure Payment</h2>
            <p class="text-muted">Complete your property booking payment</p>
        </div>

        <!-- Property Details -->
        <div class="property-card">
            <h4><i class="fas fa-home"></i> Property Details</h4>
            <div class="row mt-3">
                <div class="col-md-8">
                    <h5><?= htmlspecialchars($property['title'] ?? 'Property') ?></h5>
                    <p class="text-muted">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($property['city'] ?? '') ?>,
                        <?= htmlspecialchars($property['state'] ?? '') ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="amount-display">
                        ₹<?= number_format($amount ?? 0) ?>
                    </div>
                    <small class="text-muted">Total Amount</small>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="payment-section">
            <h5><i class="fas fa-wallet"></i> Choose Payment Method</h5>
            <div class="payment-methods">
                <div class="payment-method" data-method="card">
                    <i class="fab fa-cc-visa"></i>
                    <div>Credit/Debit Card</div>
                </div>
                <div class="payment-method" data-method="upi">
                    <i class="fas fa-mobile-alt"></i>
                    <div>UPI</div>
                </div>
                <div class="payment-method" data-method="netbanking">
                    <i class="fas fa-university"></i>
                    <div>Net Banking</div>
                </div>
                <div class="payment-method" data-method="wallet">
                    <i class="fas fa-wallet"></i>
                    <div>Wallet</div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <form id="paymentForm" method="POST" action="process-payment">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="hidden" name="property_id" value="<?= $_GET['property_id'] ?? '' ?>">
            <input type="hidden" name="amount" value="<?= $amount ?? '' ?>">
            <input type="hidden" name="payment_method" id="selectedMethod" value="card">

            <div class="text-center">
                <button type="button" class="btn btn-pay" id="payButton">
                    <i class="fas fa-lock"></i> Pay ₹<?= number_format($amount ?? 0) ?> Securely
                </button>
            </div>
        </form>

        <!-- Security Notice -->
        <div class="alert alert-info mt-4">
            <i class="fas fa-shield-alt"></i>
            <strong>Secure Payment:</strong> Your payment information is encrypted and secure.
            We use industry-standard SSL encryption to protect your data.
        </div>
    </div>

    <script>
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedMethod').value = this.dataset.method;
            });
        });

        // Razorpay integration
        document.getElementById('payButton').addEventListener('click', function() {
            const button = this;
            const originalText = button.innerHTML;

            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            button.disabled = true;

            // Create payment order
            fetch('<?= BASE_URL ?>/payment/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(new FormData(document.getElementById('paymentForm')))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Initialize Razorpay
                    const options = {
                        key: data.key,
                        amount: data.amount * 100, // Convert to paisa
                        currency: 'INR',
                        order_id: data.order_id,
                        name: 'APS Dream Home',
                        description: 'Property Booking Payment',
                        image: '<?= BASE_URL ?>/assets/images/logo.png',
                        handler: function(response) {
                            // Verify payment
                            verifyPayment(response);
                        },
                        prefill: {
                            name: '<?= $_SESSION['user_name'] ?? '' ?>',
                            email: '<?= $_SESSION['user_email'] ?? '' ?>',
                            contact: '<?= $_SESSION['user_phone'] ?? '' ?>'
                        },
                        theme: {
                            color: '#667eea'
                        },
                        modal: {
                            ondismiss: function() {
                                button.innerHTML = originalText;
                                button.disabled = false;
                            }
                        }
                    };

                    const rzp = new Razorpay(options);
                    rzp.open();
                } else {
                    alert('Error: ' + data.error);
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Payment processing failed. Please try again.');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });

        function verifyPayment(response) {
            fetch('<?= BASE_URL ?>/payment/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= BASE_URL ?>/payment/success?payment_id=' + response.razorpay_payment_id;
                } else {
                    alert('Payment verification failed: ' + data.error);
                    window.location.href = '<?= BASE_URL ?>/payment/failed?error=' + encodeURIComponent(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Payment verification failed. Please contact support.');
                window.location.href = '<?= BASE_URL ?>/payment/failed?error=Verification failed';
            });
        }
    </script>
</body>
</html>
