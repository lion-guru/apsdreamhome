<?php

// TODO: Add proper error handling with try-catch blocks

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Payment Successful' ?> - APS Dream Home</title>
    <?php
    if (!isset($payment_id)) {
        $payment_id = 'N/A'; // Default value if $payment_id is not set
    }
    ?>
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
            overflow: hidden;
        }

        .payment-success-container {
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 40px 20px;
            position: relative;
            z-index: 2;
        }

        .success-icon {
            font-size: 120px;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
            color: rgba(255, 255, 255, 0.9);
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

        .payment-details {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            margin: 30px 0;
        }

        .payment-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .payment-info:last-child {
            border-bottom: none;
        }

        .payment-label {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .payment-value {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .btn-custom {
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary-custom {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-primary-custom:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .contact-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            margin-top: 40px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            font-size: 1.1rem;
        }

        .contact-item i {
            margin-right: 15px;
            font-size: 1.3rem;
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(720deg);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .success-title {
                font-size: 2.5rem;
            }
            
            .success-subtitle {
                font-size: 1.2rem;
            }
            
            .success-icon {
                font-size: 80px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-custom {
                width: 100%;
                max-width: 300px;
            }
            
            .payment-info {
                flex-direction: column;
                text-align: center;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="payment-success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="success-title">Payment Successful!</h1>
        <p class="success-subtitle">Your payment has been processed successfully.</p>
        
        <div class="payment-details">
            <h3>Payment Details</h3>
            <div class="payment-info">
                <span class="payment-label">Payment ID:</span>
                <span class="payment-value"><?= htmlspecialchars($payment_id) ?></span>
            </div>
            <div class="payment-info">
                <span class="payment-label">Status:</span>
                <span class="payment-value">Completed</span>
            </div>
            <div class="payment-info">
                <span class="payment-label">Date:</span>
                <span class="payment-value"><?= date('d M Y, h:i A') ?></span>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>" class="btn-custom btn-primary-custom">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
            <a href="<?= BASE_URL ?>dashboard" class="btn-custom btn-secondary-custom">
                <i class="fas fa-tachometer-alt"></i>
                My Dashboard
            </a>
            <a href="<?= BASE_URL ?>properties" class="btn-custom btn-secondary-custom">
                <i class="fas fa-building"></i>
                Browse Properties
            </a>
        </div>
        
        <div class="contact-info">
            <h3>Need Assistance?</h3>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>info@apsdreamhome.com</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>+91 98765 43210</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Gorakhpur, Uttar Pradesh</span>
            </div>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 10 + 5;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize particles when page loads
        window.addEventListener('load', createParticles);
        
        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Auto-redirect after 15 seconds (optional)
        setTimeout(() => {
            const autoRedirect = confirm('Would you like to view your dashboard?');
            if (autoRedirect) {
                window.location.href = '<?= BASE_URL ?>dashboard';
            }
        }, 15000);
    </script>
</body>
</html>
