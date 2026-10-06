<?php
/**
 * Agent Wallet Activation Packages View
 * @var array $packages
 * @var array|null $userPurchase
 * @var string $walletType
 * @var bool $isActivated
 * @var string $csrf_token
 * @var string $base
 */
$base = BASE_URL;
$page_title = $page_title ?? 'Wallet Activation Packages';
?>
<?php include __DIR__ . '/../layouts/agent.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><i class="fas fa-wallet me-2 text-warning"></i>Wallet Activation Packages</h4>
            <p class="text-muted mb-0">Unlock powerful wallet features for your agent business</p>
        </div>
        <a href="<?= $base ?>/agent/wallet" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Wallet</a>
    </div>

    <?php if ($userPurchase): ?>
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle me-2 fs-4"></i>
            <div>
                <strong>Current Package:</strong> <?= htmlspecialchars($userPurchase['package_name']) ?>
                <span class="ms-2 text-muted">(Activated <?= date('d M Y', strtotime($userPurchase['activated_at'])) ?>)</span>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($packages as $pkg): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 package-card <?= $pkg['slug'] ?> <?= ($pkg['slug'] === 'pro') ? 'popular' : '' ?>">
                    <div class="card-body d-flex flex-column">
                        <div class="text-center mb-3">
                            <div class="package-icon mx-auto">
                                <i class="fas fa-<?= $pkg['slug'] === 'basic' ? 'wallet' : ($pkg['slug'] === 'pro' ? 'gem' : 'crown') ?>"></i>
                            </div>
                            <h5 class="package-name mt-2 mb-1"><?= htmlspecialchars($pkg['name']) ?></h5>
                            <div class="package-price">₹<?= number_format($pkg['price'], 2) ?><small class="text-muted fw-normal">/year</small></div>
                        </div>
                        
                        <p class="package-desc small text-muted text-center flex-grow-1"><?= htmlspecialchars($pkg['description']) ?></p>
                        
                        <ul class="features-list list-unstyled mb-3">
                            <?php 
                            $allFeatures = [
                                'emi_payment' => 'Pay EMI from Wallet',
                                'referral_earnings_view' => 'View Referral Earnings',
                                'withdrawal_request' => 'Request Withdrawal to Bank',
                                'booking_adjustment' => 'Apply Wallet to New Booking',
                                'priority_support' => 'Priority Support',
                                'auto_emi_deduction' => 'Auto EMI Deduction',
                                'detailed_analytics' => 'Detailed Earnings Analytics',
                                'dedicated_manager' => 'Dedicated Manager',
                                'vip_offers' => 'Exclusive VIP Offers',
                                'zero_withdrawal_fee' => 'Zero Withdrawal Fees',
                            ];
                            foreach ($allFeatures as $key => $label): 
                                $has = ($pkg['features'][$key] ?? false);
                            ?>
                                <li class="<?= $has ? '' : 'text-muted' ?> d-flex align-items-center gap-2 py-1 small">
                                    <i class="fas fa-<?= $has ? 'check text-success' : 'times text-muted' ?>"></i>
                                    <?= htmlspecialchars($label) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php
                        $l1pct = rtrim(rtrim(number_format((float)($pkg['referral_pct_l1'] ?? 0), 2), '0'), '.');
                        $l2pct = rtrim(rtrim(number_format((float)($pkg['referral_pct_l2'] ?? 0), 2), '0'), '.');
                        ?>
                        <?php if (($pkg['referral_reward'] ?? 0) > 0): ?>
                            <div class="alert alert-warning py-2 mb-3 small">
                                <i class="fas fa-gift me-1"></i>Refer a friend & earn <strong>₹<?= number_format((float)($pkg['referral_reward'] ?? 0), 2) ?> (<?= $l1pct ?>%)</strong><?php if ((float)($pkg['referral_pct_l2'] ?? 0) > 0): ?> + <strong><?= $l2pct ?>%</strong> L2 upline<?php endif; ?> when they activate
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        $isOwned = $userPurchase && $userPurchase['package_id'] == $pkg['id'];
                        $isHigher = $userPurchase && in_array($userPurchase['slug'], ['pro', 'premium']) && $pkg['slug'] === 'basic';
                        ?>
                        <button class="btn btn-warning w-100 mt-auto" 
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
                    <?php if ($pkg['slug'] === 'pro'): ?>
                        <div class="card-footer bg-warning bg-opacity-10 text-center small fw-bold">MOST POPULAR</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.package-card { transition: all 0.3s ease; border: 1px solid rgba(0,0,0,.05); }
.package-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,.1) !important; }
.package-card.popular { border-color: #f59e0b; }
.package-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; color: #fff; }
.package-card.basic .package-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.package-card.pro .package-icon { background: linear-gradient(135deg, #8b5cf6, #5b21b6); }
.package-card.premium .package-icon { background: linear-gradient(135deg, #f59e0b, #b45309); }
.package-name { font-weight: 700; color: #1e293b; }
.package-price { font-weight: 800; color: #f59e0b; font-size: 1.5rem; }
.package-desc { font-size: .8rem; line-height: 1.4; }
.features-list li { font-size: .75rem; }
.btn-warning { border-radius: 8px; font-weight: 600; padding: .75rem; }
</style>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/agent.php'; ?>