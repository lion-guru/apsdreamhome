<?php
/**
 * Wallet Activation Packages View
 * @var array $packages
 * @var array|null $userPurchase
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
    <title>Wallet Activation Packages - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#0d9488 100%);display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
        .container{width:100%;max-width:900px}
        .page-header{text-align:center;color:#fff;margin-bottom:2rem}
        .page-header h1{font-size:2rem;font-weight:800;margin-bottom:.5rem}
        .page-header p{font-size:1rem;color:rgba(255,255,255,.7)}
        
        .packages-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem}
        
        .package-card{background:rgba(255,255,255,.95);border-radius:20px;padding:2rem;box-shadow:0 25px 60px rgba(0,0,0,.15);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden;transition:all .3s ease}
        .package-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#0d9488,#14b8a6,#5eead4,#14b8a6,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite}
        .package-card:hover{transform:translateY(-5px);box-shadow:0 35px 70px rgba(0,0,0,.2)}
        .package-card.popular::after{content:'MOST POPULAR';position:absolute;top:1rem;right:1rem;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:.25rem .75rem;border-radius:20px;font-size:.6rem;font-weight:700;letter-spacing:.5px}
        
        .package-icon{width:70px;height:70px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.8rem;color:#fff}
        .package-card.basic .package-icon{background:linear-gradient(135deg,#3b82f6,#1d4ed8)}
        .package-card.pro .package-icon{background:linear-gradient(135deg,#8b5cf6,#5b21b6)}
        .package-card.premium .package-icon{background:linear-gradient(135deg,#f59e0b,#b45309)}
        
        .package-name{font-size:1.25rem;font-weight:800;color:#1e293b;text-align:center;margin-bottom:.25rem}
        .package-price{font-size:2rem;font-weight:800;color:#0d9488;text-align:center;margin-bottom:.25rem}
        .package-price sub{font-size:.75rem;font-weight:500;color:#64748b;bottom:0}
        .package-desc{font-size:.85rem;color:#64748b;text-align:center;margin-bottom:1.5rem;line-height:1.5}
        
        .features-list{list-style:none;padding:0;margin:0 0 1.5rem}
        .features-list li{display:flex;align-items:center;gap:.75rem;padding:.5rem 0;font-size:.85rem;color:#334155}
        .features-list li i{color:#0d9488;font-size:.75rem}
        .features-list li.unavailable{color:#94a3b8}
        .features-list li.unavailable i{color:#94a3b8}
        
        .btn-purchase{width:100%;padding:1rem;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:.5rem}
        .package-card.basic .btn-purchase{background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff}
        .package-card.basic .btn-purchase:hover{box-shadow:0 10px 30px rgba(59,130,246,.4);transform:translateY(-2px)}
        .package-card.pro .btn-purchase{background:linear-gradient(135deg,#8b5cf6,#5b21b6);color:#fff}
        .package-card.pro .btn-purchase:hover{box-shadow:0 10px 30px rgba(139,92,246,.4);transform:translateY(-2px)}
        .package-card.premium .btn-purchase{background:linear-gradient(135deg,#f59e0b,#b45309);color:#fff}
        .package-card.premium .btn-purchase:hover{box-shadow:0 10px 30px rgba(245,158,11,.4);transform:translateY(-2px)}
        .btn-purchase:disabled{opacity:.6;cursor:not-allowed;transform:none!important;box-shadow:none!important}
        
        .referral-badge{background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #fcd34d;border-radius:10px;padding:.75rem;margin-top:1rem;text-align:center}
        .referral-badge span{font-size:.75rem;color:#92400e;font-weight:600}
        .referral-badge strong{color:#78350f;font-size:.9rem}
        
        .current-badge{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#d1fae5,#a7f3d0);border:1px solid #6ee7b7;border-radius:10px;padding:.75rem 1rem;margin-bottom:2rem;color:#065f46;font-weight:600;font-size:.9rem}
        .current-badge i{color:#059669}
        
        @media(max-width:576px){
            .page-header h1{font-size:1.5rem}
            .packages-grid{grid-template-columns:1fr}
        }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-wallet me-2"></i>Activate Your Wallet</h1>
            <p>Unlock powerful wallet features to manage your earnings, pay EMI, and grow faster</p>
        </div>

        <?php if ($userPurchase): ?>
            <div class="current-badge">
                <i class="fas fa-check-circle"></i>
                <strong>Current:</strong> <?= htmlspecialchars($userPurchase['package_name']) ?> 
                <span style="font-weight:400;color:#059669">(Activated <?= date('d M Y', strtotime($userPurchase['activated_at'])) ?>)</span>
            </div>
        <?php endif; ?>

        <div class="packages-grid">
            <?php foreach ($packages as $pkg): ?>
                <div class="package-card <?= $pkg['slug'] ?> <?= ($pkg['slug'] === 'pro') ? 'popular' : '' ?>">
                    <div class="package-icon">
                        <i class="fas fa-<?= $pkg['slug'] === 'basic' ? 'wallet' : ($pkg['slug'] === 'pro' ? 'gem' : 'crown') ?>"></i>
                    </div>
                    <h3 class="package-name"><?= htmlspecialchars($pkg['name']) ?></h3>
                    <div class="package-price">₹<?= number_format($pkg['price'], 2) ?><sub>/year</sub></div>
                    <p class="package-desc"><?= htmlspecialchars($pkg['description']) ?></p>
                    
                    <ul class="features-list">
                        <?php 
                        $allFeatures = [
                            'emi_payment' => 'Pay EMI from Wallet',
                            'referral_earnings_view' => 'View Referral Earnings',
                            'withdrawal_request' => 'Request Withdrawal to Bank',
                            'booking_adjustment' => 'Apply Wallet to New Booking',
                            'priority_support' => 'Priority Customer Support',
                            'auto_emi_deduction' => 'Auto EMI Deduction',
                            'detailed_analytics' => 'Detailed Earnings Analytics',
                            'dedicated_manager' => 'Dedicated Relationship Manager',
                            'vip_offers' => 'Exclusive VIP Offers',
                            'zero_withdrawal_fee' => 'Zero Withdrawal Fees',
                        ];
                        foreach ($allFeatures as $key => $label): 
                            $has = ($pkg['features'][$key] ?? false);
                        ?>
                            <li class="<?= $has ? '' : 'unavailable' ?>">
                                <i class="fas fa-<?= $has ? 'check-circle' : 'times-circle' ?>"></i>
                                <?= htmlspecialchars($label) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php
                    $l1pct = rtrim(rtrim(number_format((float)($pkg['referral_pct_l1'] ?? 0), 2), '0'), '.');
                    $l2pct = rtrim(rtrim(number_format((float)($pkg['referral_pct_l2'] ?? 0), 2), '0'), '.');
                    ?>
                    <?php if (($pkg['referral_reward'] ?? 0) > 0): ?>
                        <div class="referral-badge">
                            <span><i class="fas fa-gift me-1"></i>Refer a friend & earn <strong>₹<?= number_format((float)($pkg['referral_reward'] ?? 0), 2) ?> (<?= $l1pct ?>%)</strong><?php if ((float)($pkg['referral_pct_l2'] ?? 0) > 0): ?> + <strong><?= $l2pct ?>%</strong> L2 upline<?php endif; ?> when they activate</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    $isOwned = $userPurchase && $userPurchase['package_id'] == $pkg['id'];
                    $isHigher = $userPurchase && in_array($userPurchase['slug'], ['pro', 'premium']) && $pkg['slug'] === 'basic';
                    ?>
                    <button class="btn-purchase" 
                            onclick="purchasePackage(<?= $pkg['id'] ?>)" 
                            <?= $isOwned || $isHigher ? 'disabled' : '' ?>>
                        <?php if ($isOwned): ?>
                            <i class="fas fa-check me-2"></i>Already Active
                        <?php elseif ($isHigher): ?>
                            <i class="fas fa-arrow-up me-2"></i>Upgrade Available
                        <?php else: ?>
                            <i class="fas fa-lock-open me-2"></i>Activate for ₹<?= number_format($pkg['price']) ?>
                        <?php endif; ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= $base ?>/auth/wallet" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Back to My Wallet
            </a>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        async function purchasePackage(packageId) {
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

            try {
                const response = await fetch('<?= $base ?>/auth/wallet/purchase', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= $csrf_token ?>'
                    },
                    body: JSON.stringify({ package_id: packageId, payment_mode: 'razorpay' })
                });

                const data = await response.json();
                
                if (data.success && data.payment_ref) {
                    // Open Razorpay checkout
                    const options = {
                        key: '<?= getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_key' ?>',
                        amount: data.amount * 100,
                        currency: 'INR',
                        name: 'APS Dream Home',
                        description: 'Wallet Activation: ' + data.package.name,
                        order_id: data.payment_ref,
                        handler: function (response) {
                            verifyPayment(data.purchase_id, response.razorpay_payment_id, response.razorpay_order_id, response.razorpay_signature);
                        },
                        prefill: {
                            name: '<?= $_SESSION['user_name'] ?? '' ?>',
                            email: '<?= $_SESSION['user_email'] ?? '' ?>',
                            contact: '<?= $_SESSION['user_phone'] ?? '' ?>'
                        },
                        theme: { color: '#0d9488' }
                    };
                    const rzp = new Razorpay(options);
                    rzp.on('payment.failed', function (response) {
                        alert('Payment failed: ' + response.error.description);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
                    rzp.open();
                } else {
                    throw new Error(data.message || 'Failed to initiate payment');
                }
            } catch (error) {
                alert('Error: ' + error.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        async function verifyPayment(purchaseId, paymentId, orderId, signature) {
            try {
                const response = await fetch('<?= $base ?>/auth/wallet/verify-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= $csrf_token ?>'
                    },
                    body: JSON.stringify({
                        purchase_id: purchaseId,
                        razorpay_payment_id: paymentId,
                        razorpay_order_id: orderId,
                        razorpay_signature: signature
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('✅ ' + data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('❌ ' + (data.message || 'Activation failed'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    </script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</body>
</html>