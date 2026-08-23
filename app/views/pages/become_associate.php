<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('assoc_page_title') ?></title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
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
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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
            .hero-split {
                flex-direction: column;
            }
        }

        /* --- 3D ID Card Journey Styles --- */
        .hero-split {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            text-align: left;
        }
        .hero-text-content {
            flex: 1;
        }
        .hero-card-content {
            flex: 1;
            display: flex;
            justify-content: center;
            perspective: 1000px; /* 3D Perspective */
        }

        .id-card-container {
            width: 320px;
            height: 480px;
            position: relative;
            cursor: pointer;
        }

        .id-card {
            width: 100%;
            height: 100%;
            position: absolute;
            transform-style: preserve-3d;
            transition: transform 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            border-radius: 20px;
        }

        /* The Flip Class */
        .id-card.is-flipped {
            transform: rotateY(180deg);
        }

        .card-face {
            width: 100%;
            height: 100%;
            position: absolute;
            backface-visibility: hidden;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Front: Normal State */
        .card-front {
            background: #fff;
            color: #333;
        }
        .cf-header {
            background: #f1f5f9;
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
        }
        .cf-header h4 { margin: 0; color: #64748b; font-weight: 600; font-size: 1.1rem; }
        .cf-photo-area {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background: #f8fafc;
        }
        .cf-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #e2e8f0;
            margin-bottom: 15px;
            border: 4px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #94a3b8;
            overflow: hidden;
        }
        .cf-photo img { width: 100%; height: 100%; object-fit: cover; }
        .cf-name { font-weight: 700; font-size: 1.4rem; color: #1e293b; }
        .cf-status { color: #64748b; font-size: 0.9rem; }
        .cf-footer {
            padding: 15px;
            text-align: center;
            background: #f1f5f9;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        /* Back: Professional State */
        .card-back {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            transform: rotateY(180deg);
            color: #fff;
            border: 2px solid #38bdf8;
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.4), inset 0 0 20px rgba(56, 189, 248, 0.2);
            position: relative;
        }
        
        /* Holographic overlay */
        .card-back::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(125deg, transparent 20%, rgba(255,255,255,0.1) 40%, rgba(255,255,255,0.3) 50%, transparent 60%);
            background-size: 200% 200%;
            animation: holoSweep 4s infinite linear;
            pointer-events: none;
            z-index: 10;
        }
        @keyframes holoSweep {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .cb-header {
            padding: 20px;
            text-align: center;
            background: linear-gradient(to right, #0d9488, #0f766e);
            border-bottom: 2px solid #5eead4;
        }
        .cb-header h4 { margin: 0; color: #fff; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; font-size: 1rem; }
        .cb-header small { color: #ccfbf1; font-size: 0.75rem; letter-spacing: 2px;}
        
        .cb-photo-area {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .cb-photo {
            width: 140px;
            height: 140px;
            border-radius: 10px;
            background: #334155;
            margin-bottom: 20px;
            border: 2px solid #38bdf8;
            padding: 4px;
            position: relative;
            overflow: hidden;
        }
        .cb-photo img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px;}
        
        .cb-details {
            width: 100%;
        }
        .cb-name { font-weight: 800; font-size: 1.5rem; color: #fff; margin-bottom: 5px; text-align: center; text-transform: uppercase;}
        .cb-role { color: #38bdf8; font-size: 1rem; font-weight: 600; text-align: center; margin-bottom: 15px;}
        
        .cb-meta {
            display: flex;
            justify-content: space-between;
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .cb-meta div { display: flex; flex-direction: column; }
        .cb-meta span { color: #94a3b8; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;}
        .cb-meta strong { color: #fff; font-family: monospace; font-size: 0.9rem;}
        
        .cb-footer {
            padding: 15px;
            text-align: center;
            background: #020617;
            font-size: 0.75rem;
            color: #64748b;
            border-top: 1px solid #1e293b;
        }

        /* Scanline Animation */
        .scanline {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #38bdf8;
            box-shadow: 0 0 15px #38bdf8, 0 0 30px #38bdf8;
            opacity: 0;
            z-index: 20;
        }

        .is-scanning .scanline {
            opacity: 1;
            animation: scan 1.5s ease-in-out forwards;
        }

        @keyframes scan {
            0% { top: -10%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 110%; opacity: 0; }
        }

        /* Button Pulse */
        .btn-journey {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            color: white;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.4);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .btn-journey:hover {
            transform: scale(1.05);
            color: white;
        }
        .btn-journey::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shine 3s infinite;
        }
        @keyframes shine {
            0% { left: -100%; }
            20% { left: 200%; }
            100% { left: 200%; }
        }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <!-- Hero Section with Interactive Journey -->
    <div class="hero">
        <div class="container">
            <div class="hero-split">
                
                <!-- Left: Content -->
                <div class="hero-text-content">
                    <h1><i class="fas fa-rocket me-3"></i>Transform Your Career</h1>
                    <p class="mb-4">Begin your journey from a regular enthusiast to a Premium Certified APS Dream Home Agent. Experience the transformation and unlock unlimited earning potential today.</p>
                    
                    <button class="btn btn-journey mb-3" id="btn-start-journey" onclick="startAgentJourney()">
                        <i class="fas fa-magic me-2"></i>Start Your Journey
                    </button>
                    <p class="small text-white-50"><i class="fas fa-info-circle me-1"></i>Click the button or tap the card to see the transformation.</p>
                </div>
                
                <!-- Right: 3D ID Card -->
                <div class="hero-card-content">
                    <div class="id-card-container" id="id-card-container" onclick="startAgentJourney()">
                        <div class="id-card" id="agent-id-card">
                            
                            <!-- Front (Normal State) -->
                            <div class="card-face card-front">
                                <div class="scanline"></div>
                                <div class="cf-header">
                                    <h4>Guest Profile</h4>
                                </div>
                                <div class="cf-photo-area">
                                    <div class="cf-photo">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="cf-name">New Visitor</div>
                                    <div class="cf-status">Unregistered</div>
                                </div>
                                <div class="cf-footer">
                                    Join APS Dream Home today
                                </div>
                            </div>
                            
                            <!-- Back (Professional State) -->
                            <div class="card-face card-back">
                                <div class="cb-header">
                                    <h4>APS Dream Home</h4>
                                    <small>Official Representative</small>
                                </div>
                                <div class="cb-photo-area">
                                    <div class="cb-photo">
                                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=256&q=80" alt="Professional Agent">
                                    </div>
                                    <div class="cb-details">
                                        <div class="cb-name">Certified Agent</div>
                                        <div class="cb-role"><i class="fas fa-check-circle me-1"></i>Premium Partner</div>
                                        <div class="cb-meta">
                                            <div>
                                                <span>Agent ID</span>
                                                <strong>APS-<?php echo date('Y') ?>-X9</strong>
                                            </div>
                                            <div>
                                                <span>Status</span>
                                                <strong class="text-success">ACTIVE</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="cb-footer">
                                    Scan to verify authenticity
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Referral Code Section -->
    <div class="container" id="referral-code">
        <div class="referral-code-box">
            <h3 class="text-center mb-4"><i class="fas fa-ticket-alt me-2 text-primary"></i><?php echo $isLoggedIn ? __('assoc_your_referral') : __('assoc_company_referral'); ?></h3>
            <p class="text-center text-muted mb-4">
                <?php if ($isLoggedIn): ?>
                    <?= __('assoc_share_code') ?>
                <?php else: ?>
                    <?= __('assoc_use_code') ?>
                <?php endif; ?>
            </p>
            <div class="referral-code-display"><?php echo htmlspecialchars($isLoggedIn && $loggedInReferralCode ? $loggedInReferralCode : $referral_code); ?></div>
            <button class="copy-btn" onclick="copyReferralCode()">
                <i class="fas fa-copy me-2"></i><?= __('assoc_copy_code') ?>
            </button>
            <div class="text-center mt-4">
                <div class="qr-code">
                    <div class="style-32164">
                        <i class="fas fa-qrcode fa-4x text-muted"></i>
                    </div>
                    <small class="text-muted d-block mt-2"><?= __('assoc_scan_join') ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <div class="benefits">
        <div class="container">
            <h2 class="text-center mb-5"><i class="fas fa-star me-2 text-primary"></i><?= __('assoc_why_title') ?></h2>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <h4><?= __('assoc_benefit_commission') ?></h4>
                        <p><?= __('assoc_benefit_commission_desc') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h4><?= __('assoc_benefit_network') ?></h4>
                        <p><?= __('assoc_benefit_network_desc') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h4><?= __('assoc_benefit_wallet') ?></h4>
                        <p><?= __('assoc_benefit_wallet_desc') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4><?= __('assoc_benefit_support') ?></h4>
                        <p><?= __('assoc_benefit_support_desc') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4><?= __('assoc_benefit_growth') ?></h4>
                        <p><?= __('assoc_benefit_growth_desc') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h4><?= __('assoc_benefit_referral') ?></h4>
                        <p><?= __('assoc_benefit_referral_desc') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings Section -->
    <div class="earnings-section">
        <div class="container">
            <h2 class="mb-5"><i class="fas fa-money-bill-wave me-2"></i><?= __('assoc_earnings_title') ?></h2>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="earning-card">
                        <div class="earning-amount">₹200</div>
                        <p><?= __('assoc_per_associate') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="earning-card">
                        <div class="earning-amount">₹250</div>
                        <p><?= __('assoc_per_agent') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="earning-card">
                        <div class="earning-amount">₹100</div>
                        <p><?= __('assoc_per_customer') ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <p class="fs-4"><?= sprintf(__('assoc_plus_commission'), '5%') ?></p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
        <div class="container">
            <h2 class="mb-4"><?= __('assoc_cta_title') ?></h2>
            <p class="text-muted mb-5"><?php echo $isLoggedIn ? __('assoc_cta_logged_in') : __('assoc_cta_not_logged'); ?></p>
            <a href="<?php echo BASE_URL; ?>/associate/register?ref=<?php echo urlencode($referral_code); ?>" class="btn-join">
                <i class="fas fa-user-plus me-2"></i><?= __('assoc_join_associate') ?>
            </a>
            <div class="mt-4">
                <a href="<?php echo BASE_URL; ?>/agent/register?ref=<?php echo urlencode($referral_code); ?>" class="btn btn-outline-primary btn-lg px-4 py-3 rounded-pill">
                    <i class="fas fa-briefcase me-2"></i><?= __('assoc_join_agent') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-dark text-white py-4 text-center">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> APS Dream Home. <?= __('footer_all_rights') ?></p>
        </div>
    </div>

    <script>
        function copyReferralCode() {
            const displayEl = document.querySelector('.referral-code-display');
            const referralCode = displayEl ? displayEl.textContent.trim() : '<?php echo addslashes($referral_code); ?>';
            navigator.clipboard.writeText(referralCode).then(() => {
                alert('<?= __('assoc_code_copied') ?>');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }

        // Agent Journey 3D Animation Logic
        let journeyStarted = false;
        function startAgentJourney() {
            if (journeyStarted) return;
            journeyStarted = true;

            const card = document.getElementById('agent-id-card');
            const btn = document.getElementById('btn-start-journey');
            const container = document.getElementById('id-card-container');
            
            // 1. Start the scanline effect on the front
            container.classList.add('is-scanning');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Verification...';
            btn.classList.remove('btn-journey');
            btn.classList.add('btn-secondary');
            
            // 2. Wait for scanner to sweep, then trigger flip
            setTimeout(() => {
                card.classList.add('is-flipped');
                
                // 3. Update button state after transformation
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-arrow-down me-2"></i>Get Your Referral Code';
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-success');
                    btn.onclick = () => {
                        window.location.href = '#referral-code';
                    };
                    
                    // Add a success pulse to the card container
                    container.style.transform = 'scale(1.05)';
                    setTimeout(() => container.style.transform = 'scale(1)', 200);
                }, 800);
                
            }, 1200);
        }
    </script>
</body>
</html>
