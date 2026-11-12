<?php
$page_title = $page_title ?? __('assoc_mlm_title', [], 'MLM Plan & Commission Structure');
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
    'associate' => __('assoc_rank_associate', [], 'Associate'),
    'senior_associate' => __('assoc_mlm_sr_associate', [], 'Senior Associate'),
    'bdm' => __('assoc_rank_bdm', [], 'BDM'),
    'sr_bdm' => __('assoc_mlm_sr_bdm', [], 'Sr. BDM'),
    'vice_president' => __('assoc_rank_vp', [], 'Vice President'),
    'president' => __('assoc_rank_president', [], 'President'),
    'site_manager' => __('assoc_rank_site_manager', [], 'Site Manager'),
];

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
.rank-card.current-rank::after { content: '<?= __('assoc_mlm_you_are_here', [], 'YOU ARE HERE') ?>'; position: absolute; top: 12px; right: -30px; background: #0d9488; color: #fff; font-size: .65rem; font-weight: 700; padding: 3px 35px; transform: rotate(45deg); }
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

<div class="plan-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1><i class="fas fa-sitemap me-2"></i><?= __('assoc_mlm_hero_title', [], 'APS Dream Home MLM Plan') ?></h1>
            <p><?= __('assoc_mlm_hero_desc', [], 'Build your network, earn commissions, and grow with us. Our transparent 7-rank system rewards performance at every level.') ?></p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <?php if ($current_plan): ?>
                <span class="badge bg-white text-primary fs-6 px-3 py-2"><i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($current_plan['plan_name'] ?? __('assoc_mlm_active_plan', [], 'Active Plan')) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($user_profile): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1"><?= __('assoc_mlm_your_rank', [], 'Your Rank') ?></div>
                <h4 class="text-primary mb-0"><?= htmlspecialchars($current_rank ?? '') ?></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1"><?= __('assoc_mlm_team_size', [], 'Team Size') ?></div>
                <h4 class="mb-0"><?= number_format($user_team_size) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1"><?= __('assoc_mlm_direct_refs', [], 'Direct Referrals') ?></div>
                <h4 class="mb-0"><?= (int)($user_profile['direct_referrals'] ?? 0) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="text-muted small mb-1"><?= __('assoc_mlm_total_earned', [], 'Total Earned') ?></div>
                <h4 class="mb-0">₹<?= number_format($user_commission_total) ?></h4>
            </div>
        </div>
    </div>
</div>

<?php if ($next_rank): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold"><?= __('assoc_mlm_progress_to', [], 'Progress to') ?> <strong><?= htmlspecialchars($next_rank['level_name'] ?? $next_rank['rank_name'] ?? '') ?></strong></span>
            <span class="text-muted small">₹<?= number_format((float)($user_profile['lifetime_sales'] ?? 0)) ?> / ₹<?= number_format((float)($next_rank['team_size_required'] ?? $next_rank['min_qualifying_volume'] ?? 0)) ?></span>
        </div>
        <div class="progress-track">
            <?php $pct = min(100, ((float)($user_profile['lifetime_sales'] ?? 0) / max(1, (float)($next_rank['team_size_required'] ?? $next_rank['min_qualifying_volume'] ?? 1))) * 100); ?>
            <div class="progress-fill"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted"><?= htmlspecialchars($current_rank ?? '') ?></small>
            <small class="text-muted"><?= htmlspecialchars($next_rank['level_name'] ?? $next_rank['rank_name'] ?? '') ?></small>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="mb-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i><?= __('assoc_mlm_how_it_works', [], 'How It Works — 3 Simple Steps') ?></h4>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="track-box text-center">
                <div class="step-circle mx-auto mb-3">1</div>
                <h5><?= __('assoc_mlm_step1_title', [], 'Register & Get Your Link') ?></h5>
                <p class="text-muted mb-0"><?= __('assoc_mlm_step1_desc', [], 'Sign up as an Associate and get your unique referral link. Share it with anyone interested in real estate.') ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="track-box text-center">
                <div class="step-circle mx-auto mb-3">2</div>
                <h5><?= __('assoc_mlm_step2_title', [], 'Build Your Network') ?></h5>
                <p class="text-muted mb-0"><?= __('assoc_mlm_step2_desc', [], 'When someone registers through your link, they join your network. Each person who joins adds to your team.') ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="track-box text-center">
                <div class="step-circle mx-auto mb-3">3</div>
                <h5><?= __('assoc_mlm_step3_title', [], 'Earn Commissions') ?></h5>
                <p class="text-muted mb-0"><?= __('assoc_mlm_step3_desc', [], 'Every time your network sells a plot, you earn a commission based on your rank. Higher rank = higher earnings!') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-coins text-success me-2"></i><?= __('assoc_mlm_4_ways', [], '4 Ways to Earn') ?></h4>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="track-box">
                <h5><i class="fas fa-handshake text-primary me-2"></i><?= __('assoc_mlm_track_a_title', [], 'Track A — Direct & Upline Commission') ?></h5>
                <p class="text-muted small"><?= __('assoc_mlm_track_a_desc', [], 'Earn on every plot sale in your network. Commission flows from the agent who made the sale, up through their upline as differential commission.') ?></p>
                <div class="bg-white rounded-3 p-3 mb-2">
                    <?php
                    $exampleSale = 1500000;
                    $associateRate = $rankRates['associate'] ?? 5;
                    $srAssocRate = $rankRates['senior_associate'] ?? 7;
                    $bdmRate = $rankRates['bdm'] ?? 10;
                    $srBdmRate = $rankRates['sr_bdm'] ?? 12;
                    $vpRate = $rankRates['vice_president'] ?? 15;
                    $presidentRate = $rankRates['president'] ?? 18;
                    $siteManagerRate = $rankRates['site_manager'] ?? 20;

                    $agentComm = (int)($exampleSale * $associateRate / 100);
                    $upline1 = (int)($exampleSale * max(0, $srAssocRate - $associateRate) / 100);
                    $upline2 = (int)($exampleSale * max(0, $bdmRate - $srAssocRate) / 100);
                    $upline3 = (int)($exampleSale * max(0, $srBdmRate - $bdmRate) / 100);
                    $total = $agentComm + $upline1 + $upline2 + $upline3;
                    $cap = (int)($exampleSale * 0.20);
                    ?>
                    <strong class="small text-muted"><?= __('assoc_mlm_example', [], 'EXAMPLE: Associate sells plot for ₹15,00,000') ?></strong>
                    <table class="table table-sm mt-2 mb-0">
                        <tr><td><?= __('assoc_mlm_ex_agent', [], 'Agent (Associate') ?> — <?= $associateRate ?>%)</td><td class="text-end fw-bold">₹<?= number_format($agentComm) ?></td></tr>
                        <?php if ($upline1 > 0): ?>
                        <tr><td><?= __('assoc_mlm_ex_upline1', [], 'Upline L1 (Sr. Associate') ?> — <?= $srAssocRate ?>% âˆ' <?= $associateRate ?>% = <?= $srAssocRate - $associateRate ?>%)</td><td class="text-end fw-bold">₹<?= number_format($upline1) ?></td></tr>
                        <?php endif; ?>
                        <?php if ($upline2 > 0): ?>
                        <tr><td><?= __('assoc_mlm_ex_upline2', [], 'Upline L2 (BDM') ?> — <?= $bdmRate ?>% âˆ' <?= $srAssocRate ?>% = <?= $bdmRate - $srAssocRate ?>%)</td><td class="text-end fw-bold">₹<?= number_format($upline2) ?></td></tr>
                        <?php endif; ?>
                        <?php if ($upline3 > 0): ?>
                        <tr><td><?= __('assoc_mlm_ex_upline3', [], 'Upline L3 (Sr. BDM') ?> — <?= $srBdmRate ?>% âˆ' <?= $bdmRate ?>% = <?= $srBdmRate - $bdmRate ?>%)</td><td class="text-end fw-bold">₹<?= number_format($upline3) ?></td></tr>
                        <?php endif; ?>
                        <tr class="table-primary"><td><strong><?= __('assoc_mlm_total_capped', [], 'Total Commission (capped at 20%)') ?></strong></td><td class="text-end fw-bold">₹<?= number_format($total) ?></td></tr>
                    </table>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i><?= __('assoc_mlm_track_a_note', [], 'Upline earns the <strong>difference</strong> between their rank rate and the downline\'s rate. Total per sale is capped at 20%.') ?></small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="track-box">
                <h5><i class="fas fa-chart-line text-success me-2"></i><?= __('assoc_mlm_track_b_title', [], 'Track B — Performance Bonus') ?></h5>
                <p class="text-muted small"><?= __('assoc_mlm_track_b_desc', [], 'Earn extra when your team hits consistent monthly sales targets. Consecutive qualifying months unlock bonus multipliers.') ?></p>
                <div class="bg-white rounded-3 p-3 mb-2">
                    <strong class="small text-muted"><?= __('assoc_mlm_bonus_structure', [], 'BONUS STRUCTURE:') ?></strong>
                    <table class="table table-sm mt-2 mb-0">
                        <tr><td><?= __('assoc_mlm_bonus_3m', [], '3 consecutive qualifying months') ?></td><td class="text-end fw-bold">+0.9% <?= __('assoc_mlm_bonus', [], 'bonus') ?></td></tr>
                        <tr><td><?= __('assoc_mlm_bonus_6m', [], '6 consecutive qualifying months') ?></td><td class="text-end fw-bold">+1.2% <?= __('assoc_mlm_bonus', [], 'bonus') ?></td></tr>
                        <tr><td><?= __('assoc_mlm_bonus_12m', [], '12 consecutive qualifying months') ?></td><td class="text-end fw-bold">+1.5% <?= __('assoc_mlm_bonus', [], 'bonus') ?></td></tr>
                    </table>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i><?= __('assoc_mlm_track_b_note', [], 'A "qualifying month" means your team generated at least ₹50,000 in sales that month.') ?></small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="track-box">
                <h5><i class="fas fa-trophy text-warning me-2"></i><?= __('assoc_mlm_track_c_title', [], 'Track C — Milestone Escrow') ?></h5>
                <p class="text-muted small"><?= __('assoc_mlm_track_c_desc', [], '2% of every sale goes into a personal escrow. When it reaches your milestone threshold, you receive a bonus payout.') ?></p>
                <div class="bg-white rounded-3 p-3 mb-2">
                    <strong class="small text-muted"><?= __('assoc_mlm_milestone_tiers', [], 'MILESTONE TIERS:') ?></strong>
                    <table class="table table-sm mt-2 mb-0">
                        <tr><td>₹50,000 <?= __('assoc_mlm_accumulated', [], 'accumulated') ?></td><td class="text-end fw-bold"><?= __('assoc_mlm_bronze', [], 'Bronze Milestone') ?></td></tr>
                        <tr><td>₹2,00,000 <?= __('assoc_mlm_accumulated', [], 'accumulated') ?></td><td class="text-end fw-bold"><?= __('assoc_mlm_silver', [], 'Silver Milestone') ?></td></tr>
                        <tr><td>₹5,00,000 <?= __('assoc_mlm_accumulated', [], 'accumulated') ?></td><td class="text-end fw-bold"><?= __('assoc_mlm_gold', [], 'Gold Milestone') ?></td></tr>
                        <tr><td>₹15,00,000 <?= __('assoc_mlm_accumulated', [], 'accumulated') ?></td><td class="text-end fw-bold"><?= __('assoc_mlm_diamond', [], 'Diamond Milestone') ?></td></tr>
                    </table>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i><?= __('assoc_mlm_track_c_note', [], 'Escrow is separate from Track A. This is your personal reserve — not shared with upline.') ?></small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="track-box">
                <h5><i class="fas fa-crown text-danger me-2"></i><?= __('assoc_mlm_track_d_title', [], 'Track D — Royalty Pool (Senior Leaders)') ?></h5>
                <p class="text-muted small"><?= __('assoc_mlm_track_d_desc', [], 'Vice President and above share 2% of all company sales, distributed proportional to lifetime sales volume.') ?></p>
                <div class="bg-white rounded-3 p-3 mb-2">
                    <strong class="small text-muted"><?= __('assoc_mlm_eligibility', [], 'ELIGIBILITY:') ?></strong>
                    <table class="table table-sm mt-2 mb-0">
                        <tr><td><?= __('assoc_rank_vp', [], 'Vice President') ?> (GBV ≥ ₹1.5 Cr)</td><td class="text-end fw-bold"><?= __('assoc_mlm_eligible', [], 'Eligible') ?></td></tr>
                        <tr><td><?= __('assoc_rank_president', [], 'President') ?> (GBV ≥ ₹3 Cr)</td><td class="text-end fw-bold"><?= __('assoc_mlm_eligible', [], 'Eligible') ?></td></tr>
                        <tr><td><?= __('assoc_rank_site_manager', [], 'Site Manager') ?> (GBV ≥ ₹5 Cr)</td><td class="text-end fw-bold"><?= __('assoc_mlm_eligible', [], 'Eligible') ?></td></tr>
                    </table>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i><?= __('assoc_mlm_track_d_note', [], 'Distributed monthly. This is additional income on top of Tracks A/B/C.') ?></small>
            </div>
        </div>
    </div>
