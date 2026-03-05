<?php

// TODO: Add proper error handling with try-catch blocks

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Construction - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .construction-container {
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 40px 20px;
            position: relative;
            z-index: 2;
        }

        .construction-icon {
            font-size: 120px;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
            color: rgba(255, 255, 255, 0.9);
        }

        .construction-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .construction-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .progress-container {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            height: 30px;
            overflow: hidden;
            margin: 30px 0;
            backdrop-filter: blur(10px);
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #fff 0%, #f8f9fa 100%);
            border-radius: 50px;
            width: 75%;
            animation: progressAnimation 3s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-weight: 600;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.9);
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

        @keyframes progressAnimation {
            0% {
                width: 0%;
            }
            50% {
                width: 85%;
            }
            100% {
                width: 75%;
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
            .construction-title {
                font-size: 2.5rem;
            }
            
            .construction-subtitle {
                font-size: 1.2rem;
            }
            
            .construction-icon {
                font-size: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="construction-container">
        <div class="construction-icon">
            <i class="fas fa-hard-hat"></i>
        </div>
        
        <h1 class="construction-title">Under Construction</h1>
        <p class="construction-subtitle">We're building something amazing for you!</p>
        
        <div class="progress-container">
            <div class="progress-bar">
                <span>75% Complete</span>
            </div>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h4>Modern Design</h4>
                <p>Beautiful and intuitive interface</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h4>Mobile Friendly</h4>
                <p>Perfect on all devices</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h4>Lightning Fast</h4>
                <p>Optimized performance</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h4>Secure</h4>
                <p>Your data is protected</p>
            </div>
        </div>
        
        <div class="contact-info">
            <h3>Get Notified When We Launch!</h3>
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
    </script>
</body>
</html>
