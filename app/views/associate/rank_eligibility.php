<?php
$page_title = $page_title ?? __('assoc_rank_title', [], 'My Rank & Eligibility');
$current_page = 'rank-eligibility';

$currentRank = $current_rank ?? 'associate';
$rankData = $rank_data ?? [];
$nextRank = $next_rank ?? null;
$progress = $progress ?? [];
$allRanks = $all_ranks ?? [];
$teamSize = $team_size ?? 0;
$directLegs = $direct_legs ?? 0;
$monthlyVolume = $monthly_volume ?? 0;
$lifetimeVolume = $lifetime_volume ?? 0;

$rankConfig = [
    'associate' => ['color' => '#6b7280', 'icon' => 'fas fa-user', 'reward' => __('assoc_rank_rwd_assoc', [], 'Mobile')],
    'senior_associate' => ['color' => '#3b82f6', 'icon' => 'fas fa-user-shield', 'reward' => __('assoc_rank_rwd_sr_assoc', [], 'Tablet')],
    'bdm' => ['color' => '#14b8a6', 'icon' => 'fas fa-user-tie', 'reward' => __('assoc_rank_rwd_bdm', [], 'Laptop')],
    'sr_bdm' => ['color' => '#ec4899', 'icon' => 'fas fa-star', 'reward' => __('assoc_rank_rwd_sr_bdm', [], 'Tour Package')],
    'vice_president' => ['color' => '#f59e0b', 'icon' => 'fas fa-crown', 'reward' => __('assoc_rank_rwd_vp', [], 'Bike')],
    'president' => ['color' => '#10b981', 'icon' => 'fas fa-gem', 'reward' => __('assoc_rank_rwd_president', [], 'Royal Enfield Bullet')],
    'site_manager' => ['color' => '#ef4444', 'icon' => 'fas fa-trophy', 'reward' => __('assoc_rank_rwd_sm', [], 'Car')],
];

$rankDisplay = [
    'associate' => __('assoc_rank_associate', [], 'Associate'),
    'senior_associate' => __('assoc_mlm_sr_associate', [], 'Senior Associate'),
    'bdm' => __('assoc_rank_bdm', [], 'BDM'),
    'sr_bdm' => __('assoc_mlm_sr_bdm', [], 'Sr. BDM'),
    'vice_president' => __('assoc_rank_vp', [], 'Vice President'),
    'president' => __('assoc_rank_president', [], 'President'),
    'site_manager' => __('assoc_rank_site_manager', [], 'Site Manager'),
];

$currentConfig = $rankConfig[$currentRank] ?? $rankConfig['associate'];
$nextConfig = $nextRank ? ($rankConfig[$nextRank] ?? null) : null;
?>

<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, <?= $currentConfig['color'] ?> 0%, <?= $currentConfig['color'] ?>dd 100%); color: #fff;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 70px; height: 70px; background: rgba(255,255,255,0.2);">
                        <i class="<?= $currentConfig['icon'] ?> fa-2x"></i>
                    </div>
                    <div>
                        <h2 class="mb-0"><?= $rankDisplay[$currentRank] ?? __('assoc_rank_associate', [], 'Associate') ?></h2>
                        <p class="mb-0 opacity-75"><?= __('assoc_rank_current_rank', [], 'Your Current Rank') ?></p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.15);">
                            <div class="small opacity-75"><?= __('assoc_rank_monthly_vol', [], 'Monthly Volume') ?></div>
                            <div class="fw-bold">₹<?= number_format($monthlyVolume) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.15);">
                            <div class="small opacity-75"><?= __('assoc_rank_lifetime_vol', [], 'Lifetime Volume') ?></div>
                            <div class="fw-bold">₹<?= number_format($lifetimeVolume) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.15);">
                            <div class="small opacity-75"><?= __('assoc_mlm_team_size', [], 'Team Size') ?></div>
                            <div class="fw-bold"><?= $teamSize ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.15);">
                            <div class="small opacity-75"><?= __('assoc_rank_direct_legs', [], 'Direct Legs') ?></div>
                            <div class="fw-bold"><?= $directLegs ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="p-3 rounded" style="background: rgba(255,255,255,0.2);">
                    <i class="fas fa-gift fa-2x mb-2"></i>
                    <h5 class="mb-0"><?= $currentConfig['reward'] ?></h5>
                    <small class="opacity-75"><?= __('assoc_rank_reward', [], 'Your Rank Reward') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($nextRank && $nextConfig): ?>
