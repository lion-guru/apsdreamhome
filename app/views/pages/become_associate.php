<?php
$page_title = $page_title ?? __('assoc_page_title');
$page_description = $page_description ?? __('assoc_page_desc', [], 'Become an Associate - APS Dream Home');
$current_page = 'become_associate';
?>
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

    <!-- Referral Code Section -->
    <div class="container" id="referral-code">
        <div class="referral-code-box">
            <h3 class="text-center mb-4"><i class="fas fa-ticket-alt me-2 text-primary"></i><?= $isLoggedIn ? __('assoc_your_referral') : __('assoc_company_referral'); ?></h3>
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
                    <div >
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
            <a href="<?= BASE_URL ?>/associate/register?ref=<?php echo urlencode($referral_code); ?>" class="btn-join">
                <i class="fas fa-user-plus me-2"></i><?= __('assoc_join_associate') ?>
            </a>
            <div class="mt-4">
                <a href="<?= BASE_URL; ?>/agent/register?ref=<?php echo urlencode($referral_code); ?>" class="btn btn-outline-primary btn-lg px-4 py-3 rounded-pill">
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

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
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