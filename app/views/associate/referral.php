<?php
/**
 * Associate Referral Page - Share code and track referrals
 */
$page_title = $page_title ?? __('assoc_ref_title', [], 'Refer & Earn');
$current_page = 'referral';
$referral_code = $referral_code ?? '';
$referral_count = $referral_count ?? 0;
$referral_earnings = $referral_earnings ?? 0;
$share_clicks = $share_clicks ?? [];
$referred_users = $referred_users ?? [];
$totalShares = array_sum($share_clicks);
$shareUrl = BASE_URL . '/register?ref=' . urlencode($referral_code);
$shareText = __('assoc_ref_share_text', ['code' => $referral_code], "Join APS Dream Home using my referral code and get exclusive benefits! Use code: $referral_code");
$tierInfo = $tier_info ?? ['tier' => 'bronze', 'label' => 'Bronze', 'color' => '#CD7F32', 'icon' => 'fas fa-medal', 'total_referrals' => 0, 'next_tier' => 'Silver', 'next_tier_min' => 5, 'progress' => 0, 'referrals_needed' => 5, 'perks' => [], 'bonus_per_referral' => 100, 'bonus_on_booking' => 500];
?>

<!-- Tier Card -->
<div class="card border-0 shadow-sm mb-4" class="style-61637">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="style-63527">
                    <i class="<?= $tierInfo['icon'] ?>" class="style-1525"></i>
                </div>
                <div>
                    <h5 class="mb-0" class="style-72606"><?= $tierInfo['label'] ?> Tier</h5>
                    <small class="text-muted">₹<?= number_format($tierInfo['bonus_per_referral']) ?> per signup Â· ₹<?= number_format($tierInfo['bonus_on_booking']) ?> on booking</small>
                </div>
            </div>
            <?php if ($tierInfo['next_tier']): ?>
            <div class="text-end">
                <small class="text-muted d-block"><?= $tierInfo['referrals_needed'] ?> more to <?= $tierInfo['next_tier'] ?></small>
                <div class="progress mt-1" class="style-58327">
                    <div class="progress-bar" class="style-25252"></div>
                </div>
            </div>
            <?php else: ?>
            <div class="badge" class="style-1828">
                <i class="fas fa-crown me-1"></i>Max Tier
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($tierInfo['perks'])): ?>
        <div class="mt-3 d-flex flex-wrap gap-3">
            <?php foreach ($tierInfo['perks'] as $perk): ?>
            <span class="small"><i class="fas fa-check-circle text-success me-1"></i><?= $perk ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Referral Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" class="style-19672">
            <div class="card-body p-4 text-center">
                <i class="fas fa-users fa-2x mb-3 opacity-75"></i>
                <div class="fs-2 fw-bold"><?= $referral_count ?></div>
                <div class="opacity-75"><?= __('assoc_ref_total_referrals', [], 'Total Referrals') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <i class="fas fa-rupee-sign fa-2x mb-3 text-success"></i>
                <div class="fs-2 fw-bold text-success">₹<?= number_format($referral_earnings) ?></div>
                <div class="text-muted"><?= __('assoc_ref_earnings', [], 'Referral Earnings') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <i class="fas fa-gift fa-2x mb-3 text-warning"></i>
                <div class="fs-2 fw-bold text-primary">₹500</div>
                <div class="text-muted"><?= __('assoc_ref_per_referral', [], 'Per Successful Referral') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Referral Code Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-share-alt me-2 text-primary"></i><?= __('assoc_ref_share_code', [], 'Share Your Referral Code') ?></h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="d-inline-block p-4 rounded-3" class="style-18667">
                <div class="small text-muted mb-2"><?= __('assoc_ref_your_code', [], 'Your Unique Referral Code') ?></div>
                <code id="referralCodeDisplay" class="fs-3 fw-bold" class="style-61650">
                    <?= htmlspecialchars($referral_code ?: __('assoc_ref_na', [], 'N/A')) ?>
                </code>
            </div>
        </div>

        <!-- Copy & Share Buttons -->
        <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
            <button class="btn btn-primary px-4" onclick="copyCode()">
                <i class="fas fa-copy me-2"></i><?= __('assoc_ref_copy_code', [], 'Copy Code') ?>
            </button>
            <button class="btn btn-success px-4" onclick="shareWhatsApp()">
                <i class="fab fa-whatsapp me-2"></i><?= __('assoc_ref_share_whatsapp', [], 'Share WhatsApp') ?>
            </button>
            <button class="btn btn-outline-primary px-4" onclick="shareTelegram()">
                <i class="fab fa-telegram me-2"></i><?= __('assoc_ref_telegram', [], 'Telegram') ?>
            </button>
            <button class="btn btn-outline-info px-4" onclick="shareSMS()">
                <i class="fas fa-sms me-2"></i><?= __('assoc_ref_sms', [], 'SMS') ?>
            </button>
        </div>

        <!-- Share Link -->
        <div class="mb-3">
            <label class="form-label fw-bold"><?= __('assoc_ref_share_link', [], 'Share Link') ?></label>
            <div class="input-group">
                <input type="text" class="form-control" id="shareLink" value="<?= htmlspecialchars($shareUrl ?? '') ?>" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">
                    <i class="fas fa-link me-1"></i> <?= __('assoc_ref_copy', [], 'Copy') ?>
                </button>
            </div>
        </div>

        <!-- More Share Options -->
        <div class="text-center">
            <button class="btn btn-outline-dark btn-sm me-2" onclick="shareFacebook()"><i class="fab fa-facebook-f me-1"></i> Facebook</button>
            <button class="btn btn-outline-dark btn-sm me-2" onclick="shareLinkedIn()"><i class="fab fa-linkedin-in me-1"></i> LinkedIn</button>
            <button class="btn btn-outline-dark btn-sm" onclick="shareTwitter()"><i class="fab fa-twitter me-1"></i> Twitter</button>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i><?= __('assoc_ref_how_works', [], 'How Referral Works') ?></h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" class="style-63775">
                    <i class="fas fa-share text-primary fa-lg"></i>
                </div>
                <h6><?= __('assoc_ref_step1_title', [], '1. Share Your Code') ?></h6>
                <p class="small text-muted"><?= __('assoc_ref_step1_desc', [], 'Share your unique referral code with friends, family, and contacts.') ?></p>
            </div>
            <div class="col-md-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" class="style-95624">
                    <i class="fas fa-user-plus text-success fa-lg"></i>
                </div>
                <h6><?= __('assoc_ref_step2_title', [], '2. They Register') ?></h6>
                <p class="small text-muted"><?= __('assoc_ref_step2_desc', [], 'When they register using your code, they become your referral.') ?></p>
            </div>
            <div class="col-md-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" class="style-47079">
                    <i class="fas fa-rupee-sign text-warning fa-lg"></i>
                </div>
                <h6><?= __('assoc_ref_step3_title', [], '3. Earn Rewards') ?></h6>
                <p class="small text-muted"><?= __('assoc_ref_step3_desc', [], 'Earn ₹500 for every successful referral who completes a purchase.') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Share Analytics -->
