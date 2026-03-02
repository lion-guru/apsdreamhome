<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .success-container {
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 40px 20px;
            position: relative;
        }

        .success-icon {
            font-size: 100px;
            margin-bottom: 30px;
            animation: bounceIn 1s ease-out;
            color: white;
        }

        .success-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .success-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .success-message {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .next-steps {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            backdrop-filter: blur(10px);
        }

        .step-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            text-align: left;
        }

        .step-number {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .step-content {
            flex: 1;
        }

        .step-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .step-desc {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            min-width: 150px;
        }

        .btn-primary-action {
            background: white;
            color: #28a745;
        }

        .btn-primary-action:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary-action {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-secondary-action:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .reference-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
            backdrop-filter: blur(10px);
        }

        .reference-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffc107;
            margin-bottom: 10px;
        }

        .social-share {
            margin-top: 30px;
        }

        .social-share a {
            display: inline-block;
            margin: 0 10px;
            color: white;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .social-share a:hover {
            color: #ffc107;
            transform: scale(1.2);
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .celebration-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: celebrate 3s ease-in-out infinite;
        }

        @keyframes celebrate {
            0%, 100% {
                transform: translateY(0px) scale(0);
                opacity: 0;
            }
            50% {
                transform: translateY(-100px) scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Celebration Particles -->
    <div class="celebration-particles" id="particles"></div>

    <div class="success-container">
        <!-- Success Icon -->
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <!-- Main Title -->
        <h1 class="success-title">Thank You!</h1>
        <p class="success-subtitle">Your submission has been received successfully</p>

        <!-- Success Message -->
        <div class="success-message">
            <p>We have received your inquiry and our team will review it shortly. You should receive a confirmation email within the next few minutes.</p>
            <p><strong>What happens next?</strong> Our customer service team will contact you within 24 hours to discuss your requirements and provide personalized assistance.</p>
        </div>

        <!-- Reference Information -->
        <div class="reference-info">
            <div class="reference-number">REF: #<?php echo \App\Helpers\SecurityHelper::secureRandomInt(100000, 999999); ?></div>
            <p>Please save this reference number for your records. Quote it when contacting us about this inquiry.</p>
        </div>

        <!-- Next Steps -->
        <div class="next-steps">
            <h4><i class="fas fa-list-ol me-2"></i>What Happens Next?</h4>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">Email Confirmation</div>
                    <div class="step-desc">You'll receive an email confirmation within 5 minutes</div>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">Team Review</div>
                    <div class="step-desc">Our specialists will review your requirements within 24 hours</div>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">Personal Contact</div>
                    <div class="step-desc">We'll contact you to discuss details and next steps</div>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <div class="step-title">Customized Solution</div>
                    <div class="step-desc">Receive a tailored proposal based on your needs</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="index.php" class="btn-action btn-primary-action">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
            <a href="properties.php" class="btn-action btn-secondary-action">
                <i class="fas fa-search me-2"></i>Browse Properties
            </a>
            <a href="contact.php" class="btn-action btn-secondary-action">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
        </div>

        <!-- Social Share -->
        <div class="social-share">
            <p class="mb-2">Share your experience:</p>
            <a href="#" onclick="shareOnFacebook()"><i class="fab fa-facebook-f"></i></a>
            <a href="#" onclick="shareOnTwitter()"><i class="fab fa-twitter"></i></a>
            <a href="#" onclick="shareOnLinkedIn()"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create celebration particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 20;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';

                // Random size
                const size = Math.random() * 6 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';

                // Random position
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = '100%';

                // Random animation delay
                particle.style.animationDelay = Math.random() * 3 + 's';
                particle.style.animationDuration = (Math.random() * 2 + 2) + 's';

                particlesContainer.appendChild(particle);
            }
        }

        // Social sharing functions
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('Just inquired about properties with APS Dream Homes!');
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('Just started my property search with APS Dream Homes! 🏠');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
        }

        function shareOnLinkedIn() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('Property Inquiry - APS Dream Homes');
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}&title=${title}`, '_blank');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
        });
    </script>
</body>
</html>
