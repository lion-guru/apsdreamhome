<?php
/**
 * My Wallet View - Wallet Activation Status
 * @var bool $isActivated
 * @var array|null $userPurchase
 * @var array $packages
 * @var string $csrf_token
 * @var string $base
 */
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#0d9488 100%);padding:2rem 1rem}
        .container{width:100%;max-width:600px;margin:0 auto}
        .page-header{text-align:center;color:#fff;margin-bottom:2rem}
        .page-header h1{font-size:2rem;font-weight:800;margin-bottom:.5rem}
        .page-header p{font-size:1rem;color:rgba(255,255,255,.7)}
        
        .wallet-card{background:rgba(255,255,255,.95);border-radius:20px;padding:2rem;box-shadow:0 25px 60px rgba(0,0,0,.15);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3)}
        .wallet-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#0d9488,#14b8a6,#5eead4,#14b8a6,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite}
        
        .wallet-status{text-align:center;padding:2rem 0}
        .status-icon{width:100px;height:100px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2.5rem;color:#fff}
        .status-icon.active{background:linear-gradient(135deg,#059669,#10b981)}
        .status-icon.inactive{background:linear-gradient(135deg,#64748b,#475569)}
        
        .status-title{font-size:1.5rem;font-weight:800;color:#1e293b;margin-bottom:.5rem}
        .status-desc{font-size:1rem;color:#64748b;margin-bottom:2rem}
        
        .package-info{background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border:1px solid #99f6e4;border-radius:16px;padding:1.5rem;margin-bottom:2rem}
        .package-info h3{font-size:1.1rem;font-weight:700;color:#065f46;margin-bottom:.5rem}
        .package-info .features{display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;margin-top:1rem}
        .package-info .feature{display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:#047857}
        .package-info .feature i{color:#059669}
        
        .wallet-balance{background:linear-gradient(135deg,#0d9488,#0f766e);border-radius:16px;padding:1.5rem;color:#fff;text-align:center;margin-bottom:2rem}
        .wallet-balance .label{font-size:.85rem;opacity:.8;margin-bottom:.25rem}
        .wallet-balance .amount{font-size:2.5rem;font-weight:800}
        
        .btn-action{width:100%;padding:1rem;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:.5rem}
        .btn-primary{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(13,148,136,.4)}
        .btn-outline{background:transparent;border:2px solid #0d9488;color:#0d9488}
        .btn-outline:hover{background:#0d9488;color:#fff}
        
        .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-top:2rem}
        .feature-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1rem;text-align:center;transition:all .2s}
        .feature-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.08)}
        .feature-card.locked{opacity:.5}
        .feature-card.locked .feature-icon{background:#e2e8f0;color:#94a3b8}
        .feature-icon{width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:1.25rem}
        .feature-name{font-size:.85rem;font-weight:600;color:#1e293b;margin-bottom:.25rem}
        .feature-desc{font-size:.7rem;color:#64748b}
        
        .back-link{display:inline-flex;align-items:center;gap:.5rem;color:rgba(255,255,255,.6);text-decoration:none;margin-top:2rem;font-size:.9rem;transition:color .2s}
        .back-link:hover{color:#fff}
        
        @media(max-width:576px){
            .page-header h1{font-size:1.5rem}
            .wallet-balance .amount{font-size:2rem}
            .package-info .features{grid-template-columns:1fr}
        }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-wallet me-2"></i>My Wallet</h1>
            <p>Manage your wallet activation and earnings</p>
        </div>

        <div class="wallet-card" style="position:relative">
            <div class="wallet-status">
                <?php if ($isActivated && $userPurchase): ?>
                    <div class="status-icon active"><i class="fas fa-check"></i></div>
                    <h2 class="status-title">Wallet Activated</h2>
                    <p class="status-desc">Your wallet is active with <strong><?= htmlspecialchars($userPurchase['package_name']) ?></strong> plan</p>
                    
                    <div class="package-info">
                        <h3><i class="fas fa-box me-2"></i><?= htmlspecialchars($userPurchase['package_name']) ?></h3>
                        <div class="features">
                            <?php 
                            $featureLabels = [
                                'emi_payment' => 'EMI Payment',
                                'referral_earnings_view' => 'Referral Earnings',
                                'withdrawal_request' => 'Bank Withdrawal',
                                'booking_adjustment' => 'Booking Adjust',
                                'priority_support' => 'Priority Support',
                                'auto_emi_deduction' => 'Auto EMI',
                                'detailed_analytics' => 'Analytics',
                                'dedicated_manager' => 'Dedicated Manager',
                                'vip_offers' => 'VIP Offers',
                                'zero_withdrawal_fee' => 'Zero Withdrawal Fee',
                            ];
                            foreach ($userPurchase['features'] as $key => $enabled):
                                if ($enabled && isset($featureLabels[$key])):
                            ?>
                                <div class="feature"><i class="fas fa-check"></i><?= $featureLabels[$key] ?></div>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="status-icon inactive"><i class="fas fa-lock"></i></div>
                    <h2 class="status-title">Wallet Not Activated</h2>
                    <p class="status-desc">Activate your wallet to unlock powerful features</p>
                <?php endif; ?>
            </div>

            <?php if ($isActivated): ?>
                <div class="wallet-balance">
                    <div class="label">Wallet Balance</div>
                    <div class="amount" id="walletBalance">₹0.00</div>
                </div>
            <?php endif; ?>

            <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem">
                <?php if ($isActivated): ?>
                    <button class="btn-action btn-primary" onclick="location.href='<?= $base ?>/user/referrals'">
                        <i class="fas fa-share-alt me-2"></i>Refer & Earn
                    </button>
                    <button class="btn-action btn-outline" onclick="location.href='<?= $base ?>/user/wallet-transactions'">
                        <i class="fas fa-history me-2"></i>Transactions
                    </button>
                    <button class="btn-action btn-outline" onclick="location.href='<?= $base ?>/user/emi-pay'">
                        <i class="fas fa-credit-card me-2"></i>Pay EMI
                    </button>
                <?php else: ?>
                    <a href="<?= $base ?>/auth/wallet/packages" class="btn-action btn-primary">
                        <i class="fas fa-rocket me-2"></i>Activate Wallet
                    </a>
                <?php endif; ?>
            </div>

            <h3 style="color:#fff;margin-bottom:1rem;font-size:1.1rem">Available Features</h3>
            <div class="features-grid">
                <?php 
                $allFeatures = [
                    'emi_payment' => ['icon' => 'credit-card', 'name' => 'Pay EMI', 'desc' => 'Pay installments from wallet'],
                    'referral_earnings_view' => ['icon' => 'chart-line', 'name' => 'View Earnings', 'desc' => 'Track referral income'],
                    'withdrawal_request' => ['icon' => 'money-bill-wave', 'name' => 'Withdraw', 'desc' => 'Transfer to bank account'],
                    'booking_adjustment' => ['icon' => 'home', 'name' => 'Booking Adjust', 'desc' => 'Apply balance to bookings'],
                    'priority_support' => ['icon' => 'headset', 'name' => 'Priority Support', 'desc' => 'Faster response times'],
                    'auto_emi_deduction' => ['icon' => 'sync-alt', 'name' => 'Auto EMI', 'desc' => 'Automatic deductions'],
                    'detailed_analytics' => ['icon' => 'chart-bar', 'name' => 'Analytics', 'desc' => 'Detailed reports'],
                    'dedicated_manager' => ['icon' => 'user-tie', 'name' => 'Dedicated Mgr', 'desc' => 'Personal manager'],
                    'vip_offers' => ['icon' => 'gem', 'name' => 'VIP Offers', 'desc' => 'Exclusive deals'],
                    'zero_withdrawal_fee' => ['icon' => 'percent', 'name' => 'Zero Fees', 'desc' => 'Free withdrawals'],
                ];
                foreach ($allFeatures as $key => $feat):
                    $has = $isActivated && ($userPurchase['features'][$key] ?? false);
                ?>
                    <div class="feature-card <?= $has ? '' : 'locked' ?>">
                        <div class="feature-icon"><i class="fas fa-<?= $feat['icon'] ?>"></i></div>
                        <div class="feature-name"><?= $feat['name'] ?></div>
                        <div class="feature-desc"><?= $feat['desc'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <a href="<?= $base ?>/user/dashboard" class="back-link">
            <i class="fas fa-arrow-left"></i>Back to Dashboard
        </a>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        // Fetch wallet balance
        <?php if ($isActivated): ?>
        fetch('<?= $base ?>/api/wallet/balance')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('walletBalance').textContent = '₹' + Number(data.balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
                }
            })
            .catch(() => {});
        <?php endif; ?>
    </script>
</body>
</html>