<?php if ($totalShares > 0): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-info"></i><?= __('assoc_ref_analytics', [], 'Share Analytics') ?></h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6 text-center">
                <div class="fs-3 fw-bold text-info"><?= $totalShares ?></div>
                <small class="text-muted"><?= __('assoc_ref_total_shares', [], 'Total Shares') ?></small>
            </div>
            <?php
            $platformLabels = [
                'whatsapp' => 'WhatsApp', 'facebook' => 'Facebook', 'twitter' => 'Twitter',
                'telegram' => 'Telegram', 'linkedin' => 'LinkedIn', 'email' => 'Email',
                'sms' => 'SMS', 'copy' => 'Copy', 'link' => 'Link', 'other' => 'Other'
            ];
            arsort($share_clicks);
            ?>
            <?php foreach (array_slice($share_clicks, 0, 5) as $platform => $count): ?>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center gap-2 p-2 rounded bg-light">
                    <i class="fab fa-<?= $platform === 'facebook' ? 'facebook-f' : ($platform === 'linkedin' ? 'linkedin-in' : ($platform === 'copy' || $platform === 'link' || $platform === 'other' ? 'share-alt' : $platform)) ?> fa-lg text-secondary"></i>
                    <div>
                        <div class="fw-bold"><?= $platformLabels[$platform] ?? ucfirst($platform) ?></div>
                        <small class="text-muted"><?= $count ?> <?= __('assoc_ref_clicks', [], 'clicks') ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Referred Users -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2 text-success"></i><?= __('assoc_ref_my_referrals', [], 'My Referrals') ?></h5>
        <span class="badge bg-success rounded-pill"><?= count($referred_users) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($referred_users)): ?>
        <div class="text-center py-5">
            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
            <p class="text-muted"><?= __('assoc_ref_no_referrals_yet', [], 'No referrals yet. Share your code to get started!') ?></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= __('assoc_ref_name', [], 'Name') ?></th>
                        <th><?= __('assoc_ref_contact', [], 'Contact') ?></th>
                        <th><?= __('assoc_ref_status', [], 'Status') ?></th>
                        <th><?= __('assoc_ref_source', [], 'Source') ?></th>
                        <th><?= __('assoc_ref_date', [], 'Date') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referred_users as $ref): ?>
                    <tr>
                        <td><?= htmlspecialchars($ref['referred_name'] ?: __('assoc_ref_pending_reg', [], 'Pending Registration')) ?></td>
                        <td>
                            <?php if ($ref['referred_email']): ?>
                            <small><?= htmlspecialchars($ref['referred_email'] ?? '') ?></small><br>
                            <?php endif; ?>
                            <?php if ($ref['referred_phone']): ?>
                            <small><?= htmlspecialchars($ref['referred_phone'] ?? '') ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $badgeClass = match($ref['status']) {
                                'registered' => 'bg-success',
                                'booked' => 'bg-primary',
                                'expired' => 'bg-secondary',
                                default => 'bg-warning text-dark'
                            };
                            $statusLabels = [
                                'pending' => __('assoc_ref_pending', [], 'Pending'),
                                'registered' => __('assoc_ref_registered', [], 'Registered'),
                                'booked' => __('assoc_ref_booked', [], 'Booked'),
                                'expired' => __('assoc_ref_expired', [], 'Expired'),
                            ];
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statusLabels[$ref['status']] ?? ucfirst($ref['status']) ?></span>
                        </td>
                        <td><small><?= htmlspecialchars($ref['source'] ?: '-') ?></small></td>
                        <td><small><?= date('d M Y', strtotime($ref['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const referralCode = <?= json_encode($referral_code) ?>;
const shareUrl = <?= json_encode($shareUrl) ?>;
const shareText = <?= json_encode($shareText) ?>;
const baseUrl = <?= json_encode(BASE_URL) ?>;

function trackShare(platform) {
    fetch(baseUrl + '/share/track', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({platform: platform, referral_code: referralCode})
    }).catch(() => {});
}

function copyCode() {
    navigator.clipboard.writeText(referralCode).then(() => {
        alert('<?= __('assoc_ref_code_copied', [], 'Referral code copied!') ?>');
        trackShare('copy');
    });
}

function copyLink() {
    navigator.clipboard.writeText(shareUrl).then(() => {
        alert('<?= __('assoc_ref_link_copied', [], 'Link copied!') ?>');
        trackShare('link');
    });
}

function shareWhatsApp() {
    window.open(`https://wa.me/?text=${encodeURIComponent(shareText + '\n' + shareUrl)}`, '_blank');
    trackShare('whatsapp');
}

function shareTelegram() {
    window.open(`https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`, '_blank');
    trackShare('telegram');
}

function shareSMS() {
    window.open(`sms:?body=${encodeURIComponent(shareText + ' ' + shareUrl)}`, '_blank');
    trackShare('sms');
}

function shareFacebook() {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`, '_blank');
    trackShare('facebook');
}

function shareLinkedIn() {
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`, '_blank');
    trackShare('linkedin');
}

function shareTwitter() {
    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`, '_blank');
    trackShare('twitter');
}
</script>
