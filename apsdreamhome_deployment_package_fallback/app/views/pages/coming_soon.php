<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .coming-soon-container {
            text-align: center;
            color: white;
            max-width: 900px;
            width: 100%;
            padding: 40px 20px;
            position: relative;
            z-index: 2;
            box-sizing: border-box;
        }

        .coming-soon-icon {
            font-size: 100px;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
            color: #f39c12;
        }

        .coming-soon-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #f39c12, #e67e22);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .coming-soon-subtitle {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #ecf0f1;
        }

        .coming-soon-description {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 40px;
            color: #bdc3c7;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .launch-date {
            background: rgba(243, 156, 18, 0.1);
            border: 2px solid #f39c12;
            border-radius: 15px;
            padding: 30px;
            margin: 40px 0;
            backdrop-filter: blur(10px);
        }

        .launch-date h3 {
            color: #f39c12;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .countdown {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .countdown-item {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            padding: 20px 15px;
            border-radius: 10px;
            min-width: 80px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .countdown-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
            color: white;
        }

        .countdown-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
        }

        .features-preview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 50px 0;
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 30px 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #f39c12;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: white;
        }

        .feature-description {
            color: #bdc3c7;
            line-height: 1.6;
        }

        .notification-signup {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            margin-top: 40px;
            backdrop-filter: blur(10px);
        }

        .notification-form {
            max-width: 400px;
            margin: 0 auto;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 25px;
            padding: 12px 20px;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-notify {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-notify:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(243, 156, 18, 0.3);
        }

        .social-links {
            margin-top: 30px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 15px;
            color: white;
            font-size: 2rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: #f39c12;
            transform: scale(1.2);
        }

        .back-home {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .back-home:hover {
            background: white;
            color: #2c3e50;
            transform: translateY(-2px);
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #2c3e50, #34495e, #2c3e50, #34495e);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>

    <div class="coming-soon-container">
        <!-- Coming Soon Icon -->
        <div class="coming-soon-icon">
            <i class="fas fa-rocket"></i>
        </div>

        <!-- Main Title -->
        <h1 class="coming-soon-title">Coming Soon</h1>
        <h2 class="coming-soon-subtitle">Exciting New Features Are On The Way!</h2>
        <p class="coming-soon-description">
            We're working on something amazing that will revolutionize your real estate experience.
            Stay tuned for our upcoming launch!
        </p>

        <!-- Launch Date -->
        <div class="launch-date">
            <h3><i class="fas fa-calendar-alt me-2"></i>Expected Launch Date</h3>
            <p class="h4 mb-3">December 31, 2024</p>

            <!-- Countdown Timer -->
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
        </div>

        <!-- Features Preview -->
        <div class="features-preview">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="feature-title">Advanced Property Search</div>
                <div class="feature-description">Find your dream home with our intelligent search system featuring filters, maps, and AI-powered recommendations.</div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="feature-title">Mobile Application</div>
                <div class="feature-description">Native iOS and Android apps for searching properties on-the-go with offline capabilities and push notifications.</div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <div class="feature-title">Virtual Reality Tours</div>
                <div class="feature-description">Experience properties through immersive 360° virtual tours and high-quality video walkthroughs.</div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="feature-title">AI-Powered Assistant</div>
                <div class="feature-description">Get instant answers to your real estate questions with our intelligent chatbot and recommendation engine.</div>
            </div>
        </div>

        <!-- Email Notification Signup -->
        <div class="notification-signup">
            <h4><i class="fas fa-bell me-2"></i>Get Notified When We Launch</h4>
            <p class="mb-4">Be the first to know when our new features go live!</p>

            <form class="notification-form" id="notificationForm">
                <div class="mb-3">
                    <input type="text" class="form-control" id="notifyName" placeholder="Your Name" required>
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" id="notifyEmail" placeholder="Your Email Address" required>
                </div>
                <button type="submit" class="btn-notify">
                    <i class="fas fa-paper-plane me-2"></i>Notify Me
                </button>
            </form>
        </div>

        <!-- Social Links -->
        <div class="social-links">
            <p class="mb-3">Follow us for updates:</p>
            <a href="https://facebook.com/apsdreamhomes" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://twitter.com/apsdreamhomes" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://instagram.com/apsdreamhomes" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://linkedin.com/company/apsdreamhomes" target="_blank"><i class="fab fa-linkedin-in"></i></a>
        </div>

        <!-- Back to Home -->
        <a href="index.php" class="back-home">
            <i class="fas fa-home me-2"></i>Back to Homepage
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        // Notification Form
        document.getElementById('notificationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('notifyName').value;
            const email = document.getElementById('notifyEmail').value;

            if (name && email) {
                // Simulate notification signup
                alert(`Thank you ${name}! You'll be notified when we launch.`);

                // Reset form
                this.reset();

                // In a real application, this would send data to your server
                // fetch('notification_handler.php', {
                //     method: 'POST',
                //     body: new FormData(this)
                // });
            }
        });

        // Initialize countdown
        document.addEventListener('DOMContentLoaded', function() {
            setInterval(updateCountdown, 1000);
        });
    </script>
</body>
</html>
