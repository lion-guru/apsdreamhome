<?php
/**
 * Agent My Wallet View
 * @var bool $isActivated
 * @var array|null $userPurchase
 * @var string $walletType
 * @var array $packages
 * @var float $walletBalance
 * @var string $csrf_token
 * @var string $base
 */
$base = BASE_URL;
$page_title = $page_title ?? 'My Wallet';
?>
<?php include __DIR__ . '/../layouts/agent.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fas fa-wallet me-2 text-warning"></i>My Wallet</h4>
    </div>

    <div class="row g-4">
        <!-- Wallet Status Card -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if ($isActivated && $userPurchase): ?>
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                                 style="width:80px;height:80px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:2rem">
                                <i class="fas fa-check"></i>
                            </div>
                            <h5 class="mb-1">Wallet Activated</h5>
                            <p class="text-muted mb-0">Active with <strong><?= htmlspecialchars($userPurchase['package_name']) ?></strong> plan</p>
                            <small class="text-muted">Activated <?= date('d M Y', strtotime($userPurchase['activated_at'])) ?></small>
                        <?php else: ?>
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                                 style="width:80px;height:80px;background:linear-gradient(135deg,#64748b,#475569);color:#fff;font-size:2rem">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h5 class="mb-1">Wallet Not Activated</h5>
                            <p class="text-muted mb-0">Activate to unlock powerful features</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($isActivated && $userPurchase): ?>
                        <div class="alert alert-light border mb-3">
                            <h6 class="mb-2"><i class="fas fa-box me-2"></i><?= htmlspecialchars($userPurchase['package_name']) ?></h6>
                            <div class="row g-2 small">
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
                                <div class="col-6">
                                    <i class="fas fa-check text-warning me-1"></i><?= $featureLabels[$key] ?>
                                </div>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Wallet Balance Card -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-gradient-warning text-dark" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%)!important">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <p class="mb-1 opacity-75 small">Available Balance</p>
                    <h2 class="fw-bold mb-0" id="walletBalance">₹<?= number_format($walletBalance, 2) ?></h2>
                    <small class="opacity-75 mt-2">Wallet Type: <strong><?= ucfirst(str_replace('_', ' ', $walletType)) ?></strong></small>
                </div>
            </div>
        </div>

        <!-- Actions Card -->
        <div class="col-12 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h6 class="mb-3">Quick Actions</h6>
                    <?php if ($isActivated): ?>
                        <a href="<?= $base ?>/agent/referrals" class="btn btn-outline-warning w-100 mb-2">
                            <i class="fas fa-share-alt me-2"></i>Refer & Earn
                        </a>
                        <a href="<?= $base ?>/agent/wallet/transactions" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-history me-2"></i>Transactions
                        </a>
                        <a href="<?= $base ?>/agent/emi-tracker" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-credit-card me-2"></i>EMI Tracker
                        </a>
                    <?php else: ?>
                        <a href="<?= $base ?>/agent/wallet/packages" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-rocket me-2"></i>Activate Wallet
                        </a>
                    <?php endif; ?>
                    <a href="<?= $base ?>/agent/wallet/packages" class="btn btn-outline-dark w-100 mt-auto">
                        <i class="fas fa-boxes me-2"></i>View Packages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Features -->
    <div class="row g-4 mt-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-list me-2"></i>Available Wallet Features</h5>
            <div class="row g-3">
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
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 feature-card <?= $has ? '' : 'locked' ?>" style="border:1px solid rgba(0,0,0,.05);transition:all .2s">
                        <div class="card-body text-center p-3">
                            <div class="feature-icon mx-auto mb-2" style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;<?= $has ? 'background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff' : 'background:#e2e8f0;color:#94a3b8' ?>">
                                <i class="fas fa-<?= $feat['icon'] ?>"></i>
                            </div>
                            <h6 class="feature-name mb-1 small"><?= $feat['name'] ?></h6>
                            <p class="feature-desc text-muted small mb-0"><?= $feat['desc'] ?></p>
                            <?php if (!$has): ?>
                                <span class="badge bg-warning mt-2 small">Locked</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.feature-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.08) !important; }
.feature-card.locked { opacity: 0.7; }
</style>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/agent.php'; ?>