<?php
$page_title = $page_title ?? 'MLM Plan & Commission Structure';
$current_page = 'mlm-plan';
$levels = $levels ?? [];
$current_plan = $current_plan ?? null;
$current_rank = $current_rank ?? 'Associate';
$next_rank = $next_rank ?? null;
$user_profile = $user_profile ?? null;
$rank_benefits = $rank_benefits ?? [];
$mlm_settings = $mlm_settings ?? [];
$user_commission_total = $user_commission_total ?? 0;
$user_team_size = $user_team_size ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '';

$rankDisplayNames = [
    'associate' => 'Associate',
    'senior_associate' => 'Senior Associate',
    'bdm' => 'BDM',
    'sr_bdm' => 'Sr. BDM',
    'vice_president' => 'Vice President',
    'president' => 'President',
    'site_manager' => 'Site Manager',
];

// Build rank rates array for differential calculation
$rankRates = [];
foreach ($rank_benefits as $rb) {
    $rankRates[strtolower($rb['rank_name'])] = (float)($rb['direct_sale_pct'] ?? 0);
}
?>
<style>
.plan-hero {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #fff; border-radius: 16px; padding: 40px 30px; margin-bottom: 30px;
}
.plan-hero h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; }
.plan-hero p { opacity: 0.9; font-size: 1.05rem; margin: 0; }
.rank-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; background: #fff; transition: all .25s ease; position: relative; overflow: hidden; }
.rank-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.1); }
.rank-card.current-rank { border: 2px solid #0d9488; background: #f5f3ff; }
.rank-card.current-rank::after { content: 'YOU ARE HERE'; position: absolute; top: 12px; right: -30px; background: #0d9488; color: #fff; font-size: .65rem; font-weight: 700; padding: 3px 35px; transform: rotate(45deg); }
.rank-badge { width: 52px; height: 52px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fff; font-weight: 700; }
.track-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; height: 100%; }
.track-box h5 { color: #0d9488; font-weight: 700; margin-bottom: 12px; }
.step-circle { width: 40px; height: 40px; border-radius: 50%; background: #0d9488; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .95rem; flex-shrink: 0; }
.earning-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid #f1f5f9; }
.earning-row:last-child { border-bottom: none; }
.progress-track { background: #e5e7eb; border-radius: 20px; height: 8px; position: relative; margin: 10px 0; }
.progress-fill { background: linear-gradient(90deg, #0d9488, #0f766e); border-radius: 20px; height: 100%; transition: width .5s ease; }
.faqs .accordion-button { font-weight: 600; color: #1e293b; }
.faqs .accordion-button:not(.collapsed) { background: #f5f3ff; color: #0d9488; }
</style>

<!-- HERO -->
<div class="plan-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1><i class="fas fa-sitemap me-2"></i>APS Dream Home MLM Plan</h1>
            <p>Build your network, earn commissions, and grow with us. Our transparent 7-rank system rewards performance at every level.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <?php if ($current_plan): ?>
                <span class="badge bg-white text-primary fs-6 px-3 py-2"><i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($current_plan['plan_name'] ?? 'Active Plan') ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MY STATS (logged-in only) -->
<?php if ($user_profile): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Your Rank</div>
                <h4 class="text-primary mb-0"><?= htmlspecialchars($current_rank) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Team Size</div>
                <h4 class="mb-0"><?= number_format($user_team_size) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Direct Referrals</div>
                <h4 class="mb-0"><?= (int)($user_profile['direct_referrals'] ?? 0) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Total Earned</div>
                <h4 class="mb-0">₹<?= number_format($user_commission_total) ?></h4>
            </div>
        </div>
    </div>
</div>

<?php if ($next_rank): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold">Progress to <strong><?= htmlspecialchars($next_rank['level_name'] ?? $next_rank['rank_name'] ?? '') ?></strong></span>
            <span class="text-muted small">₹<?= number_format((float)($user_profile['lifetime_sales'] ?? 0)) ?> / ₹<?= number_format((float)($next_rank['team_size_required'] ?? $next_rank['min_qualifying_volume'] ?? 0)) ?></span>
        </div>
        <div class="progress-track">
            <?php $pct = min(100, ((float)($user_profile['lifetime_sales'] ?? 0) / max(1, (float)($next_rank['team_size_required'] ?? $next_rank['min_qualifying_volume'] ?? 1))) * 100); ?>
            <div class="progress-fill" style="width: <?= number_format($pct, 1) ?>%"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted"><?= htmlspecialchars($current_rank) ?></small>
            <small class="text-muted"><?= htmlspecialchars($next_rank['level_name'] ?? $next_rank['rank_name'] ?? '') ?></small>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- HOW IT WORKS — 3 STEPS -->
<div class="mb-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>How It Works — 3 Simple Steps</h4>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="track-box text-center">
                <div class="step-circle mx-auto mb-3">1</div>
                <h5>Register & Get Your Link</h5>
                <p class="text-muted mb-0">Sign up as an Associate and get your unique referral link. Share it with anyone interested in real estate.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="track-box text-center">
                <div class="step-circle mx-auto mb-3">2</div>
                <h5>Build Your Network</h5>
                <p class="text-muted mb-0">When someone registers through your link, they join your network. Each person who joins adds to your team.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="track-box text-center">
                <div class="step-circle mx-auto mb-3">3</div>
                <h5>Earn Commissions</h5>
                <p class="text-muted mb-0">Every time your network sells a plot, you earn a commission based on your rank. Higher rank = higher earnings!</p>
            </div>
        </div>
    </div>
</div>

<!-- 4 EARNING TRACKS -->
<div class="mb-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-coins text-success me-2"></i>4 Ways to Earn</h4>
    <div class="row g-4">
        <!-- Track A -->
        <div class="col-md-6">
            <div class="track-box">
                <h5><i class="fas fa-handshake text-primary me-2"></i>Track A — Direct & Upline Commission</h5>
                <p class="text-muted small">Earn on every plot sale in your network. Commission flows from the agent who made the sale, up through their upline as differential commission.</p>
                <div class="bg-white rounded-3 p-3 mb-2">
                    <?php
                    // Real differential model: upline gets (their_rate - downline_rate)
                    $exampleSale = 1500000;
                    // Use real rates from DB
                    $associateRate = $rankRates['associate'] ?? 5;
                    $srAssocRate = $rankRates['senior_associate'] ?? 7;
                    $bdmRate = $rankRates['bdm'] ?? 10;
                    $srBdmRate = $rankRates['sr_bdm'] ?? 12;
                    $vpRate = $rankRates['vice_president'] ?? 15;
                    $presidentRate = $rankRates['president'] ?? 18;
                    $siteManagerRate = $rankRates['site_manager'] ?? 20;

                    // Example: Associate (5%) makes a sale
                    $agentComm = (int)($exampleSale * $associateRate / 100);
                    // Sr. Associate upline gets differential: 7% - 5% = 2%
                    $upline1 = (int)($exampleSale * max(0, $srAssocRate - $associateRate) / 100);
                    // BDM upline gets differential: 10% - 7% = 3%
                    $upline2 = (int)($exampleSale * max(0, $bdmRate - $srAssocRate) / 100);
                    // Sr. BDM upline gets differential: 12% - 10% = 2%
                    $upline3 = (int)($exampleSale * max(0, $srBdmRate - $bdmRate) / 100);
                    $total = $agentComm + $upline1 + $upline2 + $upline3;
                    $cap = (int)($exampleSale * 0.20);
                    ?>
                    <strong class="small text-muted">EXAMPLE: Associate sells plot for ₹15,00,000</strong>
                    <table class="table table-sm mt-2 mb-0">
                        <tr><td>Agent (Associate — <?= $associateRate ?>%)</td><td class="text-end fw-bold">₹<?= number_format($agentComm) ?></td></tr>
                        <?php if ($upline1 > 0): ?>
                        <tr><td>Upline L1 (Sr. Associate — <?= $srAssocRate ?>% − <?= $associateRate ?>% = <?= $srAssocRate - $associateRate ?>%)</td><td class="text-end fw-bold">₹<?= number_format($upline1) ?></td></tr>
                        <?php endif; ?>
                        <?php if ($upline2 > 0): ?>
                        <tr><td>Upline L2 (BDM — <?= $bdmRate ?>% − <?= $srAssocRate ?>% = <?= $bdmRate - $srAssocRate ?>%)</td><td class="text-end fw-bold">₹<?= number_format($upline2) ?></td></tr>
                        <?php endif; ?>
                        <?php if ($upline3 > 0): ?>
                        <tr><td>Upline L3 (Sr. BDM — <?= $srBdmRate ?>% − <?= $bdmRate ?>% = <?= $srBdmRate - $bdmRate ?>%)</td><td class="text-end fw-bold">₹<?= number_format($upline3) ?></td></tr>
                        <?php endif; ?>
                        <tr class="table-primary"><td><strong>Total Commission (capped at 20%)</strong></td><td class="text-end fw-bold">₹<?= number_format($total) ?></td></tr>
                    </table>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Upline earns the <strong>difference</strong> between their rank rate and the downline's rate. Total per sale is capped at 20%.</small>
            </div>
        </div>
        <!-- Track B -->
        <div class="col-md-6">
            <div class="track-box">
                <h5><i class="fas fa-chart-line text-success me-2"></i>Track B — Performance Bonus</h5>
                <p class="text-muted small">Earn extra when your team hits consistent monthly sales targets. Consecutive qualifying months unlock bonus multipliers.</p>
                <div class="bg-white rounded-3 p-3 mb-2">
                    <strong class="small text-muted">BONUS STRUCTURE:</strong>
                    <table class="table table-sm mt-2 mb-0">
                        <tr><td>3 consecutive qualifying months</td><td class="text-end fw-bold">+0.9% bonus</td></tr>
                        <tr><td>6 consecutive qualifying months</td><td class="text-end fw-bold">+1.2% bonus</td></tr>
                        <tr><td>12 consecutive qualifying months</td><td class="text-end fw-bold">+1.5% bonus</td></tr>
                    </table>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>A "qualifying month" means your team generated at least ₹50,000 in sales that month.</small>
            </div>
        </div>
        <!-- Track C -->
        <div class="col-md-6">
            <div class="track-box">
                <h5><i class="fas fa-trophy text-warning me-2"></i>Track C — Milestone Escrow</h5>
                <p class="text-muted small">2% of every sale goes into a personal escrow. When it reaches your milestone threshold, you receive a bonus payout.</p>
                <div class="bg-white rounded-3 p-3 mb-2">
                    <strong class="small text-muted">MILESTONE TIERS:</strong>
                    <table class="table table-sm mt-2 mb-0">
                        <tr><td>₹50,000 accumulated</td><td class="text-end fw-bold">Bronze Milestone</td></tr>
                        <tr><td>₹2,00,000 accumulated</td><td class="text-end fw-bold">Silver Milestone</td></tr>
                        <tr><td>₹5,00,000 accumulated</td><td class="text-end fw-bold">Gold Milestone</td></tr>
                        <tr><td>₹15,00,000 accumulated</td><td class="text-end fw-bold">Diamond Milestone</td></tr>
                    </table>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Escrow is separate from Track A. This is your personal reserve — not shared with upline.</small>
            </div>
        </div>
        <!-- Royalty Pool -->
        <div class="col-md-6">
            <div class="track-box">
                <h5><i class="fas fa-crown text-danger me-2"></i>Track D — Royalty Pool (Senior Leaders)</h5>
                <p class="text-muted small">Vice President and above share 2% of all company sales, distributed proportional to lifetime sales volume.</p>
                <div class="bg-white rounded-3 p-3 mb-2">
                    <strong class="small text-muted">ELIGIBILITY:</strong>
                    <table class="table table-sm mt-2 mb-0">
                        <tr><td>Vice President (GBV ≥ ₹1.5 Cr)</td><td class="text-end fw-bold">Eligible</td></tr>
                        <tr><td>President (GBV ≥ ₹3 Cr)</td><td class="text-end fw-bold">Eligible</td></tr>
                        <tr><td>Site Manager (GBV ≥ ₹5 Cr)</td><td class="text-end fw-bold">Eligible</td></tr>
                    </table>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Distributed monthly. This is additional income on top of Tracks A/B/C.</small>
            </div>
        </div>
    </div>
</div>

<!-- RANK LADDER -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-layer-group text-primary me-2"></i>Rank Ladder — Your Path to the Top</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Rank</th>
                        <th>Min Leg Count</th>
                        <th>Min Qualifying Vol.</th>
                        <th>Direct Sale %</th>
                        <th>Override %</th>
                        <th>Perks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rankIcons = ['fa-user','fa-user-tie','fa-briefcase','fa-chart-bar','fa-gem','fa-crown','fa-star'];
                    foreach ($rank_benefits as $i => $rb):
                        $rankKey = strtolower($rb['rank_name'] ?? '');
                        $displayName = $rankDisplayNames[$rankKey] ?? ucwords(str_replace('_', ' ', $rb['rank_name'] ?? ''));
                        $isCurrent = (strtolower(str_replace(' ', '_', $current_rank)) === $rankKey);
                        $rowClass = $isCurrent ? 'table-primary fw-bold' : '';
                        $colorCode = $rb['color_code'] ?? '#6c757d';
                        $perks = json_decode($rb['perks'] ?? '{}', true);
                        $perkText = $perks['training'] ?? '';
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rank-badge me-1" style="width:32px;height:32px;font-size:.8rem;background:<?= htmlspecialchars($colorCode) ?>;">
                                        <i class="fas <?= $rankIcons[$i] ?? 'fa-user' ?>"></i>
                                    </span>
                                    <div>
                                        <?= htmlspecialchars($displayName) ?>
                                        <?php if ($isCurrent): ?>
                                            <span class="badge bg-primary ms-1">You</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= (int)($rb['min_leg_count'] ?? 0) ?> legs</td>
                            <td>₹<?= number_format((float)($rb['min_qualifying_volume'] ?? 0)) ?></td>
                            <td><strong><?= number_format((float)($rb['direct_sale_pct'] ?? 0), 1) ?>%</strong></td>
                            <td><?= number_format((float)($rb['l1_pct'] ?? 0), 1) ?>%</td>
                            <td><small class="text-muted"><?= htmlspecialchars($perkText) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- COMMISSION FLOW DIAGRAM -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-project-diagram text-info me-2"></i>Commission Flow — How Payment Travels</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center gap-2 flex-wrap justify-content-center" style="font-size:.9rem;">
                <span class="badge bg-success p-2 px-3"><i class="fas fa-user me-1"></i>Customer Pays</span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-primary p-2 px-3"><i class="fas fa-credit-card me-1"></i>Payment Recorded</span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-warning text-dark p-2 px-3"><i class="fas fa-calculator me-1"></i>Commission Calculated</span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-info p-2 px-3"><i class="fas fa-users me-1"></i>Distributed to Network</span>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3">
                    <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold">Track A</h6>
                    <small class="text-muted">Direct + Upline Commission<br>5-20% per rank</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3">
                    <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                    <h6 class="fw-bold">Track B</h6>
                    <small class="text-muted">Performance Bonus<br>0.9-1.5% bonus</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3">
                    <i class="fas fa-piggy-bank fa-2x text-warning mb-2"></i>
                    <h6 class="fw-bold">Track C</h6>
                    <small class="text-muted">Milestone Escrow<br>2% saved for you</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3">
                    <i class="fas fa-crown fa-2x text-danger mb-2"></i>
                    <h6 class="fw-bold">Track D</h6>
                    <small class="text-muted">Royalty Pool<br>2% shared (VP+)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQs -->
<div class="mb-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-question-circle text-info me-2"></i>Frequently Asked Questions</h4>
    <div class="faqs">
        <div class="accordion" id="mlmFaqs">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">How do I register as an Associate?</button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body">Click on "Register as Associate" or ask your existing associate for their referral link. Complete the registration form with your basic details. You'll receive your unique referral link immediately after registration.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">When do I get paid?</button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body">Commissions are calculated automatically when a payment is received for a plot booking. Payouts are processed in monthly batches. You can track all your earnings in the Commissions section of your dashboard.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Is there a cap on earnings?</button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body">Yes, the total commission per sale is capped at 20% of the sale value. This cap ensures sustainability while still providing excellent earning potential. The 2% Royalty Pool for VP+ ranks is outside this cap.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Can I lose my rank?</button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body">No — once you achieve a rank, you keep it. Ranks only go up, never down. Monthly evaluations check if you qualify for the next rank based on team size and qualifying volume.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">What is the difference between direct commission and differential commission?</button>
                </h2>
                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body"><strong>Direct commission</strong> is what the agent who makes the sale earns (5-20% based on rank). <strong>Differential commission</strong> is what upline members earn — the difference between their rank's rate and the direct agent's rate. For example, if you're a BDM (10%) and your downline Associate (5%) makes a sale, you earn the 5% difference. The total never exceeds your rank's rate.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">What if a booking is cancelled?</button>
                </h2>
                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body">If a booking is cancelled, all commissions paid against that booking are clawed back (reversed). This keeps the system fair and prevents earning on cancelled transactions.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- REFERRAL LINK (logged-in only) -->
<?php if (!empty($_SESSION['user_id'])): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-link text-primary me-2"></i>Your Referral Link</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Share this link with potential associates to grow your team:</p>
        <div class="input-group">
            <input type="text" class="form-control" id="refLink" value="<?= htmlspecialchars($base) ?>/associate/register?ref=<?= urlencode($_SESSION['referral_code'] ?? '') ?>" readonly>
            <button class="btn btn-primary" onclick="copyRefLink()"><i class="fas fa-copy me-1"></i> Copy</button>
        </div>
        <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle"></i> When someone registers using this link, they join your network as a direct referral.</small>
    </div>
</div>
<?php endif; ?>

<script>
function copyRefLink() {
    var inp = document.getElementById('refLink');
    inp.select(); inp.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(inp.value).then(function() {
        alert('Referral link copied!');
    }).catch(function() { document.execCommand('copy'); alert('Copied!'); });
}
</script>
