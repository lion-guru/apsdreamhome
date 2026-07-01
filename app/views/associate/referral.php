<?php
/**
 * Associate Referral Page - Share code and track referrals
 */
$page_title = $page_title ?? 'Refer & Earn';
$current_page = 'referral';
$referral_code = $referral_code ?? '';
$referral_count = $referral_count ?? 0;
$referral_earnings = $referral_earnings ?? 0;
$shareUrl = BASE_URL . '/register?ref=' . urlencode($referral_code);
$shareText = "Join APS Dream Home using my referral code and get exclusive benefits! Use code: $referral_code";
?>

<!-- Referral Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #6366f1 0%, #14b8a6 100%); color: #fff;">
            <div class="card-body p-4 text-center">
                <i class="fas fa-users fa-2x mb-3 opacity-75"></i>
                <div class="fs-2 fw-bold"><?= $referral_count ?></div>
                <div class="opacity-75">Total Referrals</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <i class="fas fa-rupee-sign fa-2x mb-3 text-success"></i>
                <div class="fs-2 fw-bold text-success">₹<?= number_format($referral_earnings) ?></div>
                <div class="text-muted">Referral Earnings</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <i class="fas fa-gift fa-2x mb-3 text-warning"></i>
                <div class="fs-2 fw-bold text-primary">₹500</div>
                <div class="text-muted">Per Successful Referral</div>
            </div>
        </div>
    </div>
</div>

<!-- Referral Code Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-share-alt me-2 text-primary"></i>Share Your Referral Code</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="d-inline-block p-4 rounded-3" style="background: linear-gradient(135deg, #f8fafc, #e2e8f0);">
                <div class="small text-muted mb-2">Your Unique Referral Code</div>
                <code id="referralCodeDisplay" class="fs-3 fw-bold" style="color: #0d9488; letter-spacing: 3px;">
                    <?= htmlspecialchars($referral_code ?: 'N/A') ?>
                </code>
            </div>
        </div>

        <!-- Copy & Share Buttons -->
        <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
            <button class="btn btn-primary px-4" onclick="copyCode()">
                <i class="fas fa-copy me-2"></i>Copy Code
            </button>
            <button class="btn btn-success px-4" onclick="shareWhatsApp()">
                <i class="fab fa-whatsapp me-2"></i>Share WhatsApp
            </button>
            <button class="btn btn-outline-primary px-4" onclick="shareTelegram()">
                <i class="fab fa-telegram me-2"></i>Telegram
            </button>
            <button class="btn btn-outline-info px-4" onclick="shareSMS()">
                <i class="fas fa-sms me-2"></i>SMS
            </button>
        </div>

        <!-- Share Link -->
        <div class="mb-3">
            <label class="form-label fw-bold">Share Link</label>
            <div class="input-group">
                <input type="text" class="form-control" id="shareLink" value="<?= htmlspecialchars($shareUrl) ?>" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">
                    <i class="fas fa-link me-1"></i> Copy
                </button>
            </div>
        </div>

        <!-- More Share Options -->
        <div class="text-center">
            <button class="btn btn-outline-dark btn-sm me-2" onclick="shareFacebook()">
                <i class="fab fa-facebook-f me-1"></i>Facebook
            </button>
            <button class="btn btn-outline-dark btn-sm me-2" onclick="shareLinkedIn()">
                <i class="fab fa-linkedin-in me-1"></i>LinkedIn
            </button>
            <button class="btn btn-outline-dark btn-sm" onclick="shareTwitter()">
                <i class="fab fa-twitter me-1"></i>Twitter
            </button>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>How Referral Works</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #dbeafe;">
                    <i class="fas fa-share text-primary fa-lg"></i>
                </div>
                <h6>1. Share Your Code</h6>
                <p class="small text-muted">Share your unique referral code with friends, family, and contacts.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #dcfce7;">
                    <i class="fas fa-user-plus text-success fa-lg"></i>
                </div>
                <h6>2. They Register</h6>
                <p class="small text-muted">When they register using your code, they become your referral.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #fef3c7;">
                    <i class="fas fa-rupee-sign text-warning fa-lg"></i>
                </div>
                <h6>3. Earn Rewards</h6>
                <p class="small text-muted">Earn ₹500 for every successful referral who completes a purchase.</p>
            </div>
        </div>
    </div>
</div>

<script>
const referralCode = <?= json_encode($referral_code) ?>;
const shareUrl = <?= json_encode($shareUrl) ?>;
const shareText = <?= json_encode($shareText) ?>;

function copyCode() {
    navigator.clipboard.writeText(referralCode).then(() => {
        alert('Referral code copied!');
    });
}

function copyLink() {
    navigator.clipboard.writeText(shareUrl).then(() => {
        alert('Link copied!');
    });
}

function shareWhatsApp() {
    window.open(`https://wa.me/?text=${encodeURIComponent(shareText + '\n' + shareUrl)}`, '_blank');
}

function shareTelegram() {
    window.open(`https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`, '_blank');
}

function shareSMS() {
    window.open(`sms:?body=${encodeURIComponent(shareText + ' ' + shareUrl)}`, '_blank');
}

function shareFacebook() {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`, '_blank');
}

function shareLinkedIn() {
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`, '_blank');
}

function shareTwitter() {
    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`, '_blank');
}
</script>
