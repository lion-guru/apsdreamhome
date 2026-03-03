<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Payment Failed' ?> - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .failed-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            padding: 40px;
        }
        .failed-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 3rem;
            color: white;
        }
        .failed-title {
            color: #dc3545;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error-details {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            color: #721c24;
        }
        .help-section {
            background: #e9ecef;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        .help-section h5 {
            color: #495057;
            margin-bottom: 15px;
        }
        .help-section .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .contact-item {
            padding: 15px;
            background: white;
            border-radius: 8px;
            text-align: center;
        }
        .contact-item i {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 8px;
        }
        .btn-retry {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 25px;
            margin: 10px;
        }
        .btn-home {
            background: #6c757d;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 25px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="container failed-container">
        <!-- Failed Icon -->
        <div class="failed-icon">
            <i class="fas fa-times"></i>
        </div>

        <!-- Failed Message -->
        <h1 class="failed-title">
            <i class="fas fa-exclamation-triangle"></i> Payment Failed
        </h1>
        <p class="lead text-muted">Unfortunately, your payment could not be processed.</p>

        <!-- Error Details -->
        <?php if (isset($error) && !empty($error)): ?>
        <div class="error-details">
            <h6><i class="fas fa-info-circle"></i> Error Details:</h6>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <!-- Common Issues -->
        <div class="help-section">
            <h5><i class="fas fa-question-circle"></i> Common Payment Issues</h5>
            <div class="row mt-3">
                <div class="col-md-6">
                    <ul class="text-start" style="list-style: none; padding: 0;">
                        <li><i class="fas fa-times-circle text-danger"></i> Insufficient balance</li>
                        <li><i class="fas fa-times-circle text-danger"></i> Card expired</li>
                        <li><i class="fas fa-times-circle text-danger"></i> Bank declined transaction</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="text-start" style="list-style: none; padding: 0;">
                        <li><i class="fas fa-times-circle text-danger"></i> Incorrect card details</li>
                        <li><i class="fas fa-times-circle text-danger"></i> Network connectivity</li>
                        <li><i class="fas fa-times-circle text-danger"></i> Daily limit exceeded</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="help-section">
            <h5><i class="fas fa-headset"></i> Need Help?</h5>
            <p>Contact our support team for assistance with your payment:</p>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div>+91-XXXXXXXXXX</div>
                    <small>Call us</small>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>support@apsdreamhome.com</div>
                    <small>Email us</small>
                </div>
                <div class="contact-item">
                    <i class="fas fa-comments"></i>
                    <div>Live Chat</div>
                    <small>Available 24/7</small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <button class="btn btn-retry" onclick="window.history.back()">
                <i class="fas fa-redo"></i> Try Again
            </button>
            <a href="tel:+91XXXXXXXXXX" class="btn btn-outline-danger">
                <i class="fas fa-phone"></i> Call Support
            </a>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-primary">
                <i class="fas fa-search"></i> Browse Properties
            </a>
            <a href="<?= BASE_URL ?>/" class="btn btn-home">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>

        <!-- Auto redirect after 15 seconds -->
        <div class="mt-4 text-muted">
            <small>Redirecting to homepage in <span id="countdown">15</span> seconds...</small>
        </div>
    </div>

    <script>
        // Countdown timer
        let countdown = 15;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '<?= BASE_URL ?>/';
            }
        }, 1000);

        // Alternative payment methods suggestion
        document.addEventListener('DOMContentLoaded', function() {
            const errorText = '<?= strtolower($error ?? "") ?>';

            if (errorText.includes('card') || errorText.includes('declined')) {
                // Show alternative payment methods
                const alternativeDiv = document.createElement('div');
                alternativeDiv.className = 'alert alert-warning mt-3';
                alternativeDiv.innerHTML = `
                    <h6><i class="fas fa-lightbulb"></i> Alternative Payment Methods</h6>
                    <p class="mb-2">Try using UPI, Net Banking, or Wallet for faster processing:</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <span class="badge bg-primary"><i class="fab fa-google-pay"></i> Google Pay</span>
                        <span class="badge bg-info"><i class="fab fa-paypal"></i> Paytm</span>
                        <span class="badge bg-success"><i class="fas fa-mobile-alt"></i> PhonePe</span>
                        <span class="badge bg-warning text-dark"><i class="fas fa-university"></i> Net Banking</span>
                    </div>
                `;

                document.querySelector('.failed-container').appendChild(alternativeDiv);
            }
        });
    </script>
</body>
</html>
