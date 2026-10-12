<?php
$page_title = $page_title ?? 'Activate Wallet';
$current_page = $current_page ?? 'wallet-packages';
$packages = $packages ?? [];
?>
<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-bolt me-2"></i>Activate Wallet</h2>
            <p class="text-muted">Choose a wallet activation package to unlock full wallet features</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= BASE_URL ?>/user/wallet" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Wallet
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php if (!empty($packages)): ?>
        <?php foreach ($packages as $pkg): ?>
            <div class="col-md-4">
                <div class="aps-cp-card h-100">
                    <div class="aps-cp-card-body d-flex flex-column">
                        <h5 class="mb-2"><?= htmlspecialchars($pkg['name'] ?? 'Package') ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($pkg['description'] ?? '') ?></p>
                        
                        <div class="mb-3">
                            <span class="display-4 fw-bold text-primary">₹<?= number_format((float)($pkg['amount'] ?? 0), 2) ?></span>
                            <span class="text-muted">/ one-time</span>
                        </div>
                        
                        <ul class="list-unstyled mb-3 flex-grow-1">
                            <?php if (!empty($pkg['wallet_points'])): ?>
                                <li><i class="fas fa-check text-success me-2"></i> <strong><?= (int)$pkg['wallet_points'] ?></strong> Wallet Points</li>
                            <?php endif; ?>
                            <?php if (!empty($pkg['emi_discount_pct'])): ?>
                                <li><i class="fas fa-check text-success me-2"></i> <strong><?= (float)$pkg['emi_discount_pct'] ?>%</strong> EMI Discount</li>
                            <?php endif; ?>
                            <?php if (!empty($pkg['priority_support'])): ?>
                                <li><i class="fas fa-check text-success me-2"></i> Priority Support</li>
                            <?php endif; ?>
                            <?php if (!empty($pkg['features'])): ?>
                                <?php foreach (json_decode($pkg['features'], true) ?? [] as $feat): ?>
                                    <li><i class="fas fa-check text-success me-2"></i> <?= htmlspecialchars($feat) ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        
                        <form method="post" action="<?= BASE_URL ?>/user/wallet/packages/purchase">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="package_id" value="<?= (int)$pkg['id'] ?>">
                            <button type="submit" class="btn btn-primary w-100 mt-auto">
                                <i class="fas fa-bolt me-1"></i>Activate Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5>No Packages Available</h5>
                    <p class="text-muted">Wallet activation packages are not configured yet.</p>
                    <a href="<?= BASE_URL ?>/user/wallet" class="btn btn-secondary mt-3">
                        <i class="fas fa-arrow-left me-1"></i>Back to Wallet
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Wallet Activation Benefits:</strong>
    <ul class="mb-0 mt-2">
        <li>Earn wallet points on every booking and referral</li>
        <li>Use points for EMI payments and discounts</li>
        <li>Priority customer support</li>
        <li>Exclusive offers and cashback</li>
    </ul>
</div>