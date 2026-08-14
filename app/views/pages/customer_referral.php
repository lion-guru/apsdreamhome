<?php
$page_title = $page_title ?? 'Refer & Earn';
$current_page = 'referral';
$user = $user ?? [];
$referralCode = $referral_code ?? '';
$stats = $stats ?? ['total_referrals' => 0, 'successful_referrals' => 0, 'total_earned' => 0, 'pending_earned' => 0];
$referrals = $referrals ?? [];
$earnings = $earnings ?? [];
$shareLinks = $share_links ?? [];
$tierInfo = $tier_info ?? ['tier' => 'bronze', 'label' => 'Bronze', 'color' => '#CD7F32', 'icon' => 'fas fa-medal', 'total_referrals' => 0, 'next_tier' => 'Silver', 'next_tier_min' => 5, 'progress' => 0, 'referrals_needed' => 5, 'perks' => [], 'bonus_per_referral' => 100, 'bonus_on_booking' => 500];
?>

<div class="aps-cp-hero" class="style-1227">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-gift me-2"></i><?= __('referral_hero_title') ?></h2>
            <p><?= __('referral_hero_desc') ?></p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="display-5 fw-bold text-white" class="style-96215">
                <?= htmlspecialchars($referralCode) ?>
            </div>
            <small class="text-white-50"><?= __('referral_your_code') ?></small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-users"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)($stats['total_referrals'] ?? 0) ?>">0</div>
                <div class="aps-cp-stat-label"><?= __('referral_total_referrals') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)($stats['successful_referrals'] ?? 0) ?>">0</div>
                <div class="aps-cp-stat-label"><?= __('referral_booked_earned') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">â‚¹<?= number_format((float)($stats['total_earned'] ?? 0)) ?></div>
                <div class="aps-cp-stat-label"><?= __('referral_total_earned') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--purple">
            <div class="aps-cp-stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">â‚¹<?= number_format((float)($stats['pending_earned'] ?? 0)) ?></div>
                <div class="aps-cp-stat-label"><?= __('referral_pending_payout') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Tier Card -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="aps-cp-card" class="style-61637">
            <div class="aps-cp-card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="style-63527">
                            <i class="<?= $tierInfo['icon'] ?>" class="style-1525"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" class="style-72606"><?= $tierInfo['label'] ?> Tier</h5>
                            <small class="text-muted">â‚¹<?= number_format($tierInfo['bonus_per_referral']) ?> per signup Â· â‚¹<?= number_format($tierInfo['bonus_on_booking']) ?> on booking</small>
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
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-share-alt text-primary me-2"></i><?= __('referral_share_your_code') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="text-center p-4 mb-3" class="style-5014">
                    <small class="text-muted d-block mb-1"><?= __('referral_your_referral_code') ?></small>
                    <div class="display-6 fw-bold text-primary" class="style-32630" id="refCode">
                        <?= htmlspecialchars($referralCode) ?>
                    </div>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyToClipboard('<?= htmlspecialchars($referralCode) ?>', this)">
                        <i class="fas fa-copy me-1"></i><?= __('referral_copy_code') ?>
                    </button>
                </div>

                <label class="form-label small text-muted fw-bold"><?= __('referral_referral_link') ?></label>
                <div class="input-group input-group-sm mb-3">
                    <input type="text" class="form-control" id="refLink" value="<?= htmlspecialchars($shareLinks['url'] ?? '') ?>" readonly>
                    <button class="btn btn-outline-primary" onclick="copyToClipboard(document.getElementById('refLink').value, this)">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>

                <label class="form-label small text-muted fw-bold"><?= __('referral_share_via') ?></label>
                <div class="d-grid gap-2">
                    <a href="<?= htmlspecialchars($shareLinks['whatsapp'] ?? '#') ?>" target="_blank" class="btn btn-success" onclick="trackShare('whatsapp')">
                        <i class="fab fa-whatsapp me-2"></i><?= __('referral_share_whatsapp') ?>
                    </a>
                    <a href="<?= htmlspecialchars($shareLinks['sms'] ?? '#') ?>" class="btn btn-secondary" onclick="trackShare('sms')">
                        <i class="fas fa-sms me-2"></i><?= __('referral_share_sms') ?>
                    </a>
                    <div class="row g-2">
                        <div class="col-4">
                            <a href="<?= htmlspecialchars($shareLinks['facebook'] ?? '#') ?>" target="_blank" class="btn btn-outline-primary w-100" onclick="trackShare('facebook')">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?= htmlspecialchars($shareLinks['twitter'] ?? '#') ?>" target="_blank" class="btn btn-outline-info w-100" onclick="trackShare('twitter')">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?= htmlspecialchars($shareLinks['email'] ?? '#') ?>" class="btn btn-outline-secondary w-100" onclick="trackShare('email')">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-info-circle text-info me-2"></i><?= __('referral_how_it_works') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <span class="badge bg-primary rounded-circle" class="style-6262">1</span>
                    </div>
                    <div class="ms-3">
                        <strong class="d-block"><?= __('referral_step1_title') ?></strong>
                        <small class="text-muted"><?= __('referral_step1_desc') ?></small>
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <span class="badge bg-primary rounded-circle" class="style-6262">2</span>
                    </div>
                    <div class="ms-3">
                        <strong class="d-block"><?= __('referral_step2_title') ?></strong>
                        <small class="text-muted"><?= __('referral_step2_desc') ?></small>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <span class="badge bg-success rounded-circle" class="style-6262">3</span>
                    </div>
                    <div class="ms-3">
                        <strong class="d-block"><?= __('referral_step3_title') ?></strong>
                        <small class="text-muted"><?= __('referral_step3_desc') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-file-contract text-muted me-2"></i><?= __('referral_terms') ?></h5>
            </div>
            <div class="aps-cp-card-body small text-muted">
                <ul class="mb-0 ps-3">
                    <li><?= __('referral_term1') ?></li>
                    <li><?= __('referral_term2') ?></li>
                    <li><?= __('referral_term3') ?></li>
                    <li><?= __('referral_term4') ?></li>
                    <li><?= __('referral_term5') ?></li>
                    <li><?= __('referral_term6') ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-user-friends text-primary me-2"></i><?= sprintf(__('referral_your_referrals'), count($referrals)) ?></h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($referrals)): ?>
                    <div class="aps-cp-empty">
                        <div class="aps-cp-empty-icon"><i class="fas fa-user-plus"></i></div>
                        <h5><?= __('referral_no_referrals') ?></h5>
                        <p><?= __('referral_no_referrals_hint') ?></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="aps-cp-table">
                            <thead>
                                <tr>
                                    <th><?= __('referral_table_name') ?></th>
                                    <th><?= __('referral_table_contact') ?></th>
                                    <th><?= __('referral_table_status') ?></th>
                                    <th><?= __('referral_table_joined') ?></th>
                                    <th class="text-end"><?= __('referral_table_commission') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($referrals as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['name'] ?? 'User') ?></strong></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php if (!empty($r['phone'])): ?>
                                                <i class="fas fa-phone me-1"></i><?= htmlspecialchars($r['phone']) ?>
                                            <?php else: ?>
                                                <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($r['email'] ?? '') ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (!empty($r['has_booking'])): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i><?= __('referral_status_booked') ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= __('referral_status_joined') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?= date('M j, Y', strtotime($r['created_at'] ?? 'now')) ?></small></td>
                                    <td class="text-end fw-bold text-success">â‚¹<?= number_format((float)($r['commission_earned'] ?? 0), 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-coins text-success me-2"></i><?= __('referral_earnings_history') ?></h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($earnings)): ?>
                    <div class="aps-cp-empty">
                        <div class="aps-cp-empty-icon"><i class="fas fa-receipt"></i></div>
                        <h5><?= __('referral_no_earnings') ?></h5>
                        <p><?= __('referral_no_earnings_hint') ?></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="aps-cp-table">
                            <thead>
                                <tr>
                                    <th><?= __('referral_earnings_referred') ?></th>
                                    <th><?= __('referral_earnings_date') ?></th>
                                    <th class="text-end"><?= __('referral_earnings_amount') ?></th>
                                    <th><?= __('referral_earnings_status') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($earnings as $e): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($e['referred_name'] ?? 'User') ?></strong><br><small class="text-muted"><?= htmlspecialchars($e['notes'] ?? '') ?></small></td>
                                    <td><small class="text-muted"><?= date('M j, Y', strtotime($e['created_at'] ?? 'now')) ?></small></td>
                                    <td class="text-end fw-bold text-success">â‚¹<?= number_format((float)($e['amount'] ?? 0), 2) ?></td>
                                    <td>
                                        <?php
                                        $st = $e['status'] ?? 'pending';
                                        $badge = match($st) { 'paid' => 'success', 'cancelled' => 'danger', default => 'warning' };
$shareStats = $share_stats ?? ['total' => 0, 'by_platform' => [], 'recent' => []];
$leaderboard = $leaderboard ?? [];
?>
                                        <span class="badge bg-<?= $badge ?>"><?= ucfirst(htmlspecialchars($st)) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Share Analytics -->
        <?php if ($shareStats['total'] > 0): ?>
        <div class="aps-cp-card mt-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-chart-bar text-info me-2"></i>Your Share Analytics</h5>
                <span class="badge bg-info"><?= $shareStats['total'] ?> total shares</span>
            </div>
            <div class="aps-cp-card-body">
                <?php if (!empty($shareStats['by_platform'])): ?>
                <div class="d-flex gap-3 flex-wrap mb-3">
                    <?php foreach ($shareStats['by_platform'] as $p):
                        $platformIcons = ['whatsapp'=>'fab fa-whatsapp text-success', 'sms'=>'fas fa-sms text-secondary', 'facebook'=>'fab fa-facebook-f text-primary', 'twitter'=>'fab fa-twitter text-info', 'email'=>'fas fa-envelope text-warning', 'telegram'=>'fab fa-telegram text-info', 'copy'=>'fas fa-copy text-muted'];
                    ?>
                    <div class="text-center">
                        <div class="style-73326">
                            <i class="<?= $platformIcons[$p['share_method']] ?? 'fas fa-share text-muted' ?>" class="style-88102"></i>
                        </div>
                        <div class="fw-bold mt-1" class="style-51894"><?= $p['cnt'] ?></div>
                        <small class="text-muted" class="style-68658"><?= ucfirst($p['share_method']) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($shareStats['recent'])): ?>
                <small class="text-muted fw-bold d-block mb-2">Recent Shares</small>
                <?php foreach (array_slice($shareStats['recent'], 0, 5) as $rs): ?>
                <div class="d-flex justify-content-between align-items-center py-1" class="style-34922">
                    <div>
                        <i class="<?= $platformIcons[$rs['share_method']] ?? 'fas fa-share' ?> me-2 text-muted"></i>
                        <span class="style-47175"><?= ucfirst(htmlspecialchars($rs['share_method'])) ?></span>
                        <?php if (!empty($rs['lead_name'])): ?>
                            <small class="text-muted"> â€” <?= htmlspecialchars($rs['lead_name']) ?></small>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted"><?= date('M d, g:i A', strtotime($rs['created_at'])) ?></small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Referral Leaderboard -->
        <?php if (!empty($leaderboard)): ?>
        <div class="aps-cp-card mt-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-trophy text-warning me-2"></i>Top Referrers</h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive">
                    <table class="aps-cp-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th class="text-center">Referrals</th>
                                <th class="text-end">Earned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $idx => $lb): ?>
                            <tr class="style-7671">
                                <td>
                                    <?php if ($idx === 0): ?><i class="fas fa-crown text-warning"></i>
                                    <?php elseif ($idx === 1): ?><i class="fas fa-medal text-secondary"></i>
                                    <?php elseif ($idx === 2): ?><i class="fas fa-medal" class="style-68030"></i>
                                    <?php else: ?><?= $idx + 1 ?><?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($lb['name'] ?? '') ?></strong>
                                    <?php if (($lb['name'] ?? '') === ($user['name'] ?? '')): ?>
                                        <span class="badge bg-success ms-1" class="style-56522">You</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= (int)$lb['referral_count'] ?></td>
                                <td class="text-end fw-bold text-success">â‚¹<?= number_format((float)$lb['total_earned']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i><?= __('referral_copied') ?>';
        btn.classList.add('btn-success');
        setTimeout(function() {
            btn.innerHTML = orig;
            btn.classList.remove('btn-success');
        }, 2000);
    });
}

function trackShare(platform) {
    var code = '<?= htmlspecialchars($referralCode) ?>';
    if (!code) return;
    try {
        var fd = new FormData();
        fd.append('platform', platform);
        fd.append('referral_code', code);
        fd.append('message', 'Shared via ' + platform);
        fetch('<?= BASE_URL ?>/share/track', { method: 'POST', body: fd, credentials: 'same-origin' });
    } catch(e) {}
}
</script>
