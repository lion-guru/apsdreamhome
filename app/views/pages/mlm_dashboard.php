<?php
$page_title = $page_title ?? __('mlm_dash_title', [], 'MLM Dashboard');
$associate_name = $associate_name ?? __('mlm_dash_associate', [], 'Associate');
$associate_id = $associate_id ?? 'N/A';
$referral_code = $referral_code ?? 'APS000000';
$team_size = $team_size ?? 0;
$total_sales = $total_sales ?? 0;
$commission_earned = $commission_earned ?? 0;
$pending_payout = $pending_payout ?? 0;
$downline_members = $downline_members ?? [];
$commission_history = $commission_history ?? [];
$payout_history = $payout_history ?? [];
?>

<!-- Welcome Banner -->
<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #0d6839 0%, #198754 50%, #20c997 100%);">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                    <i class="fas fa-user-tie fa-2x text-white"></i>
                                </div>
                                <div>
                                    <h2 class="text-white mb-1 fw-bold"><?= __('mlm_dash_welcome', ['name' => htmlspecialchars($associate_name)], 'Welcome back, ' . htmlspecialchars($associate_name) . '!') ?></h2>
                                    <span class="badge bg-light text-success fw-semibold px-3 py-2">
                                        <i class="fas fa-id-badge me-1"></i> <?= __('mlm_dash_assoc_id', [], 'Associate ID:') ?> <?= htmlspecialchars($associate_id) ?>
                                    </span>
                                </div>
                            </div>
                            <p class="text-white-50 mb-0"><i class="fas fa-calendar-alt me-1"></i> <?= date('l, F j, Y') ?></p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-inline-block bg-white bg-opacity-10 rounded-3 p-3">
                                <small class="text-white-50 d-block mb-1"><?= __('mlm_dash_your_referral', [], 'Your Referral Code') ?></small>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-white fw-bold fs-4 font-monospace" id="referralCodeDisplay"><?= htmlspecialchars($referral_code) ?></span>
                                    <button class="btn btn-light btn-sm" onclick="copyReferralCode()" title="<?= __('mlm_dash_copy_code', [], 'Copy Code') ?>">
                                        <i class="fas fa-copy" id="copyIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small fw-semibold text-uppercase"><?= __('mlm_dash_team_size', [], 'Team Size') ?></p>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format($team_size) ?></h3>
                            <small class="text-success"><i class="fas fa-users me-1"></i><?= __('mlm_dash_active_members', [], 'Active Members') ?></small>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(25, 135, 84, 0.1);">
                            <i class="fas fa-users text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small fw-semibold text-uppercase"><?= __('mlm_dash_total_sales', [], 'Total Sales') ?></p>
                            <h3 class="fw-bold mb-0 text-dark">&#8377;<?= number_format($total_sales, 2) ?></h3>
                            <small class="text-info"><i class="fas fa-chart-line me-1"></i><?= __('mlm_dash_all_time', [], 'All Time') ?></small>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(13, 202, 240, 0.1);">
                            <i class="fas fa-rupee-sign text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small fw-semibold text-uppercase"><?= __('mlm_dash_commission_earned', [], 'Commission Earned') ?></p>
                            <h3 class="fw-bold mb-0 text-dark">&#8377;<?= number_format($commission_earned, 2) ?></h3>
                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i><?= __('mlm_dash_lifetime', [], 'Lifetime') ?></small>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(25, 135, 84, 0.1);">
                            <i class="fas fa-hand-holding-usd text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small fw-semibold text-uppercase"><?= __('mlm_dash_pending_payout', [], 'Pending Payout') ?></p>
                            <h3 class="fw-bold mb-0 text-dark">&#8377;<?= number_format($pending_payout, 2) ?></h3>
                            <small class="text-warning"><i class="fas fa-clock me-1"></i><?= __('mlm_dash_processing', [], 'Processing') ?></small>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(255, 193, 7, 0.1);">
                            <i class="fas fa-hourglass-half text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Rank progress variables (set by controller)
    $currentRank       = $current_rank       ?? 'associate';
    $currentRankLabel  = $current_rank_label ?? 'Associate';
    $currentRankColor  = $current_rank_color ?? '#94a3b8';
    $currentRankRate   = $current_rank_rate  ?? 5;
    $nextRankLabel     = $next_rank_label    ?? 'Sr. Associate';
    $nextRankColor     = $next_rank_color    ?? '#64748b';
    $nextRankRate      = $next_rank_rate     ?? 7;
    $rankProgressPct   = $rank_progress_pct  ?? 0;
    $amountToNext      = $amount_to_next_rank ?? 0;
    $lifetimeSales     = $lifetime_sales_volume ?? 0;
    $commByType        = $commission_by_type ?? [];
    $totalCommEarned   = $commission_earned  ?? 0;
    $rankBenefits      = $rank_benefits      ?? [];
    ?>

    <!-- ═══════════════════════════════════════════════════════════════
         MY RANK PROGRESS SECTION
    ═══════════════════════════════════════════════════════════════ -->
    <div class="row g-4 mb-4">

        <!-- LEFT: Rank Progress Card -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(145deg,#fff,#f8faff);">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-4"><i class="fas fa-trophy me-2" style="color:<?= htmlspecialchars($currentRankColor) ?>;"></i>My Rank Progress</h6>

                    <!-- Current Rank Badge -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width:60px;height:60px;background:<?= htmlspecialchars($currentRankColor) ?>20;border:2px solid <?= htmlspecialchars($currentRankColor) ?>40;">
                            <i class="fas fa-medal fa-xl" style="color:<?= htmlspecialchars($currentRankColor) ?>;"></i>
                        </div>
                        <div>
                            <div class="badge px-3 py-2 mb-1 fw-bold fs-6" style="background:<?= htmlspecialchars($currentRankColor) ?>;color:#fff;">
                                <?= htmlspecialchars($currentRankLabel) ?>
                            </div>
                            <div class="small text-muted">Commission Rate: <strong class="text-dark"><?= $currentRankRate ?>%</strong> on your plot sales</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <?php if ($amountToNext > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="fw-semibold text-dark">Progress to <span style="color:<?= htmlspecialchars($nextRankColor) ?>;"><?= htmlspecialchars($nextRankLabel) ?></span></small>
                            <span class="badge rounded-pill" style="background:<?= htmlspecialchars($nextRankColor) ?>20;color:<?= htmlspecialchars($nextRankColor) ?>;font-size:.8rem;"><?= $rankProgressPct ?>%</span>
                        </div>
                        <div class="progress" style="height:14px;border-radius:10px;background:#f1f5f9;">
                            <div class="progress-bar" role="progressbar"
                                 style="width:<?= $rankProgressPct ?>%;background:linear-gradient(90deg,<?= htmlspecialchars($currentRankColor) ?>,<?= htmlspecialchars($nextRankColor) ?>);border-radius:10px;transition:width 1.2s ease;"
                                 aria-valuenow="<?= $rankProgressPct ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">Your GBV: <strong class="text-dark">₹<?= number_format($lifetimeSales) ?></strong></small>
                            <small class="text-muted">Need: <strong class="text-danger">₹<?= number_format($amountToNext) ?> more</strong></small>
                        </div>
                    </div>

                    <!-- Next Rank Unlock Preview -->
                    <div class="rounded-3 p-3" style="background:<?= htmlspecialchars($nextRankColor) ?>10;border:1px solid <?= htmlspecialchars($nextRankColor) ?>30;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lock me-2" style="color:<?= htmlspecialchars($nextRankColor) ?>;"></i>
                            <div>
                                <div class="fw-semibold small" style="color:<?= htmlspecialchars($nextRankColor) ?>;">Unlock <?= htmlspecialchars($nextRankLabel) ?></div>
                                <small class="text-muted">Earn <strong><?= $nextRankRate ?>%</strong> on plot sales (+<?= round($nextRankRate - $currentRankRate, 1) ?>% uplift)</small>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success py-2 mb-3">
                        <i class="fas fa-crown me-2"></i>
                        <strong>Congratulations!</strong> You are at the highest rank — <strong>Site Manager (20%)</strong>
                    </div>
                    <?php endif; ?>

                    <!-- Rank Ladder (mini) -->
                    <?php if (!empty($rankBenefits)): ?>
                    <div class="mt-3">
                        <small class="text-muted fw-semibold d-block mb-2">RANK LADDER</small>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($rankBenefits as $rb):
                                $isActive = $rb['rank_name'] === $currentRank;
                                $isPassed = ($rb['rank_order'] ?? 99) < ($rankBenefits[array_search($currentRank, array_column($rankBenefits,'rank_name'))]['rank_order'] ?? 0);
                            ?>
                            <span class="badge px-2 py-1" style="font-size:.7rem;background:<?= $isActive ? htmlspecialchars($rb['color_code'] ?? '#94a3b8') : '#f1f5f9' ?>;color:<?= $isActive ? '#fff' : '#94a3b8' ?>;border:1px solid <?= htmlspecialchars($rb['color_code'] ?? '#e2e8f0') ?>50;">
                                <?= htmlspecialchars($rb['rank_name'] === $currentRank ? '★ ' : '') ?><?= (float)$rb['direct_sale_pct'] ?>%
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Commission Breakdown by Type -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-bar text-success me-2"></i>My Earnings Breakdown</h6>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">Total: ₹<?= number_format($totalCommEarned) ?></span>
                    </div>

                    <?php
                    $typeLabels = [
                        'direct_sale'       => ['label'=>'Direct Sale Commission',    'icon'=>'fa-handshake',        'color'=>'#0d9488'],
                        'override'          => ['label'=>'Upline Override',           'icon'=>'fa-layer-group',      'color'=>'#6366f1'],
                        'matching_bonus'    => ['label'=>'Matching Bonus',            'icon'=>'fa-hands-helping',    'color'=>'#f59e0b'],
                        'generation_bonus'  => ['label'=>'Generation Bonus',          'icon'=>'fa-network-wired',    'color'=>'#0891b2'],
                        'rank_bonus'        => ['label'=>'Rank Promotion Bonus',      'icon'=>'fa-medal',            'color'=>'#dc2626'],
                        'royalty_pool'      => ['label'=>'Royalty Pool',              'icon'=>'fa-crown',            'color'=>'#0f766e'],
                        'infinity_override' => ['label'=>'Infinity Override',         'icon'=>'fa-infinity',         'color'=>'#059669'],
                        'level_bonus'       => ['label'=>'Level Bonus',               'icon'=>'fa-sitemap',          'color'=>'#d97706'],
                        'team_bonus'        => ['label'=>'Team Bonus',                'icon'=>'fa-users',            'color'=>'#2563eb'],
                        'performance_bonus' => ['label'=>'Performance Bonus',         'icon'=>'fa-bolt',             'color'=>'#db2777'],
                        'investment_sale'   => ['label'=>'Investment Sale',           'icon'=>'fa-chart-line',       'color'=>'#10b981'],
                    ];
                    ?>

                    <?php if (!empty($commByType)): ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($commByType as $ct):
                            $typeMeta  = $typeLabels[$ct['commission_type']] ?? ['label'=>ucfirst(str_replace('_',' ',$ct['commission_type'])),'icon'=>'fa-coins','color'=>'#64748b'];
                            $pct       = $totalCommEarned > 0 ? round(($ct['total'] / $totalCommEarned) * 100, 1) : 0;
                        ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:34px;height:34px;background:<?= $typeMeta['color'] ?>18;">
                                <i class="fas <?= $typeMeta['icon'] ?> small" style="color:<?= $typeMeta['color'] ?>;"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-semibold text-dark text-truncate"><?= htmlspecialchars($typeMeta['label']) ?></small>
                                    <small class="fw-bold ms-2 flex-shrink-0" style="color:<?= $typeMeta['color'] ?>;">₹<?= number_format($ct['total']) ?></small>
                                </div>
                                <div class="progress" style="height:5px;border-radius:4px;">
                                    <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $typeMeta['color'] ?>;border-radius:4px;"></div>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0" style="min-width:40px;">
                                <small class="text-muted"><?= $pct ?>%</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-bar fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">No commission earnings yet.<br>Start closing plots to see your breakdown here.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-bolt text-success me-2"></i><?= __('mlm_dash_quick_actions', [], 'Quick Actions') ?></h6>
                    <div class="row g-3">
                        <div class="col-xl-3 col-md-6">
                            <a href="javascript:void(0)" onclick="shareReferralLink()" class="btn w-100 py-3 fw-semibold text-start d-flex align-items-center text-decoration-none" style="background: linear-gradient(135deg, rgba(25,135,84,0.08), rgba(25,135,84,0.03)); border: 1px solid rgba(25,135,84,0.2); border-radius: 12px;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(25,135,84,0.12);">
                                    <i class="fas fa-share-alt text-success"></i>
                                </div>
                                <div>
                                    <span class="d-block text-dark"><?= __('mlm_dash_share_referral', [], 'Share Referral Link') ?></span>
                                    <small class="text-muted"><?= __('mlm_dash_invite_members', [], 'Invite new members') ?></small>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <a href="#downlineSection" class="btn w-100 py-3 fw-semibold text-start d-flex align-items-center text-decoration-none" style="background: linear-gradient(135deg, rgba(13,202,240,0.08), rgba(13,202,240,0.03)); border: 1px solid rgba(13,202,240,0.2); border-radius: 12px;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(13,202,240,0.12);">
                                    <i class="fas fa-sitemap text-info"></i>
                                </div>
                                <div>
                                    <span class="d-block text-dark"><?= __('mlm_dash_view_team', [], 'View Team') ?></span>
                                    <small class="text-muted"><?= __('mlm_dash_see_network', [], 'See your network') ?></small>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <a href="javascript:void(0)" onclick="requestPayout()" class="btn w-100 py-3 fw-semibold text-start d-flex align-items-center text-decoration-none" style="background: linear-gradient(135deg, rgba(255,193,7,0.08), rgba(255,193,7,0.03)); border: 1px solid rgba(255,193,7,0.2); border-radius: 12px;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(255,193,7,0.12);">
                                    <i class="fas fa-wallet text-warning"></i>
                                </div>
                                <div>
                                    <span class="d-block text-dark"><?= __('mlm_dash_request_payout', [], 'Request Payout') ?></span>
                                    <small class="text-muted"><?= __('mlm_dash_withdraw_earnings', [], 'Withdraw earnings') ?></small>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <a href="javascript:void(0)" onclick="downloadStatement()" class="btn w-100 py-3 fw-semibold text-start d-flex align-items-center text-decoration-none" style="background: linear-gradient(135deg, rgba(108,117,125,0.08), rgba(108,117,125,0.03)); border: 1px solid rgba(108,117,125,0.2); border-radius: 12px;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; background: rgba(108,117,125,0.12);">
                                    <i class="fas fa-file-download text-secondary"></i>
                                </div>
                                <div>
                                    <span class="d-block text-dark"><?= __('mlm_dash_download_statement', [], 'Download Statement') ?></span>
                                    <small class="text-muted"><?= __('mlm_dash_export_reports', [], 'Export reports') ?></small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Referral Code Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark"><i class="fas fa-link text-success me-2"></i><?= __('mlm_dash_referral_code', [], 'Referral Code') ?></h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center p-4 rounded-3 mb-3" style="background: linear-gradient(135deg, #0d6839, #198754);">
                        <small class="text-white-50 d-block mb-2"><?= __('mlm_dash_unique_code', [], 'Your Unique Code') ?></small>
                        <h2 class="text-white fw-bold font-monospace mb-0"><?= htmlspecialchars($referral_code) ?></h2>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control font-monospace bg-light border-end-0" id="referralLinkInput" value="https://apsrealty.com/join/<?= htmlspecialchars($referral_code) ?>" readonly>
                        <button class="btn btn-success" type="button" onclick="copyReferralLink()" id="copyLinkBtn">
                            <i class="fas fa-copy me-1"></i> <?= __('mlm_dash_copy_link', [], 'Copy Link') ?>
                        </button>
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-outline-success btn-sm rounded-circle" style="width: 40px; height: 40px;" onclick="shareVia('whatsapp')" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm rounded-circle" style="width: 40px; height: 40px;" onclick="shareVia('telegram')" title="Telegram">
                            <i class="fab fa-telegram-plane"></i>
                        </button>
                        <button class="btn btn-outline-info btn-sm rounded-circle" style="width: 40px; height: 40px;" onclick="shareVia('twitter')" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm rounded-circle" style="width: 40px; height: 40px;" onclick="shareVia('email')" title="Email">
                            <i class="fas fa-envelope"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Network Tree -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark"><i class="fas fa-project-diagram text-success me-2"></i><?= __('mlm_dash_network_tree', [], 'Network Tree') ?></h6>
                </div>
                <div class="card-body p-4" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($downline_members)): ?>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <div class="d-flex align-items-center p-3 rounded-3" style="background: linear-gradient(135deg, #0d6839, #198754);">
                                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px;">
                                        <i class="fas fa-crown text-success"></i>
                                    </div>
                                    <div>
                                        <span class="text-white fw-bold"><?= htmlspecialchars($associate_name) ?></span>
                                        <span class="badge bg-light text-success ms-2"><?= __('mlm_dash_you', [], 'You') ?></span>
                                        <small class="d-block text-white-50"><?= htmlspecialchars($associate_id) ?></small>
                                    </div>
                                </div>
                                <?php
                                $level1 = array_filter($downline_members, fn($m) => ($m['level'] ?? 1) == 1);
                                $level2 = array_filter($downline_members, fn($m) => ($m['level'] ?? 1) == 2);
                                ?>
                                <?php if (!empty($level1)): ?>
                                <ul class="list-unstyled ms-4 mt-2 border-start border-2 border-success ps-3">
                                    <?php foreach ($level1 as $member): ?>
                                    <li class="mb-2">
                                        <div class="d-flex align-items-center p-2 rounded-2 bg-light">
                                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px;">
                                                <i class="fas fa-user text-success small"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="fw-semibold small"><?= htmlspecialchars($member['name'] ?? __('mlm_dash_member', [], 'Member')) ?></span>
                                                <span class="badge bg-success bg-opacity-10 text-success ms-1 small">L1</span>
                                                <small class="d-block text-muted" style="font-size: 0.75rem;"><?= __('mlm_dash_joined', [], 'Joined:') ?> <?= htmlspecialchars($member['join_date'] ?? 'N/A') ?></small>
                                            </div>
                                            <span class="badge <?= ($member['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' ?> small">
                                                <?= ucfirst(htmlspecialchars($member['status'] ?? 'active')) ?>
                                            </span>
                                        </div>
                                        <?php
                                        $l2Children = array_filter($downline_members, function($m) use ($member) {
                                            return ($m['level'] ?? 1) == 2 && ($m['parent_id'] ?? '') == ($member['id'] ?? '');
                                        });
                                        ?>
                                        <?php if (!empty($l2Children)): ?>
                                        <ul class="list-unstyled ms-4 mt-1 border-start border-2 border-success-subtle ps-3">
                                            <?php foreach ($l2Children as $child): ?>
                                            <li class="mb-1">
                                                <div class="d-flex align-items-center p-2 rounded-2" style="background: rgba(25,135,84,0.03);">
                                                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                                                        <i class="fas fa-user text-success" style="font-size: 0.65rem;"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <span class="small"><?= htmlspecialchars($child['name'] ?? __('mlm_dash_member', [], 'Member')) ?></span>
                                                        <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size: 0.65rem;">L2</span>
                                                    </div>
                                                    <span class="badge <?= ($child['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' ?>" style="font-size: 0.65rem;">
                                                        <?= ucfirst(htmlspecialchars($child['status'] ?? 'active')) ?>
                                                    </span>
                                                </div>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-seedling fa-2x text-muted"></i>
                            </div>
                            <h6 class="text-muted"><?= __('mlm_dash_network_empty', [], 'Your network is empty') ?></h6>
                            <p class="text-muted small mb-3"><?= __('mlm_dash_start_growing', [], 'Start growing your team by sharing your referral code') ?></p>
                            <button class="btn btn-success btn-sm" onclick="shareReferralLink()">
                                <i class="fas fa-share-alt me-1"></i> <?= __('mlm_dash_share_referral', [], 'Share Referral Link') ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Downline Members -->
    <div class="row mb-4" id="downlineSection">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-users text-success me-2"></i><?= __('mlm_dash_downline_members', [], 'Downline Members') ?></h6>
                    <span class="badge bg-success"><?= count($downline_members) ?> <?= __('mlm_dash_members_badge', [], 'Members') ?></span>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($downline_members)): ?>
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                            <thead>
                                <tr style="background: rgba(25,135,84,0.05);">
                                    <th class="border-0 fw-semibold text-muted small text-uppercase">#</th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_name', [], 'Name') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_level', [], 'Level') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_join_date', [], 'Join Date') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_status', [], 'Status') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_sales', [], 'Sales') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $idx = 1; foreach ($downline_members as $member): ?>
                                <tr>
                                    <td class="text-muted small"><?= $idx++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                                <i class="fas fa-user text-success small"></i>
                                            </div>
                                            <span class="fw-semibold"><?= htmlspecialchars($member['name'] ?? 'N/A') ?></span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success"><?= __('mlm_dash_level', [], 'Level') ?> <?= htmlspecialchars($member['level'] ?? 1) ?></span></td>
                                    <td class="text-muted small"><?= htmlspecialchars($member['join_date'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php $status = $member['status'] ?? 'active'; ?>
                                        <?php if ($status === 'active'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i><?= __('mlm_dash_active', [], 'Active') ?></span>
                                        <?php elseif ($status === 'inactive'): ?>
                                            <span class="badge bg-secondary"><i class="fas fa-pause-circle me-1"></i><?= __('mlm_dash_inactive', [], 'Inactive') ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i><?= ucfirst(htmlspecialchars($status)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold text-dark">&#8377;<?= number_format($member['sales'] ?? 0, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0"><?= __('mlm_dash_no_downline', [], 'No downline members yet. Share your referral link to build your team!') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Commission History -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-coins text-success me-2"></i><?= __('mlm_dash_commission_history', [], 'Commission History') ?></h6>
                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-success"><?= __('mlm_dash_view_all', [], 'View All') ?></a>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($commission_history)): ?>
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                            <thead>
                                <tr style="background: rgba(25,135,84,0.05);">
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_date', [], 'Date') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_type', [], 'Type') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_amount', [], 'Amount') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_status', [], 'Status') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($commission_history as $comm): ?>
                                <tr>
                                    <td class="text-muted small"><?= htmlspecialchars($comm['date'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="fw-semibold small">
                                            <?php
                                            $type = $comm['type'] ?? 'Direct';
                                            $typeIcons = [
                                                'Direct' => 'fa-user-plus',
                                                'Indirect' => 'fa-sitemap',
                                                'Bonus' => 'fa-gift',
                                                'Override' => 'fa-layer-group',
                                            ];
                                            ?>
                                            <i class="fas <?= $typeIcons[$type] ?? 'fa-coins' ?> text-success me-1"></i>
                                            <?= htmlspecialchars($type) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">&#8377;<?= number_format($comm['amount'] ?? 0, 2) ?></td>
                                    <td>
                                        <?php $cStatus = $comm['status'] ?? 'pending'; ?>
                                        <?php if ($cStatus === 'credited'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i><?= __('mlm_dash_credited', [], 'Credited') ?></span>
                                        <?php elseif ($cStatus === 'pending'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i><?= __('mlm_dash_pending', [], 'Pending') ?></span>
                                        <?php elseif ($cStatus === 'processing'): ?>
                                            <span class="badge bg-info"><i class="fas fa-spinner me-1"></i><?= __('mlm_dash_processing', [], 'Processing') ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($cStatus)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0"><?= __('mlm_dash_no_commission', [], 'No commission history yet.') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payout History -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-money-check-alt text-success me-2"></i><?= __('mlm_dash_payout_history', [], 'Payout History') ?></h6>
                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-success"><?= __('mlm_dash_view_all', [], 'View All') ?></a>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($payout_history)): ?>
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                            <thead>
                                <tr style="background: rgba(25,135,84,0.05);">
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_date', [], 'Date') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_amount', [], 'Amount') ?></th>
                                    <th class="border-0 fw-semibold text-muted small text-uppercase"><?= __('mlm_dash_th_status', [], 'Status') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payout_history as $payout): ?>
                                <tr>
                                    <td class="text-muted small"><?= htmlspecialchars($payout['date'] ?? 'N/A') ?></td>
                                    <td class="fw-bold text-dark">&#8377;<?= number_format($payout['amount'] ?? 0, 2) ?></td>
                                    <td>
                                        <?php $pStatus = $payout['status'] ?? 'pending'; ?>
                                        <?php if ($pStatus === 'completed'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i><?= __('mlm_dash_completed', [], 'Completed') ?></span>
                                        <?php elseif ($pStatus === 'pending'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i><?= __('mlm_dash_pending', [], 'Pending') ?></span>
                                        <?php elseif ($pStatus === 'processing'): ?>
                                            <span class="badge bg-info"><i class="fas fa-spinner me-1"></i><?= __('mlm_dash_processing', [], 'Processing') ?></span>
                                        <?php elseif ($pStatus === 'rejected'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i><?= __('mlm_dash_rejected', [], 'Rejected') ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($pStatus)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-wallet fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0"><?= __('mlm_dash_no_payout', [], 'No payout history yet.') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; border-radius: 12px; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important; }
    .card { border-radius: 12px; }
    .table th { font-size: 0.75rem; letter-spacing: 0.5px; }
    .table tbody tr:hover { background: rgba(25,135,84,0.03); }
</style>

<script>
    function copyReferralCode() {
        const code = '<?= htmlspecialchars($referral_code) ?>';
        navigator.clipboard.writeText(code).then(() => {
            const icon = document.getElementById('copyIcon');
            icon.classList.replace('fa-copy', 'fa-check');
            setTimeout(() => icon.classList.replace('fa-check', 'fa-copy'), 2000);
        });
    }

    function copyReferralLink() {
        const input = document.getElementById('referralLinkInput');
        const btn = document.getElementById('copyLinkBtn');
        navigator.clipboard.writeText(input.value).then(() => {
            btn.innerHTML = '<i class="fas fa-check me-1"></i> <?= __('mlm_dash_copied', [], 'Copied!') ?>';
            btn.classList.replace('btn-success', 'btn-outline-success');
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-copy me-1"></i> <?= __('mlm_dash_copy_link', [], 'Copy Link') ?>';
                btn.classList.replace('btn-outline-success', 'btn-success');
            }, 2000);
        });
    }

    function shareReferralLink() {
        const link = document.getElementById('referralLinkInput').value;
        if (navigator.share) {
            navigator.share({ title: 'Join APS Realty', text: '<?= __('mlm_dash_share_text', [], 'Use my referral code to join APS Realty!') ?>', url: link });
        } else {
            copyReferralLink();
        }
    }

    function shareVia(platform) {
        const link = document.getElementById('referralLinkInput').value;
        const text = encodeURIComponent('<?= __('mlm_dash_share_text', [], 'Use my referral code to join APS Realty!') ?> ' + link);
        const urls = {
            whatsapp: 'https://wa.me/?text=' + text,
            telegram: 'https://t.me/share/url?url=' + encodeURIComponent(link) + '&text=' + encodeURIComponent('Join APS Realty!'),
            twitter: 'https://twitter.com/intent/tweet?text=' + text,
            email: 'mailto:?subject=' + encodeURIComponent('Join APS Realty') + '&body=' + text
        };
        window.open(urls[platform], '_blank');
    }

    function requestPayout() {
        <?php if ($pending_payout > 0): ?>
        if (confirm('<?= __('mlm_dash_confirm_payout', ['amount' => number_format($pending_payout, 2)], 'Request payout of ₹' . number_format($pending_payout, 2) . '?') ?>')) {
            alert('<?= __('mlm_dash_payout_submitted', [], 'Payout request submitted successfully!') ?>');
        }
        <?php else: ?>
        alert('<?= __('mlm_dash_no_pending', [], 'No pending payout available.') ?>');
        <?php endif; ?>
    }

    function downloadStatement() {
        alert('<?= __('mlm_dash_generating', [], 'Generating your statement... Download will begin shortly.') ?>');
    }
</script>
