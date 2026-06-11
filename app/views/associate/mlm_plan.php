<?php
$page_title = $page_title ?? 'MLM Plan & Commission Structure';
$current_page = 'mlm-plan';
$levels = $levels ?? [];
$current_plan = $current_plan ?? null;
$current_rank = $current_rank ?? 'Associate';
$next_rank = $next_rank ?? null;
$user_profile = $user_profile ?? null;
?>
<style>
.rank-card { transition: all 0.3s ease; cursor: default; }
.rank-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.12); }
.rank-card.current { border: 2px solid #6366f1; background: #f5f3ff; }
.rank-card.next { border: 2px dashed #f59e0b; }
.progress-ring { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-sitemap text-primary me-2"></i>MLM Plan & Commission Structure</h4>
    <?php if ($current_plan): ?>
        <span class="badge bg-success fs-6 px-3 py-2"><?php echo htmlspecialchars($current_plan['name']); ?> — Active</span>
    <?php endif; ?>
</div>

<?php if ($user_profile): ?>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Current Rank</div>
                <h3 class="text-primary mb-0 mt-1"><?php echo htmlspecialchars($current_rank); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Team Size</div>
                <h3 class="mb-0 mt-1"><?php echo (int)($user_profile['total_team_size'] ?? 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Direct Referrals</div>
                <h3 class="mb-0 mt-1"><?php echo (int)($user_profile['direct_referrals'] ?? 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Lifetime Sales</div>
                <h3 class="mb-0 mt-1">₹<?php echo number_format((float)($user_profile['lifetime_sales'] ?? 0)); ?></h3>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0"><i class="fas fa-trophy text-warning me-2"></i>Rank Achievement Path</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Rank</th>
                        <th>Direct Commission</th>
                        <th>Team Commission</th>
                        <th>Team Required</th>
                        <th>Directs Required</th>
                        <th>Monthly Target</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($levels as $i => $level):
                        $isCurrent = $level['level_name'] === $current_rank;
                        $isNext = $next_rank && $level['level_name'] === $next_rank['level_name'];
                        $rowClass = $isCurrent ? 'table-primary fw-bold' : ($isNext ? '' : '');
                    ?>
                        <tr class="rank-card <?php echo $isCurrent ? 'current' : ($isNext ? 'next' : ''); ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-<?php echo $isCurrent ? 'primary' : ($i < 4 ? 'secondary' : ($i < 7 ? 'info' : 'dark')); ?> me-1"><?php echo $i + 1; ?></span>
                                    <?php echo htmlspecialchars($level['level_name']); ?>
                                    <?php if ($isCurrent): ?>
                                        <span class="badge bg-primary ms-2"><i class="fas fa-check-circle"></i> Current</span>
                                    <?php elseif ($isNext): ?>
                                        <span class="badge bg-warning text-dark ms-2"><i class="fas fa-arrow-up"></i> Next</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><strong><?php echo rtrim(rtrim(number_format($level['direct_commission_percentage'], 2), '0'), '.'); ?>%</strong></td>
                            <td><?php echo rtrim(rtrim(number_format($level['team_commission_percentage'], 2), '0'), '.'); ?>%</td>
                            <td><?php echo (int)$level['team_size_required']; ?></td>
                            <td><?php echo (int)$level['direct_referrals_required']; ?></td>
                            <td>₹<?php echo number_format((float)$level['monthly_target']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-calculator text-success me-2"></i>How Differential Commission Works</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <p class="text-muted">As you grow in rank, you earn a higher commission percentage. The system pays you the <strong>difference</strong> between your rank percentage and what's already been paid to your downline.</p>
                <div class="bg-light p-3 rounded-3">
                    <strong>Example:</strong>
                    <ul class="mt-2 mb-0 small">
                        <li>You are <strong>Gold</strong> (16% commission)</li>
                        <li>Your downline Associate (10%) sells a ₹1,00,000 property</li>
                        <li>They earn 10% = ₹10,000</li>
                        <li>You earn the difference: 16% - 10% = 6% = ₹6,000</li>
                        <li><strong>Total paid: 16% — no pyramid!</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-gift text-info me-2"></i>Additional Earning Opportunities</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                    <span><i class="fas fa-user-plus text-success me-2"></i>Direct Associate Referral</span>
                    <strong>₹200 reward</strong>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                    <span><i class="fas fa-user-friends text-primary me-2"></i>Direct Agent Referral</span>
                    <strong>₹250 reward</strong>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                    <span><i class="fas fa-user text-info me-2"></i>Direct Customer Referral</span>
                    <strong>₹100 reward</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span><i class="fas fa-chart-line text-warning me-2"></i>Performance Bonus</span>
                    <strong>As per rank</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0"><i class="fas fa-link text-primary me-2"></i>Your Referral Link</h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <p class="text-muted">Share this link with potential users to grow your team:</p>
        <div class="input-group">
            <input type="text" class="form-control" id="refLink" value="<?php echo BASE_URL; ?>/associate/register?ref=<?php echo urlencode($_SESSION['referral_code'] ?? ''); ?>" readonly>
            <button class="btn btn-primary" onclick="copyRefLink()"><i class="fas fa-copy me-1"></i> Copy</button>
        </div>
        <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle"></i> When someone registers with this link, they'll be tagged under your network.</small>
    </div>
</div>

<script>
function copyRefLink() {
    var inp = document.getElementById('refLink');
    inp.select(); inp.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(inp.value).then(function() {
        alert('Referral link copied!');
    }).catch(function() { console.error('Copy failed'); });
}
</script>
