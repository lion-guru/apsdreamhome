<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become Associate - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .referral-code-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            margin: -60px auto 60px;
            position: relative;
        }

        .referral-code-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 5px;
            text-align: center;
            margin-bottom: 20px;
        }

        .copy-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .copy-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }

        .benefits {
            padding: 80px 0;
            background: white;
        }

        .benefit-card {
            text-align: center;
            padding: 40px 20px;
            border-radius: 20px;
            background: #f8f9fa;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .benefit-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }

        .benefit-card h4 {
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }

        .benefit-card p {
            color: #666;
            margin: 0;
        }

        .earnings-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .earning-card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .earning-amount {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .cta-section {
            padding: 80px 0;
            background: white;
            text-align: center;
        }

        .btn-join {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 20px 50px;
            border-radius: 30px;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-join:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .qr-code {
            background: white;
            padding: 20px;
            border-radius: 15px;
            display: inline-block;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .referral-code-display {
                font-size: 1.8rem;
                letter-spacing: 3px;
            }
            
            .earning-amount {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><i class="fas fa-users me-3"></i>Become an Associate</h1>
            <p>Join APS Dream Home and start earning today! Build your network, earn commissions, and grow your income.</p>
            <a href="#referral-code" class="btn btn-light btn-lg px-5 py-3 fw-bold rounded-pill">
                <i class="fas fa-arrow-down me-2"></i>Get Your Referral Code
            </a>
        </div>
    </div>

    <!-- Referral Code Section -->
    <div class="container" id="referral-code">
        <div class="referral-code-box">
            <h3 class="text-center mb-4"><i class="fas fa-ticket-alt me-2 text-primary"></i>Your Company Referral Code</h3>
            <p class="text-center text-muted mb-4">Use this code to join as Associate/Agent. No referral needed!</p>
            <div class="referral-code-display">APS2025COMP</div>
            <button class="copy-btn" onclick="copyReferralCode()">
                <i class="fas fa-copy me-2"></i>Copy Referral Code
            </button>
            <div class="text-center mt-4">
                <div class="qr-code">
                    <div style="width: 150px; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-qrcode fa-4x text-muted"></i>
                    </div>
                    <small class="text-muted d-block mt-2">Scan to join instantly</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <div class="benefits">
        <div class="container">
            <h2 class="text-center mb-5"><i class="fas fa-star me-2 text-primary"></i>Why Join as Associate?</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <h4>Earn Commissions</h4>
                        <p>Earn up to 5% commission on every property sale you refer</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h4>Build Network</h4>
                        <p>Create your team and earn from their referrals too</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h4>Wallet System</h4>
                        <p>Track earnings in your wallet and transfer to EMI</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Full Support</h4>
                        <p>Get training and support from our experienced team</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Growth Tracking</h4>
                        <p>Monitor your performance with detailed analytics</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h4>Referral Rewards</h4>
                        <p>Earn ₹200 for every associate you refer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings Section -->
    <div class="earnings-section">
        <div class="container">
            <h2 class="mb-5"><i class="fas fa-money-bill-wave me-2"></i>Potential Earnings</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="earning-card">
                        <div class="earning-amount">₹200</div>
                        <p>Per Associate Referral</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="earning-card">
                        <div class="earning-amount">₹250</div>
                        <p>Per Agent Referral</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="earning-card">
                        <div class="earning-amount">₹100</div>
                        <p>Per Customer Referral</p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <p class="fs-4">Plus <strong>5% commission</strong> on every property sale!</p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
        <div class="container">
            <h2 class="mb-4">Ready to Start Earning?</h2>
            <p class="text-muted mb-5">Join now with company referral code and start your journey</p>
            <a href="/associate/register?ref=APS2025COMP" class="btn-join">
                <i class="fas fa-user-plus me-2"></i>Join as Associate
            </a>
            <div class="mt-4">
                <a href="/agent/register?ref=APS2025COMP" class="btn btn-outline-primary btn-lg px-4 py-3 rounded-pill">
                    <i class="fas fa-briefcase me-2"></i>Join as Agent
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-dark text-white py-4 text-center">
        <div class="container">
            <p class="mb-0">© 2025 APS Dream Home. All rights reserved.</p>
        </div>
    </div>

    <script>
        function copyReferralCode() {
            const referralCode = 'APS2025COMP';
            navigator.clipboard.writeText(referralCode).then(() => {
                alert('Referral code copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>
</body>
</html>