<?php
    $nextRankInfo = null;
    foreach ($allRanks as $ar) {
        if (strtolower($ar['rank_name']) === $nextRank) {
            $nextRankInfo = $ar;
            break;
        }
    }
    $targetVolume = (float)($nextRankInfo['min_qualifying_volume'] ?? 0);
    $targetLegs = (int)($nextRankInfo['min_leg_count'] ?? 0);
    $volumePct = $targetVolume > 0 ? min(100, round(($lifetimeVolume / $targetVolume) * 100)) : 0;
    $legsPct = $targetLegs > 0 ? min(100, round(($directLegs / $targetLegs) * 100)) : 0;
    $overallPct = round(($volumePct + $legsPct) / 2);
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-arrow-up me-2 text-success"></i><?= __('assoc_rank_progress_to', [], 'Progress to') ?> <?= $rankDisplay[$nextRank] ?? $nextRank ?></h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold"><?= __('assoc_rank_biz_volume', [], 'Business Volume') ?></span>
                    <span>₹<?= number_format($lifetimeVolume) ?> / ₹<?= number_format($targetVolume) ?></span>
                </div>
                <div class="progress" style="height: 20px; border-radius: 10px;">
                    <div class="progress-bar <?= $volumePct >= 100 ? 'bg-success' : '' ?>" role="progressbar" 
                         style="width: <?= $volumePct ?>%; <?= $volumePct < 100 ? 'background: linear-gradient(90deg, #0d9488, #0f766e);' : '' ?> border-radius: 10px;">
                        <?= $volumePct ?>%
                    </div>
                </div>
                <?php if ($volumePct < 100): ?>
                    <small class="text-muted"><?= __('assoc_rank_more_needed', [], '₹%s more needed') ?></small>
                <?php else: ?>
                    <small class="text-success"><i class="fas fa-check-circle"></i> <?= __('assoc_rank_target_achieved', [], 'Target achieved!') ?></small>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold"><?= __('assoc_rank_direct_legs_title', [], 'Direct Legs (Team)') ?></span>
                    <span><?= $directLegs ?> / <?= $targetLegs ?> <?= __('assoc_rank_required', [], 'required') ?></span>
                </div>
                <div class="progress" style="height: 20px; border-radius: 10px;">
                    <div class="progress-bar <?= $legsPct >= 100 ? 'bg-success' : '' ?>" role="progressbar" 
                         style="width: <?= $legsPct ?>%; <?= $legsPct < 100 ? 'background: linear-gradient(90deg, #f59e0b, #f97316);' : '' ?> border-radius: 10px;">
                        <?= $legsPct ?>%
                    </div>
                </div>
                <?php if ($legsPct < 100): ?>
                    <small class="text-muted"><?= $targetLegs - $directLegs ?> <?= __('assoc_rank_more_members', [], 'more team members needed') ?></small>
                <?php else: ?>
                    <small class="text-success"><i class="fas fa-check-circle"></i> <?= __('assoc_rank_team_met', [], 'Team requirement met!') ?></small>
                <?php endif; ?>
            </div>
        </div>
        
        <?php $bgColor = $overallPct >= 100 ? '#dcfce7' : '#fef3c7'; ?>
        <div class="mt-4 p-3 rounded-3 text-center" style="background: <?php echo $bgColor; ?>;">
            <?php if ($overallPct >= 100): ?>
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="text-success mb-1"><?= __('assoc_rank_eligible', [], "You're Eligible for Promotion!") ?></h5>
                <p class="text-muted mb-0"><?= __('assoc_rank_eligible_desc', [], 'Both volume and team requirements are met. Promotion will be evaluated at month end.') ?></p>
            <?php else: ?>
                <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                <h5 class="text-warning mb-1"><?php echo $overallPct; ?>% <?= __('assoc_rank_complete', [], 'Complete') ?></h5>
                <p class="text-muted mb-0"><?= __('assoc_rank_keep_working', [], 'Keep working! You need') ?> <?php echo ($volumePct < 100) ? __('assoc_rank_more_volume', [], 'more business volume') : __('assoc_rank_more_team', [], 'more team members'); ?> <?= __('assoc_rank_to_qualify', [], 'to qualify.') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-layer-group me-2 text-primary"></i><?= __('assoc_rank_all_ranks', [], 'All Ranks & Rewards') ?></h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th><?= __('assoc_mlm_rank', [], 'Rank') ?></th>
                        <th><?= __('assoc_rank_biz_target', [], 'Business Target') ?></th>
                        <th><?= __('assoc_rank_team_required', [], 'Team Required') ?></th>
                        <th><?= __('assoc_rank_comm_rate', [], 'Commission Rate') ?></th>
                        <th><?= __('assoc_rank_reward_label', [], 'Reward') ?></th>
                        <th><?= __('assoc_book_th_status', [], 'Status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allRanks as $rank): ?>
                        <?php
                        $rankName = strtolower($rank['rank_name']);
                        $isCurrent = ($rankName === $currentRank);
                        $isAchieved = ($lifetimeVolume >= $rank['min_qualifying_volume']);
                        $config = $rankConfig[$rankName] ?? $rankConfig['associate'];
                        ?>
                        <tr class="<?= $isCurrent ? 'table-primary' : ($isAchieved ? 'table-success' : '') ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 35px; height: 35px; background: <?= $config['color'] ?>; color: #fff;">
                                        <i class="<?= $config['icon'] ?>"></i>
                                    </div>
                                    <strong><?= $rankDisplay[$rankName] ?? $rank['rank_name'] ?></strong>
                                </div>
                            </td>
                            <td>₹<?= number_format($rank['min_qualifying_volume']) ?></td>
                            <td><?= $rank['min_leg_count'] ?> <?= __('assoc_rank_members', [], 'members') ?></td>
                            <td><?= $rank['direct_sale_pct'] ?>%</td>
                            <td><span class="badge bg-light text-dark"><?= $config['reward'] ?></span></td>
                            <td>
                                <?php if ($isCurrent): ?>
                                    <span class="badge bg-primary"><i class="fas fa-user me-1"></i><?= __('assoc_rank_current', [], 'Current') ?></span>
                                <?php elseif ($isAchieved): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i><?= __('assoc_rank_achieved', [], 'Achieved') ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= __('assoc_rank_locked', [], 'Locked') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i><?= __('assoc_rank_terms', [], 'Terms & Conditions') ?></h5>
    </div>
    <div class="card-body">
        <div class="accordion" id="termsAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#term1">
                        <i class="fas fa-chart-line me-2 text-primary"></i><?= __('assoc_rank_term1_q', [], 'How is rank calculated?') ?>
                    </button>
                </h2>
                <div id="term1" class="accordion-collapse collapse show" data-bs-parent="#termsAccordion">
                    <div class="accordion-body"><?= __('assoc_rank_term1_a', [], 'Rank is calculated based on <strong>lifetime business volume</strong> (total plot sales value) and <strong>team size</strong> (number of direct legs). Both criteria must be met for promotion. Evaluation happens at month end.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#term2">
                        <i class="fas fa-gift me-2 text-primary"></i><?= __('assoc_rank_term2_q', [], 'When do I get my rank reward?') ?>
                    </button>
                </h2>
                <div id="term2" class="accordion-collapse collapse" data-bs-parent="#termsAccordion">
                    <div class="accordion-body"><?= __('assoc_rank_term2_a', [], 'Rank rewards are delivered within <strong>30 days</strong> of achieving and maintaining the rank for <strong>3 consecutive months</strong>. Rewards are non-transferable and subject to tax deduction at source.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#term3">
                        <i class="fas fa-users me-2 text-primary"></i><?= __('assoc_rank_term3_q', [], 'What counts as a "direct leg"?') ?>
                    </button>
                </h2>
                <div id="term3" class="accordion-collapse collapse" data-bs-parent="#termsAccordion">
                    <div class="accordion-body"><?= __('assoc_rank_term3_a', [], 'A direct leg is an associate who has registered using your referral code and has made at least <strong>one sale</strong>. Inactive associates (no sales in 3 months) are not counted toward team requirements.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#term4">
                        <i class="fas fa-arrow-down me-2 text-danger"></i><?= __('assoc_rank_term4_q', [], 'Can I lose my rank?') ?>
                    </button>
                </h2>
                <div id="term4" class="accordion-collapse collapse" data-bs-parent="#termsAccordion">
                    <div class="accordion-body"><?= __('assoc_rank_term4_a', [], 'Yes. If your <strong>monthly business volume</strong> drops below the minimum threshold for <strong>2 consecutive months</strong>, your rank will be reviewed. You will be given a 1-month grace period to recover.') ?></div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#term5">
                        <i class="fas fa-calculator me-2 text-primary"></i><?= __('assoc_rank_term5_q', [], 'How is business volume calculated?') ?>
                    </button>
                </h2>
                <div id="term5" class="accordion-collapse collapse" data-bs-parent="#termsAccordion">
                    <div class="accordion-body"><?= __('assoc_rank_term5_a', [], 'Business volume includes the <strong>total plot value</strong> of all bookings made by you and your team. Only confirmed/completed bookings count. Cancelled or refunded bookings are excluded.') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
