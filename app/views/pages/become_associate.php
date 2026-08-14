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
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><i class="fas fa-users me-3"></i><?= __('assoc_hero_title') ?></h1>
            <p><?= __('assoc_hero_desc') ?></p>
            <a href="#referral-code" class="btn btn-light btn-lg px-5 py-3 fw-bold rounded-pill">
                <i class="fas fa-arrow-down me-2"></i><?= __('assoc_get_code') ?>
            </a>
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
                        <div class="earning-amount">â‚¹200</div>
                        <p><?= __('assoc_per_associate') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="earning-card">
                        <div class="earning-amount">â‚¹250</div>
                        <p><?= __('assoc_per_agent') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="earning-card">
                        <div class="earning-amount">â‚¹100</div>
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
    </script>
</body>
</html>
