<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .maintenance-container {
            text-align: center;
            color: white;
            max-width: 700px;
            padding: 40px 20px;
            position: relative;
        }

        .maintenance-icon {
            font-size: 80px;
            margin-bottom: 30px;
            animation: spin 3s linear infinite;
            color: rgba(255, 255, 255, 0.9);
        }

        .maintenance-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .maintenance-subtitle {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .maintenance-message {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .estimated-time {
            background: rgba(23, 162, 184, 0.2);
            border: 2px solid #17a2b8;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            backdrop-filter: blur(10px);
        }

        .time-left {
            font-size: 2rem;
            font-weight: 700;
            color: #17a2b8;
            margin-bottom: 10px;
        }

        .status-updates {
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            backdrop-filter: blur(10px);
        }

        .update-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border-left: 4px solid #17a2b8;
        }

        .update-icon {
            margin-right: 15px;
            color: #28a745;
            font-size: 1.2rem;
        }

        .update-text {
            flex: 1;
            text-align: left;
        }

        .update-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .update-desc {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .contact-during-maintenance {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            backdrop-filter: blur(10px);
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
            color: #17a2b8;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .progress-ring {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: inline-block;
            position: relative;
            margin: 20px auto;
        }

        .progress-ring-circle {
            stroke: rgba(255, 255, 255, 0.2);
            fill: transparent;
            stroke-width: 4;
        }

        .progress-ring-circle-progress {
            stroke: #fff;
            fill: transparent;
            stroke-width: 4;
            stroke-linecap: round;
            transform-origin: center;
            transform: rotate(-90deg);
            transition: stroke-dasharray 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <!-- Maintenance Icon -->
        <div class="maintenance-icon">
            <i class="fas fa-cog"></i>
        </div>

        <!-- Progress Ring (Optional) -->
        <div class="progress-ring">
            <svg width="120" height="120">
                <circle class="progress-ring-circle" cx="60" cy="60" r="54" />
                <circle class="progress-ring-circle-progress" cx="60" cy="60" r="54"
                        stroke-dasharray="339.292" stroke-dashoffset="84.823" />
            </svg>
        </div>

        <!-- Main Title -->
        <h1 class="maintenance-title">Scheduled Maintenance</h1>
        <p class="maintenance-subtitle">We're improving your experience</p>

        <!-- Maintenance Message -->
        <div class="maintenance-message">
            <p>Our website is currently undergoing scheduled maintenance to enhance your browsing experience and add new features.</p>
            <p><strong>Estimated Downtime:</strong> 2-4 hours</p>
            <p>We apologize for any inconvenience and appreciate your patience.</p>
        </div>

        <!-- Estimated Time -->
        <div class="estimated-time">
            <div class="time-left" id="timeLeft">Calculating...</div>
            <p>Estimated time remaining</p>
        </div>

        <!-- Status Updates -->
        <div class="status-updates">
            <h4><i class="fas fa-info-circle me-2"></i>Maintenance Updates</h4>

            <div class="update-item">
                <div class="update-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="update-text">
                    <div class="update-title">Database Optimization</div>
                    <div class="update-desc">Improving query performance and data structure</div>
                </div>
            </div>

            <div class="update-item">
                <div class="update-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="update-text">
                    <div class="update-title">Security Updates</div>
                    <div class="update-desc">Implementing latest security patches and protocols</div>
                </div>
            </div>

            <div class="update-item">
                <div class="update-icon">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="update-text">
                    <div class="update-title">New Features</div>
                    <div class="update-desc">Adding exciting new features (currently in progress)</div>
                </div>
            </div>
        </div>

        <!-- Contact During Maintenance -->
        <div class="contact-during-maintenance">
            <h5><i class="fas fa-phone me-2"></i>Need Immediate Assistance?</h5>
            <p>Contact us directly during maintenance:</p>
            <div class="row mt-3">
                <div class="col-md-6">
                    <strong>Emergency Phone:</strong><br>
                    +91-522-400-1234
                </div>
                <div class="col-md-6">
                    <strong>Email Support:</strong><br>
                    support@apsdreamhomes.com
                </div>
            </div>
        </div>

        <!-- Back to Home (if accessible) -->
        <a href="index.php" class="back-home">
            <i class="fas fa-home me-2"></i>Try Homepage
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simulate maintenance time remaining
        function updateTimeLeft() {
            const maintenanceEnd = new Date(Date.now() + 2 * 60 * 60 * 1000); // 2 hours from now
            const now = new Date();
            const timeLeft = maintenanceEnd - now;

            if (timeLeft > 0) {
                const hours = Math.floor(timeLeft / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));

                document.getElementById('timeLeft').textContent =
                    `${hours}h ${minutes}m remaining`;
            } else {
                document.getElementById('timeLeft').textContent = 'Maintenance completed';
            }
        }

        // Update time every minute
        document.addEventListener('DOMContentLoaded', function() {
            updateTimeLeft();
            setInterval(updateTimeLeft, 60000);
        });
    </script>
</body>
</html>
