<?php $this->layout = 'layouts/base'; ?>
<?php $this->title = 'Referral Network - APS Dream Home'; ?>

<style>
.referral-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
}

.stat-item {
    text-align: center;
}

.stat-item .value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-item .label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.referral-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s ease;
}

.referral-card:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.referral-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 20px;
}

.referral-details {
    flex: 1;
}

.referral-name {
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.referral-meta {
    font-size: 0.85rem;
    color: #888;
    margin-bottom: 3px;
}

.referral-reward {
    font-weight: 700;
    color: #11998e;
    font-size: 1.2rem;
}

.share-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-radius: 15px;
    padding: 30px;
    color: white;
    margin-bottom: 30px;
}

.referral-code-display {
    background: rgba(255, 255, 255, 0.2);
    padding: 20px 30px;
    border-radius: 10px;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-align: center;
    margin: 20px 0;
}

.copy-btn {
    background: white;
    color: #f5576c;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.copy-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.share-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid white;
    padding: 12px 25px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 5px;
}

.share-btn:hover {
    background: white;
    color: #f5576c;
}

.level-badge {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.level-badge.customer {
    background: #d4edda;
    color: #155724;
}

.level-badge.associate {
    background: #cce5ff;
    color: #004085;
}

.level-badge.agent {
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 768px) {
    .referral-card {
        flex-direction: column;
        text-align: center;
    }
    
    .referral-icon {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .referral-reward {
        margin-top: 15px;
    }
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2 text-primary"></i>Referral Network</h2>
        <a href="/wallet" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Wallet</a>
    </div>

    <!-- Share Card -->
    <div class="share-card">
        <h4 class="mb-3"><i class="fas fa-share-alt me-2"></i>Share Your Referral Code</h4>
        <p class="mb-0 opacity-75">Share this code with friends and earn points when they register!</p>
        <div class="referral-code-display">
            <?php echo htmlspecialchars($user_referral_code); ?>
        </div>
        <div class="text-center">
            <button class="copy-btn" onclick="copyReferralCode()">
                <i class="fas fa-copy me-2"></i>Copy Code
            </button>
            <button class="share-btn" onclick="shareOnWhatsApp()">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </button>
            <button class="share-btn" onclick="shareOnFacebook()">
                <i class="fab fa-facebook me-2"></i>Facebook
            </button>
            <button class="share-btn" onclick="shareOnTwitter()">
                <i class="fab fa-twitter me-2"></i>Twitter
            </button>
        </div>
    </div>

    <!-- Referral Stats -->
    <div class="referral-stats">
        <div class="row">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <div class="value"><?php echo $referralEarnings['total_referrals'] ?? 0; ?></div>
                    <div class="label">Total Referrals</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <div class="value">₹<?php echo number_format($referralEarnings['total_earnings'] ?? 0, 2); ?></div>
                    <div class="label">Total Earnings</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <div class="value"><?php echo $referralEarnings['today_referrals'] ?? 0; ?></div>
                    <div class="label">Today's Referrals</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <div class="value">₹<?php echo number_format(($referralEarnings['total_earnings'] ?? 0) * 0.10, 2); ?></div>
                    <div class="label">Est. Monthly</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Rewards Info -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="fas fa-gift me-2 text-primary"></i>Referral Rewards</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <div class="fs-4 fw-bold text-success">₹100</div>
                        <small class="text-muted">Per Customer Referral</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <div class="fs-4 fw-bold text-primary">₹200</div>
                        <small class="text-muted">Per Associate Referral</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <div class="fs-4 fw-bold text-warning">₹250</div>
                        <small class="text-muted">Per Agent Referral</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Direct Referrals List -->
    <h4 class="mb-4"><i class="fas fa-user-friends me-2"></i>Your Referrals</h4>
    
    <?php if (!empty($directReferrals)): ?>
        <?php foreach ($directReferrals as $referral): ?>
            <div class="referral-card">
                <div class="d-flex align-items-center">
                    <div class="referral-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="referral-details">
                        <div class="referral-name">
                            <?php echo htmlspecialchars($referral['name']); ?>
                            <?php if ($referral['reward_amount']): ?>
                                <span class="level-badge <?php echo strtolower($referral['user_type'] ?? 'customer'); ?>">
                                    <?php echo ucfirst($referral['user_type'] ?? 'Customer'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="referral-meta">
                            <i class="fas fa-envelope me-1"></i>
                            <?php echo htmlspecialchars($referral['email']); ?>
                        </div>
                        <div class="referral-meta">
                            <i class="fas fa-phone me-1"></i>
                            <?php echo htmlspecialchars($referral['phone']); ?>
                        </div>
                        <div class="referral-meta">
                            <i class="fas fa-calendar me-1"></i>
                            Referred on: <?php echo date('M d, Y', strtotime($referral['referral_date'] ?? $referral['created_at'])); ?>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <?php if ($referral['reward_amount']): ?>
                        <div class="referral-reward">+₹<?php echo number_format($referral['reward_amount'], 2); ?></div>
                        <small class="text-success">Credited</small>
                    <?php else: ?>
                        <div class="text-muted">Pending</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4>No Referrals Yet</h4>
                <p class="text-muted mb-4">Start sharing your referral code to earn rewards!</p>
                <button class="btn btn-primary" onclick="copyReferralCode()">
                    <i class="fas fa-copy me-2"></i>Copy Referral Code
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function copyReferralCode() {
    const referralCode = '<?php echo $user_referral_code; ?>';
    navigator.clipboard.writeText(referralCode).then(() => {
        alert('Referral code copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}

function shareOnWhatsApp() {
    const referralCode = '<?php echo $user_referral_code; ?>';
    const text = `Join APS Dream Home and use my referral code: ${referralCode} to get 5% discount on your first booking!`;
    const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}

function shareOnFacebook() {
    const referralCode = '<?php echo $user_referral_code; ?>';
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
}

function shareOnTwitter() {
    const referralCode = '<?php echo $user_referral_code; ?>';
    const text = `Join APS Dream Home with my referral code: ${referralCode} - Get 5% discount!`;
    const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}
</script>
