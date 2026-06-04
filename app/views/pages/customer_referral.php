<?php
$page_title = $page_title ?? 'Refer & Earn';
$current_page = 'referral';
$user = $user ?? [];
$referralCode = $referral_code ?? '';
$stats = $stats ?? [];
$referrals = $referrals ?? [];
$earnings = $earnings ?? [];
$shareLinks = $share_links ?? [];
?>

<div class="content-area p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1"><i class="fas fa-gift text-primary me-2"></i>Refer & Earn</h2>
            <p class="text-muted mb-0">Invite your friends and family. Earn commission on every successful referral.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small opacity-75">Total Referrals</div>
                            <div class="h2 mb-0 fw-bold"><?php echo number_format($stats['total_referrals'] ?? 0); ?></div>
                            <small class="opacity-75"><?php echo number_format($stats['active_referrals'] ?? 0); ?> active</small>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small opacity-75">Total Earnings</div>
                            <div class="h2 mb-0 fw-bold">₹<?php echo number_format($stats['total_earnings'] ?? 0); ?></div>
                            <small class="opacity-75">All time</small>
                        </div>
                        <i class="fas fa-coins fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small opacity-75">This Month</div>
                            <div class="h2 mb-0 fw-bold">₹<?php echo number_format($stats['this_month_earnings'] ?? 0); ?></div>
                            <small class="opacity-75">Last 30 days</small>
                        </div>
                        <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small opacity-75">Pending Payout</div>
                            <div class="h2 mb-0 fw-bold">₹<?php echo number_format($stats['pending_earnings'] ?? 0); ?></div>
                            <small class="opacity-75">Awaiting approval</small>
                        </div>
                        <i class="fas fa-hourglass-half fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Your Referral Code</h5>
                </div>
                <div class="card-body">
                    <div class="referral-code-box text-center p-4 mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 8px; border: 2px dashed #667eea;">
                        <div class="text-muted small mb-2">SHARE THIS CODE</div>
                        <div class="referral-code display-4 fw-bold text-primary" style="font-family: 'Courier New', monospace; letter-spacing: 2px;"><?php echo htmlspecialchars($referralCode); ?></div>
                        <button class="btn btn-sm btn-outline-primary mt-3" onclick="copyReferralCode()">
                            <i class="fas fa-copy me-1"></i>Copy Code
                        </button>
                    </div>

                    <h6 class="text-muted small mb-2">SHARE VIA</h6>
                    <div class="d-grid gap-2">
                        <a href="<?php echo htmlspecialchars($shareLinks['whatsapp'] ?? '#'); ?>" target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp me-2"></i>Share on WhatsApp
                        </a>
                        <a href="<?php echo htmlspecialchars($shareLinks['telegram'] ?? '#'); ?>" target="_blank" class="btn btn-info text-white">
                            <i class="fab fa-telegram me-2"></i>Share on Telegram
                        </a>
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="<?php echo htmlspecialchars($shareLinks['facebook'] ?? '#'); ?>" target="_blank" class="btn btn-primary w-100">
                                    <i class="fab fa-facebook me-1"></i>Facebook
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo htmlspecialchars($shareLinks['twitter'] ?? '#'); ?>" target="_blank" class="btn btn-dark w-100">
                                    <i class="fab fa-twitter me-1"></i>Twitter
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small text-muted">Direct Referral Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($shareLinks['url'] ?? ''); ?>" id="referralLink" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyReferralLink()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-info"></i>How It Works</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle" style="width:32px; height:32px; line-height:22px;">1</span>
                        </div>
                        <div class="ms-3">
                            <strong class="d-block">Share Your Code</strong>
                            <small class="text-muted">Share your unique referral code with friends via WhatsApp, social media, or directly.</small>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle" style="width:32px; height:32px; line-height:22px;">2</span>
                        </div>
                        <div class="ms-3">
                            <strong class="d-block">Friend Registers & Buys</strong>
                            <small class="text-muted">When they register with your code and make their first purchase.</small>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle" style="width:32px; height:32px; line-height:22px;">3</span>
                        </div>
                        <div class="ms-3">
                            <strong class="d-block">You Earn Commission</strong>
                            <small class="text-muted">Get up to 5% commission on every successful referral. Paid out monthly.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-friends me-2 text-primary"></i>Your Referrals (<?php echo count($referrals); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($referrals)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-user-plus fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No referrals yet. Start sharing your code!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Contact</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($referrals as $r): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($r['name'] ?? 'User'); ?></strong></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php if (!empty($r['phone'])): ?>
                                                        <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($r['phone']); ?>
                                                    <?php elseif (!empty($r['email'])): ?>
                                                        <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($r['email']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo ($r['role'] ?? '') === 'associate' ? 'info' : 'secondary'; ?>"><?php echo htmlspecialchars($r['role'] ?? 'user'); ?></span>
                                            </td>
                                            <td><small class="text-muted"><?php echo date('M j, Y', strtotime($r['created_at'] ?? 'now')); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-coins me-2 text-success"></i>Earnings History</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($earnings)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No earnings yet. Refer friends to start earning!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($earnings as $e): ?>
                                        <tr>
                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($e['commission_type'] ?? '—'); ?></span></td>
                                            <td><small><?php echo htmlspecialchars($e['description'] ?? '—'); ?></small></td>
                                            <td><small class="text-muted"><?php echo date('M j, Y', strtotime($e['created_at'] ?? 'now')); ?></small></td>
                                            <td class="text-end fw-bold text-success">₹<?php echo number_format((float)($e['amount'] ?? 0), 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    $st = $e['status'] ?? 'pending';
                                                    echo $st === 'paid' ? 'success' : ($st === 'cancelled' ? 'danger' : 'warning');
                                                ?>"><?php echo htmlspecialchars($st); ?></span>
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
</div>

<style>
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.bg-gradient-warning { background: linear-gradient(135deg, #f5af19 0%, #f12711 100%); }
.bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.referral-code-box { transition: transform 0.2s; }
.referral-code-box:hover { transform: translateY(-2px); }
</style>

<script>
function copyReferralCode() {
    const code = '<?php echo addslashes($referralCode); ?>';
    navigator.clipboard.writeText(code).then(() => {
        showToast('Code copied: ' + code);
    });
}
function copyReferralLink() {
    const link = document.getElementById('referralLink').value;
    navigator.clipboard.writeText(link).then(() => {
        showToast('Link copied!');
    });
}
function showToast(msg) {
    const t = document.createElement('div');
    t.style.cssText = 'position:fixed;top:20px;right:20px;background:#10b981;color:#fff;padding:12px 20px;border-radius:6px;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.2);';
    t.innerHTML = '<i class="fas fa-check me-2"></i>' + msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
}
</script>