</div>

<!-- PERMANENT LEADERSHIP CAREER SALARY INCOME PLAN -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #d97706 !important;">
    <div class="card-header bg-transparent border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <span class="badge bg-primary text-white px-2 py-1 mb-1"><i class="fas fa-award me-1"></i>PERMANENT LEADERSHIP PLAN</span>
            <h5 class="card-title mb-0 fw-bold text-dark"><i class="fas fa-calendar-check text-warning me-2"></i>Permanent Career Salary Plan (परमानेंट लीडरशिप करियर सैलरी प्लान)</h5>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 fs-6 fw-bold">Active Monthly Maintenance Gated</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 bg-white">
                <thead class="table-warning text-dark">
                    <tr class="text-center">
                        <th>Business Target</th>
                        <th>Time Duration</th>
                        <th>Monthly Salary</th>
                        <th>Duration</th>
                        <th>Total Salary Benefit</th>
                    </tr>
                </thead>
                <tbody class="text-center align-middle">
                    <tr>
                        <td><strong class="text-primary fs-6">₹15,00,000</strong> (15 Lakhs)</td>
                        <td><span class="badge bg-secondary">60 Days</span></td>
                        <td><strong class="text-success fs-6">₹5,000</strong> / Month</td>
                        <td>6 Months</td>
                        <td><span class="badge bg-success fs-6">₹30,000</span></td>
                    </tr>
                    <tr>
                        <td><strong class="text-primary fs-6">₹30,00,000</strong> (30 Lakhs)</td>
                        <td><span class="badge bg-secondary">100 Days</span></td>
                        <td><strong class="text-success fs-6">₹5,000</strong> / Month</td>
                        <td>12 Months (1 Year)</td>
                        <td><span class="badge bg-success fs-6">₹60,000</span></td>
                    </tr>
                    <tr>
                        <td><strong class="text-primary fs-6">₹50,00,000</strong> (50 Lakhs)</td>
                        <td><span class="badge bg-secondary">150 Days</span></td>
                        <td><strong class="text-success fs-6">₹8,000</strong> / Month</td>
                        <td>12 Months (1 Year)</td>
                        <td><span class="badge bg-success fs-6">₹96,000</span></td>
                    </tr>
                    <tr>
                        <td><strong class="text-primary fs-6">₹75,00,000</strong> (75 Lakhs)</td>
                        <td><span class="badge bg-secondary">200 Days</span></td>
                        <td><strong class="text-success fs-6">₹12,000</strong> / Month</td>
                        <td>12 Months (1 Year)</td>
                        <td><span class="badge bg-success fs-6">₹1,44,000</span></td>
                    </tr>
                    <tr class="table-light">
                        <td><strong class="text-danger fs-6">₹1,00,00,000</strong> (1 Crore)</td>
                        <td><span class="badge bg-dark">300 Days</span></td>
                        <td><strong class="text-danger fs-5">₹20,000</strong> / Month</td>
                        <td>12 Months (1 Year)</td>
                        <td><span class="badge bg-danger fs-6">₹2,40,000</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="p-3 bg-white border-top text-muted small">
            <i class="fas fa-info-circle text-primary me-1"></i>
            यह सैलरी प्लान एसोसिएट्स के नियमित प्लॉट कमीशन के <strong>अतिरिक्त (Extra Incentive)</strong> है। जब आप तय समय सीमा में अपना बिजनेस टारगेट पूरा करते हैं, तो आपकी मासिक सैलरी सीधे आपके बैंक खाते / ई-वॉलेट में क्रेडिट की जाती है।
        </div>
        <!-- S457: PERFORMANCE ACCELERATION & MAINTENANCE RULES -->
        <div class="p-3 bg-light border-top">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-2 bg-white rounded border h-100">
                        <h6 class="fw-bold text-dark small mb-1"><i class="fas fa-running text-success me-1"></i>मासिक सक्रियता शर्त (Active Maintenance)</h6>
                        <p class="text-muted small mb-0" style="font-size: 11.5px;">
                            सैलरी रिलीज के लिए एसोसिएट को उस माह में न्यूनतम <strong>2 सत्यापित साइट विजिट</strong> या <strong>₹50,000 की नई बुकिंग</strong> दिखाना अनिवार्य है। ड्राई मंथ होने पर सैलरी लैप्स नहीं होती, बल्कि होल्ड रहती है और अगले माह एक्टिव होते ही रिलीज हो जाती है।
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2 bg-white rounded border h-100">
                        <h6 class="fw-bold text-dark small mb-1"><i class="fas fa-balance-scale text-primary me-1"></i>40:60 लेग बैलेंस नियम (Leg Balance Rule)</h6>
                        <p class="text-muted small mb-0" style="font-size: 11.5px;">
                            सैलरी और रैंक टारगेट में किसी भी एकल मजबूत लेग से अधिकतम <strong>60% वॉल्यूम</strong> मान्य होगा। शेष <strong>40% बिजनेस</strong> अन्य लेग्स या पर्सनल डायरेक्ट सेल से होना चाहिए, जिससे संतुलित टीम का निर्माण हो।
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2 bg-white rounded border h-100">
                        <h6 class="fw-bold text-dark small mb-1"><i class="fas fa-bolt text-warning me-1"></i>30-दिन फास्ट-स्टार्ट बोनस (₹2,100)</h6>
                        <p class="text-muted small mb-0" style="font-size: 11.5px;">
                            नए एसोसिएट के पहले 30 दिनों में <strong>5 डायरेक्ट एसोसिएट जोड़ने</strong> एवं <strong>3 कस्टमर साइट विजिट</strong> कराने पर कंपनी की तरफ से <strong>₹2,100 का फास्ट-ट्रैक किकस्टार्ट बोनस</strong> सीधे वॉलेट में तुरंत मिलता है।
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-layer-group text-primary me-2"></i><?= __('assoc_mlm_rank_ladder', [], 'Rank Ladder — Your Path to the Top') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th><?= __('assoc_mlm_rank', [], 'Rank') ?></th>
                        <th>Downline Legs</th>
                        <th>Business Target</th>
                        <th>Commission %</th>
                        <th>Reward (उपहार)</th>
                        <th>Override %</th>
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
                        $reward = $rb['reward_item'] ?? '—';
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rank-badge me-1">
                                        <i class="fas <?= $rankIcons[$i] ?? 'fa-user' ?>"></i>
                                    </span>
                                    <div>
                                        <?= htmlspecialchars($displayName ?? '') ?>
                                        <?php if ($isCurrent): ?>
                                            <span class="badge bg-primary ms-1"><?= __('assoc_mlm_you', [], 'You') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= (int)($rb['min_leg_count'] ?? 0) ?> <?= (int)($rb['min_leg_count'] ?? 0) === 5 ? 'Associates' : ((int)($rb['min_leg_count'] ?? 0) > 0 ? 'Qualifying Legs' : 'Direct') ?></td>
                            <td>₹<?= number_format((float)($rb['min_qualifying_volume'] ?? 0)) ?></td>
                            <td><strong class="text-primary"><?= number_format((float)($rb['direct_sale_pct'] ?? 0), 1) ?>%</strong></td>
                            <td><span class="badge bg-warning text-dark"><i class="fas fa-gift me-1"></i><?= htmlspecialchars($reward) ?></span></td>
                            <td><?= number_format((float)($rb['l1_pct'] ?? 0), 1) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-project-diagram text-info me-2"></i><?= __('assoc_mlm_commission_flow', [], 'Commission Flow — How Payment Travels') ?></h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center gap-2 flex-wrap justify-content-center">
                <span class="badge bg-success p-2 px-3"><i class="fas fa-user me-1"></i><?= __('assoc_mlm_flow_customer', [], 'Customer Pays') ?></span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-primary p-2 px-3"><i class="fas fa-credit-card me-1"></i><?= __('assoc_mlm_flow_payment', [], 'Payment Recorded') ?></span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-warning text-dark p-2 px-3"><i class="fas fa-calculator me-1"></i><?= __('assoc_mlm_flow_calc', [], 'Commission Calculated') ?></span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-info p-2 px-3"><i class="fas fa-users me-1"></i><?= __('assoc_mlm_flow_distribute', [], 'Distributed to Network') ?></span>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3">
                    <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold"><?= __('assoc_mlm_track_a', [], 'Track A') ?></h6>
                    <small class="text-muted"><?= __('assoc_mlm_flow_a', [], 'Direct + Upline Commission<br>5-20% per rank') ?></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3">
                    <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                    <h6 class="fw-bold"><?= __('assoc_mlm_track_b', [], 'Track B') ?></h6>
                    <small class="text-muted"><?= __('assoc_mlm_flow_b', [], 'Performance Bonus<br>0.9-1.5% bonus') ?></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3">
                    <i class="fas fa-piggy-bank fa-2x text-warning mb-2"></i>
                    <h6 class="fw-bold"><?= __('assoc_mlm_track_c', [], 'Track C') ?></h6>
                    <small class="text-muted"><?= __('assoc_mlm_flow_c', [], 'Milestone Escrow<br>2% saved for you') ?></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3">
                    <i class="fas fa-crown fa-2x text-danger mb-2"></i>
                    <h6 class="fw-bold"><?= __('assoc_mlm_track_d', [], 'Track D') ?></h6>
                    <small class="text-muted"><?= __('assoc_mlm_flow_d', [], 'Royalty Pool<br>2% shared (VP+)') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-question-circle text-info me-2"></i><?= __('assoc_mlm_faq_title', [], 'Frequently Asked Questions') ?></h4>
    <div class="faqs">
        <div class="accordion" id="mlmFaqs">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1"><?= __('assoc_mlm_faq1_q', [], 'How do I register as an Associate?') ?></button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body"><?= __('assoc_mlm_faq1_a', [], 'Click on "Register as Associate" or ask your existing associate for their referral link. Complete the registration form with your basic details. You\'ll receive your unique referral link immediately after registration.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2"><?= __('assoc_mlm_faq2_q', [], 'When do I get paid?') ?></button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body"><?= __('assoc_mlm_faq2_a', [], 'Commissions are calculated automatically when a payment is received for a plot booking. Payouts are processed in monthly batches. You can track all your earnings in the Commissions section of your dashboard.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3"><?= __('assoc_mlm_faq3_q', [], 'Is there a cap on earnings?') ?></button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body"><?= __('assoc_mlm_faq3_a', [], 'Yes, the total commission per sale is capped at 20% of the sale value. This cap ensures sustainability while still providing excellent earning potential. The 2% Royalty Pool for VP+ ranks is outside this cap.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4"><?= __('assoc_mlm_faq4_q', [], 'Can I lose my rank?') ?></button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body"><?= __('assoc_mlm_faq4_a', [], 'No — once you achieve a rank, you keep it. Ranks only go up, never down. Monthly evaluations check if you qualify for the next rank based on team size and qualifying volume.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5"><?= __('assoc_mlm_faq5_q', [], 'What is the difference between direct commission and differential commission?') ?></button>
                </h2>
                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body"><?= __('assoc_mlm_faq5_a', [], '<strong>Direct commission</strong> is what the agent who makes the sale earns (5-20% based on rank). <strong>Differential commission</strong> is what upline members earn — the difference between their rank\'s rate and the direct agent\'s rate. For example, if you\'re a BDM (10%) and your downline Associate (5%) makes a sale, you earn the 5% difference. The total never exceeds your rank\'s rate.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6"><?= __('assoc_mlm_faq6_q', [], 'What if a booking is cancelled?') ?></button>
                </h2>
                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#mlmFaqs">
                    <div class="accordion-body"><?= __('assoc_mlm_faq6_a', [], 'If a booking is cancelled, all commissions paid against that booking are clawed back (reversed). This keeps the system fair and prevents earning on cancelled transactions.') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['user_id'])): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-link text-primary me-2"></i><?= __('assoc_mlm_your_ref_link', [], 'Your Referral Link') ?></h5>
    </div>
    <div class="card-body">
        <p class="text-muted"><?= __('assoc_mlm_share_desc', [], 'Share this link with potential associates to grow your team:') ?></p>
        <div class="input-group">
            <input type="text" class="form-control" id="refLink" value="<?= htmlspecialchars($base ?? '') ?>/associate/register?ref=<?= urlencode($_SESSION['referral_code'] ?? '') ?>" readonly>
            <button class="btn btn-primary" onclick="copyRefLink()"><i class="fas fa-copy me-1"></i> <?= __('assoc_mlm_copy', [], 'Copy') ?></button>
        </div>
        <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle"></i> <?= __('assoc_mlm_share_note', [], 'When someone registers using this link, they join your network as a direct referral.') ?></small>
    </div>
</div>
<?php endif; ?>

<script>
function copyRefLink() {
    var inp = document.getElementById('refLink');
    inp.select(); inp.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(inp.value).then(function() {
        alert('<?= __('assoc_mlm_ref_copied', [], 'Referral link copied!') ?>');
    }).catch(function() { document.execCommand('copy'); alert('<?= __('assoc_mlm_ref_copied2', [], 'Copied!') ?>'); });
}
</script>
