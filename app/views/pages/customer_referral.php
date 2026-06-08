<?php
$page_title = $page_title ?? 'Refer & Earn';
$current_page = 'referral';
$user = $user ?? [];
$referralCode = $referral_code ?? '';
$stats = $stats ?? ['total_referrals' => 0, 'successful_referrals' => 0, 'total_earned' => 0, 'pending_earned' => 0];
$referrals = $referrals ?? [];
$earnings = $earnings ?? [];
$shareLinks = $share_links ?? [];
?>

<div class="aps-cp-hero" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-gift me-2"></i>Refer & Earn</h2>
            <p>Invite friends and family to APS Dream Home. Earn 1% commission on every successful booking they make.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="display-5 fw-bold text-white" style="letter-spacing:0.15em; font-family:'Courier New',monospace;">
                <?= htmlspecialchars($referralCode) ?>
            </div>
            <small class="text-white-50">Your Referral Code</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-users"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)($stats['total_referrals'] ?? 0) ?>">0</div>
                <div class="aps-cp-stat-label">Total Referrals</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value" data-aps-count="<?= (int)($stats['successful_referrals'] ?? 0) ?>">0</div>
                <div class="aps-cp-stat-label">Booked &amp; Earned</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format((float)($stats['total_earned'] ?? 0)) ?></div>
                <div class="aps-cp-stat-label">Total Earned</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="aps-cp-stat aps-cp-stat--purple">
            <div class="aps-cp-stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format((float)($stats['pending_earned'] ?? 0)) ?></div>
                <div class="aps-cp-stat-label">Pending Payout</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-share-alt text-primary me-2"></i>Share Your Code</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="text-center p-4 mb-3" style="background:linear-gradient(135deg,#f8fafc,#ede9fe);border-radius:12px;border:2px dashed #7c3aed;">
                    <small class="text-muted d-block mb-1">YOUR REFERRAL CODE</small>
                    <div class="display-6 fw-bold text-primary" style="font-family:'Courier New',monospace;letter-spacing:3px;" id="refCode">
                        <?= htmlspecialchars($referralCode) ?>
                    </div>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyToClipboard('<?= htmlspecialchars($referralCode) ?>', this)">
                        <i class="fas fa-copy me-1"></i>Copy Code
                    </button>
                </div>

                <label class="form-label small text-muted fw-bold">REFERRAL LINK</label>
                <div class="input-group input-group-sm mb-3">
                    <input type="text" class="form-control" id="refLink" value="<?= htmlspecialchars($shareLinks['url'] ?? '') ?>" readonly>
                    <button class="btn btn-outline-primary" onclick="copyToClipboard(document.getElementById('refLink').value, this)">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>

                <label class="form-label small text-muted fw-bold">SHARE VIA</label>
                <div class="d-grid gap-2">
                    <a href="<?= htmlspecialchars($shareLinks['whatsapp'] ?? '#') ?>" target="_blank" class="btn btn-success">
                        <i class="fab fa-whatsapp me-2"></i>Share on WhatsApp
                    </a>
                    <a href="<?= htmlspecialchars($shareLinks['sms'] ?? '#') ?>" class="btn btn-secondary">
                        <i class="fas fa-sms me-2"></i>Share via SMS
                    </a>
                    <div class="row g-2">
                        <div class="col-4">
                            <a href="<?= htmlspecialchars($shareLinks['facebook'] ?? '#') ?>" target="_blank" class="btn btn-outline-primary w-100">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?= htmlspecialchars($shareLinks['twitter'] ?? '#') ?>" target="_blank" class="btn btn-outline-info w-100">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="<?= htmlspecialchars($shareLinks['email'] ?? '#') ?>" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-info-circle text-info me-2"></i>How It Works</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;line-height:22px;">1</span>
                    </div>
                    <div class="ms-3">
                        <strong class="d-block">Share Your Code</strong>
                        <small class="text-muted">Share your unique code with friends via WhatsApp, SMS, or social media.</small>
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;line-height:22px;">2</span>
                    </div>
                    <div class="ms-3">
                        <strong class="d-block">Friend Registers &amp; Books</strong>
                        <small class="text-muted">They register with your code and make their first plot booking.</small>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <span class="badge bg-success rounded-circle" style="width:32px;height:32px;line-height:22px;">3</span>
                    </div>
                    <div class="ms-3">
                        <strong class="d-block">You Earn 1% Commission</strong>
                        <small class="text-muted">Get 1% of the booking value as referral commission. Paid within 30 days.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-file-contract text-muted me-2"></i>Terms</h5>
            </div>
            <div class="aps-cp-card-body small text-muted">
                <ul class="mb-0 ps-3">
                    <li>1% flat commission on the total booking amount.</li>
                    <li>Commission is credited after the booking is confirmed by admin.</li>
                    <li>Maximum 100 referrals per user.</li>
                    <li>Payouts are processed within 30 days of booking confirmation.</li>
                    <li>Self-referrals are not allowed.</li>
                    <li>APS Dream Home reserves the right to modify or terminate the program.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-user-friends text-primary me-2"></i>Your Referrals (<?= count($referrals) ?>)</h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($referrals)): ?>
                    <div class="aps-cp-empty">
                        <div class="aps-cp-empty-icon"><i class="fas fa-user-plus"></i></div>
                        <h5>No referrals yet</h5>
                        <p>Share your referral code to start earning!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="aps-cp-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th class="text-end">Commission</th>
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
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Booked</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Joined</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?= date('M j, Y', strtotime($r['created_at'] ?? 'now')) ?></small></td>
                                    <td class="text-end fw-bold text-success">₹<?= number_format((float)($r['commission_earned'] ?? 0), 2) ?></td>
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
                <h5><i class="fas fa-coins text-success me-2"></i>Earnings History</h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($earnings)): ?>
                    <div class="aps-cp-empty">
                        <div class="aps-cp-empty-icon"><i class="fas fa-receipt"></i></div>
                        <h5>No earnings yet</h5>
                        <p>Earn 1% commission when your referrals book a plot.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="aps-cp-table">
                            <thead>
                                <tr>
                                    <th>Referred User</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($earnings as $e): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($e['referred_name'] ?? 'User') ?></strong><br><small class="text-muted"><?= htmlspecialchars($e['notes'] ?? '') ?></small></td>
                                    <td><small class="text-muted"><?= date('M j, Y', strtotime($e['created_at'] ?? 'now')) ?></small></td>
                                    <td class="text-end fw-bold text-success">₹<?= number_format((float)($e['amount'] ?? 0), 2) ?></td>
                                    <td>
                                        <?php
                                        $st = $e['status'] ?? 'pending';
                                        $badge = match($st) { 'paid' => 'success', 'cancelled' => 'danger', default => 'warning' };
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
    </div>
</div>

<script>
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        btn.classList.add('btn-success');
        setTimeout(function() {
            btn.innerHTML = orig;
            btn.classList.remove('btn-success');
        }, 2000);
    });
}
</script>
