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

        .construction-message {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .progress-bar {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            height: 8px;
            margin: 30px 0;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            background: linear-gradient(90deg, #fff 0%, #f8f9fa 100%);
            height: 100%;
            width: 75%;
            border-radius: 50px;
            animation: progress 3s ease-in-out infinite;
        }

        .construction-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.15);
            padding: 25px 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #fff;
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .feature-desc {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .contact-info {
            margin-top: 40px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: white;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: #f8f9fa;
            transform: scale(1.2);
        }

        .particles {
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
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        @keyframes progress {
            0% {
                width: 0%;
            }
            50% {
                width: 75%;
            }
            100% {
                width: 100%;
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.7;
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.3;
            }
        }

        .countdown {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }

        .countdown-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px 15px;
            border-radius: 10px;
            min-width: 80px;
        }

        .countdown-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
        }

        .countdown-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .back-home {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .back-home:hover {
            background: white;
            color: #f39c12;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <!-- Animated Background Particles -->
    <div class="particles" id="particles"></div>

    <div class="construction-container">
        <!-- Construction Icon -->
        <div class="construction-icon">
            <i class="fas fa-tools"></i>
        </div>

        <!-- Main Title -->
        <h1 class="construction-title">Under Construction</h1>
        <p class="construction-subtitle">Something Amazing is Coming Soon!</p>

        <!-- Construction Message -->
        <div class="construction-message">
            <p>We're working hard to bring you an incredible experience. This section of our website is currently being upgraded with new features and improvements.</p>
            <p><strong>Expected Completion:</strong> Our development team is working diligently to complete this section. Check back soon for updates!</p>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        <p style="font-size: 0.9rem; opacity: 0.8;">75% Complete</p>

        <!-- Countdown Timer (Optional) -->
        <div class="countdown" id="countdown">
            <div class="countdown-item">
                <span class="countdown-number" id="days">00</span>
                <span class="countdown-label">Days</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-number" id="hours">00</span>
                <span class="countdown-label">Hours</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-number" id="minutes">00</span>
                <span class="countdown-label">Minutes</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-number" id="seconds">00</span>
                <span class="countdown-label">Seconds</span>
            </div>
        </div>

        <!-- Features Coming Soon -->
        <div class="construction-features">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="feature-title">Property Search</div>
                <div class="feature-desc">Advanced search with filters and maps</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="feature-title">Mortgage Calculator</div>
                <div class="feature-desc">Calculate EMI and loan eligibility</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <div class="feature-title">Virtual Tours</div>
                <div class="feature-desc">360° property tours and videos</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="feature-title">Mobile App</div>
                <div class="feature-desc">Native iOS and Android apps</div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="contact-info">
            <h4><i class="fas fa-phone me-2"></i>Contact Us During Construction</h4>
            <p>For immediate assistance or inquiries, please contact us:</p>
            <div class="row mt-3">
                <div class="col-md-4">
                    <strong>Phone:</strong><br>
                    +91-522-400-1234
                </div>
                <div class="col-md-4">
                    <strong>Email:</strong><br>
                    info@apsdreamhomes.com
                </div>
                <div class="col-md-4">
                    <strong>Office:</strong><br>
                    123, Gomti Nagar, Lucknow
                </div>
            </div>

            <!-- Social Links -->
            <div class="social-links">
                <a href="https://facebook.com/apsdreamhomes" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/apsdreamhomes" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://instagram.com/apsdreamhomes" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://linkedin.com/company/apsdreamhomes" target="_blank"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>

        <!-- Back to Home -->
        <a href="index.php" class="back-home">
            <i class="fas fa-home me-2"></i>Back to Homepage
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';

                // Random size
                const size = Math.random() * 4 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';

                // Random position
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';

                // Random animation delay
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';

                particlesContainer.appendChild(particle);
            }
        }

        // Countdown Timer
        function updateCountdown() {
            const targetDate = new Date('2024-12-31T23:59:59').getTime();
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById('days').textContent = days.toString().padStart(2, '0');
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            setInterval(updateCountdown, 1000);
        });

        // Smooth scroll for any internal links
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
