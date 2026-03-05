<?php

// TODO: Add proper error handling with try-catch blocks

?>
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
            overflow: hidden;
        }

        .thank-you-container {
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

        .thank-you-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .thank-you-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
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
            .thank-you-title {
                font-size: 2.5rem;
            }
            
            .thank-you-subtitle {
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
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="thank-you-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="thank-you-title">Thank You!</h1>
        <p class="thank-you-subtitle">Your submission has been received successfully.</p>
        
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>" class="btn-custom btn-primary-custom">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
            <a href="<?= BASE_URL ?>properties" class="btn-custom btn-secondary-custom">
                <i class="fas fa-building"></i>
                Browse Properties
            </a>
            <a href="<?= BASE_URL ?>contact" class="btn-custom btn-secondary-custom">
                <i class="fas fa-phone"></i>
                Contact Us
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
        
        // Auto-redirect after 10 seconds (optional)
        setTimeout(() => {
            const autoRedirect = confirm('Would you like to return to the homepage?');
            if (autoRedirect) {
                window.location.href = '<?= BASE_URL ?>';
            }
        }, 10000);
    </script>
</body>
</html>
