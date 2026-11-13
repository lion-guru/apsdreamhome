<?php
$page_title = $page_title ?? 'Festival & Special Campaign Offers - Associate Portal';
$current_page = 'offers';
$dashboard = $dashboard ?? [];
$offers = $offers ?? ($dashboard['offers'] ?? []);
$claims = $claims ?? ($dashboard['claims'] ?? []);
$customerOffers = $customer_offers ?? [];
$activeCount = (int)($dashboard['active_count'] ?? count($offers));
$eligibleCount = (int)($dashboard['eligible_count'] ?? 0);

$totalEarned = 0.0;
foreach ($claims as $cl) {
    if (in_array($cl['claim_status'] ?? '', ['wallet_credited', 'delivered'], true)) {
        $totalEarned += (float)($cl['cash_amount'] ?? 0);
    }
}
?>

<div class="container-fluid px-4 py-3">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold">
                <i class="fas fa-gift text-danger me-2"></i> त्योहार व विशेष अभियान ऑफर्स (Festival & Special Offers)
            </h2>
            <p class="text-muted small mb-0">
                Participate in festive sales targets, earn pure gold coins, electronics, consumer gifts, and instant cash bonuses in your wallet.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/associate/salary" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-hand-holding-usd me-1"></i> Career Salary Plan
            </a>
            <a href="<?= BASE_URL ?>/associate/rewards" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-trophy me-1"></i> Rank Rewards
            </a>
            <a href="<?= BASE_URL ?>/associate/wallet" class="btn btn-primary btn-sm">
                <i class="fas fa-wallet me-1"></i> My Wallet
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- KPI SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-bold">Active Campaigns</div>
                            <div class="fs-3 fw-bold mt-1"><?= $activeCount ?></div>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 text-white">
                            <i class="fas fa-bullhorn fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-bold">Eligible to Claim</div>
                            <div class="fs-3 fw-bold mt-1"><?= $eligibleCount ?></div>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 text-white">
                            <i class="fas fa-check-double fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: white; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-bold">My Claims</div>
                            <div class="fs-3 fw-bold mt-1"><?= count($claims) ?></div>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 text-white">
                            <i class="fas fa-file-invoice fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%); color: white; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-bold">Total Earned</div>
                            <div class="fs-3 fw-bold mt-1">₹<?= number_format($totalEarned, 0) ?></div>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 text-white">
                            <i class="fas fa-coins fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTIVE FESTIVAL CAMPAIGNS GRID -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-fire text-danger me-2"></i> लाइव त्योहार व प्रमोशनल अभियान्स (Live Festive Targets)
            </h5>
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-1">
                Active Festival Window
            </span>
        </div>
        <div class="card-body pt-0">
            <?php if (empty($offers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Active Festival Campaigns Currently</h5>
                    <p class="text-muted small">New festival and holiday offers are announced by management regularly. Check back soon!</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($offers as $off): ?>
                        <?php
                            $prog = $off['progress'] ?? [];
                            $pct = (float)($prog['percent'] ?? 0.0);
                            $isEligible = !empty($prog['is_eligible']);
                            $userStatus = $off['user_status'] ?? 'in_progress';
                            $targetVal = (float)($prog['target_value'] ?? 0);
                            $curVol = (float)($prog['current_volume'] ?? 0);
                            $curBk = (int)($prog['current_bookings'] ?? 0);
                            $minVisits = (int)($prog['min_visits'] ?? 0);
                            $curVisits = (int)($prog['current_visits'] ?? 0);
                            $rewardItem = !empty($off['reward_item']) ? $off['reward_item'] : ($off['reward_type'] === 'bonus_amount' ? 'Cash Bonus' : 'Festival Gift');
                            $rewardVal = (float)($off['reward_value'] ?? 0);
                        ?>
                        <div class="col-md-6 col-lg-6">
                            <div class="card h-100 border shadow-sm rounded-3 <?= $isEligible ? 'border-success' : 'border-light' ?>" style="overflow: hidden;">
                                <div class="card-header border-0 py-3 text-white" style="background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-warning text-dark fw-bold">
                                            <i class="fas fa-star me-1"></i><?= htmlspecialchars($off['festival_name'] ?? 'Festival Special') ?>
                                        </span>
                                        <small class="text-white-50">
                                            Valid: <?= date('d M', strtotime($off['starts_at'])) ?> - <?= date('d M Y', strtotime($off['ends_at'])) ?>
                                        </small>
                                    </div>
                                    <h5 class="mb-0 mt-2 fw-bold text-white"><?= htmlspecialchars($off['title']) ?></h5>
                                </div>
                                <div class="card-body p-4">
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($off['description'] ?? '') ?></p>

                                    <!-- REWARD BANNER -->
                                    <div class="p-3 mb-3 rounded-3 d-flex align-items-center justify-content-between" style="background: #fef3c7; border-left: 4px solid #d97706;">
                                        <div>
                                            <div class="small text-uppercase fw-bold text-muted">Festival Reward</div>
                                            <div class="fw-bold text-dark fs-5">
                                                <i class="fas fa-award text-warning me-1"></i> <?= htmlspecialchars($rewardItem) ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">Cash Equivalent</div>
                                            <div class="fw-bold text-success fs-5">₹<?= number_format($rewardVal, 0) ?></div>
                                        </div>
                                    </div>

                                    <!-- PROGRESS METER -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small fw-bold mb-1">
                                            <span>
                                                Target Progress (<?= ($prog['criteria_type'] ?? '') === 'booking_count' ? 'Plot Bookings' : 'Sales Volume' ?>)
                                            </span>
                                            <span>
                                                <?php if (($prog['criteria_type'] ?? '') === 'booking_count'): ?>
                                                    <?= $curBk ?> / <?= (int)$targetVal ?> Bookings
                                                <?php else: ?>
                                                    ₹<?= number_format($curVol, 0) ?> / ₹<?= number_format($targetVal, 0) ?>
                                                <?php endif; ?>
                                                (<?= $pct ?>%)
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 12px; border-radius: 6px;">
                                            <div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-primary' ?> progress-bar-striped progress-bar-animated"
                                                 role="progressbar" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                    </div>

                                    <!-- SITE VISITS PREREQUISITE -->
                                    <?php if ($minVisits > 0): ?>
                                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light small mb-3">
                                            <span><i class="fas fa-map-marked-alt text-primary me-1"></i> Verified Site Visits:</span>
                                            <span class="fw-bold <?= $curVisits >= $minVisits ? 'text-success' : 'text-danger' ?>">
                                                <?= $curVisits ?> / <?= $minVisits ?> Completed
                                                <?= $curVisits >= $minVisits ? '✅' : '⏳' ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- ACTION / CLAIM BUTTON -->
                                    <div class="mt-4 pt-2 border-top d-flex align-items-center justify-content-between">
                                        <div>
                                            <?php if ($userStatus === 'eligible'): ?>
                                                <span class="badge bg-success py-2 px-3"><i class="fas fa-check-circle me-1"></i> Target Achieved!</span>
                                            <?php elseif ($userStatus === 'claimed'): ?>
                                                <span class="badge bg-warning text-dark py-2 px-3"><i class="fas fa-clock me-1"></i> Claim Under Review</span>
                                            <?php elseif ($userStatus === 'approved'): ?>
                                                <span class="badge bg-info text-white py-2 px-3"><i class="fas fa-thumbs-up me-1"></i> Approved / Processing</span>
                                            <?php elseif (in_array($userStatus, ['delivered', 'wallet_credited'], true)): ?>
                                                <span class="badge bg-success py-2 px-3"><i class="fas fa-gift me-1"></i> Reward Fulfilled 🎉</span>
                                            <?php else: ?>
                                                <span class="text-muted small">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <?php if (($prog['criteria_type'] ?? '') === 'booking_count'): ?>
                                                        <?= max(0, (int)$targetVal - $curBk) ?> more booking(s) needed
                                                    <?php else: ?>
                                                        ₹<?= number_format(max(0, $targetVal - $curVol), 0) ?> more volume needed
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div>
                                            <?php if ($userStatus === 'eligible'): ?>
                                                <button type="button" class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#claimModal<?= $off['id'] ?>">
                                                    <i class="fas fa-gift me-1"></i> Claim Reward
                                                </button>
                                            <?php elseif ($userStatus === 'in_progress'): ?>
                                                <a href="<?= BASE_URL ?>/associate/book-plot" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-plus me-1"></i> Book Plot
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CLAIM MODAL -->
                        <div class="modal fade" id="claimModal<?= $off['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form method="POST" action="<?= BASE_URL ?>/associate/offers/<?= $off['id'] ?>/claim">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-trophy me-2"></i>Claim Festival Reward</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="text-center mb-3">
                                                <div class="rounded-circle bg-warning bg-opacity-25 d-inline-flex p-3 mb-2">
                                                    <i class="fas fa-gift fa-3x text-warning"></i>
                                                </div>
                                                <h4 class="fw-bold mb-1"><?= htmlspecialchars($rewardItem) ?></h4>
                                                <p class="text-muted small">Campaign: <?= htmlspecialchars($off['title']) ?></p>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-uppercase">Choose Reward Mode</label>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <input type="radio" class="btn-check" name="claim_mode" id="mode_cash_<?= $off['id'] ?>" value="wallet_cash" checked>
                                                        <label class="btn btn-outline-success w-100 py-2 text-center" for="mode_cash_<?= $off['id'] ?>">
                                                            <i class="fas fa-wallet fa-lg d-block mb-1"></i>
                                                            <strong>₹<?= number_format($rewardVal, 0) ?> Cash</strong>
                                                            <small class="d-block text-muted">Wallet Credit</small>
                                                        </label>
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="radio" class="btn-check" name="claim_mode" id="mode_gift_<?= $off['id'] ?>" value="physical_gift">
                                                        <label class="btn btn-outline-danger w-100 py-2 text-center" for="mode_gift_<?= $off['id'] ?>">
                                                            <i class="fas fa-box-open fa-lg d-block mb-1"></i>
                                                            <strong>Physical Gift</strong>
                                                            <small class="d-block text-muted">Office / Courier</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Contact Phone Number</label>
                                                <input type="text" class="form-control" name="contact_phone" placeholder="Enter active contact number" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Delivery / Handover Address (if physical gift chosen)</label>
                                                <textarea class="form-control" name="shipping_address" rows="2" placeholder="Gorakhpur Office Handover or Full Postal Address..."></textarea>
                                            </div>

                                            <div class="alert alert-warning small mb-0">
                                                <i class="fas fa-info-circle me-1"></i> Your sales volume and verified site visits will be audited by management upon submission.
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger fw-bold px-4">Confirm & Submit Claim</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CUSTOMER PROMO CODES & WHATSAPP SHARE FOR ASSOCIATES -->
    <?php if (!empty($customerOffers)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-tags text-primary me-2"></i> ग्राहकों के लिए बुकिंग डिस्काउंट कूपन (Customer Promo Codes &amp; Deals)
                </h5>
                <p class="text-muted small mb-0 mt-1">
                    अपने क्लाइंट्स को ये फेस्टिव डिस्काउंट कूपन शेयर करें और तुरंत बुकिंग क्लोज करें।
                </p>
            </div>
            <a href="<?= BASE_URL ?>/offers" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="fas fa-external-link-alt me-1"></i> View Public Deals Page
            </a>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                <?php foreach ($customerOffers as $co):
                    $cDiscType = $co['discount_type'] ?? 'percentage';
                    $cDiscText = '';
                    if ($cDiscType === 'flat_amount') {
                        $cDiscText = '₹' . number_format((float)($co['discount_value'] ?? 0)) . ' FLAT OFF';
                    } elseif ($cDiscType === 'percentage') {
                        $pct = (float)($co['discount_percentage'] ?? 0);
                        $cDiscText = ($pct > 0 ? $pct : (float)($co['discount_value'] ?? 0)) . '% OFF';
                    } elseif ($cDiscType === 'per_sqft') {
                        $cDiscText = '₹' . number_format((float)($co['discount_value'] ?? 0)) . '/sqft छूट';
                    } else {
                        $cDiscText = 'विशेष उपहार';
                    }
                    $cCode = htmlspecialchars($co['promo_code'] ?? '');
                    $cFest = htmlspecialchars(!empty($co['festival_name']) ? $co['festival_name'] : 'Festive Special');
                    $shareMsg = "नमस्ते! APS Dream Home के खास फेस्टिव ऑफर में प्लॉट बुकिंग पर पाइए " . $cDiscText . "। प्रोमो कोड: " . ($co['promo_code'] ?? '') . " का उपयोग करें। ऑफर्स देखें: " . BASE_URL . "/offers?promo=" . urlencode($co['promo_code'] ?? '');
                    $waLink = "https://api.whatsapp.com/send?text=" . urlencode($shareMsg);
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between bg-light">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger small">
                                    <i class="fas fa-gift me-1"></i> <?= $cFest ?>
                                </span>
                                <span class="badge bg-success-subtle text-success fw-bold">
                                    <?= htmlspecialchars($cDiscText) ?>
                                </span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($co['title'] ?? 'Special Offer') ?></h6>
                            <p class="text-muted small mb-2 text-truncate" style="max-height: 40px;">
                                <?= htmlspecialchars($co['description'] ?? 'त्योहार पर विशेष बुकिंग छूट') ?>
                            </p>
                            <!-- Promo Code Badge -->
                            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-white border border-dashed mb-3">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">PROMO CODE</small>
                                    <strong class="text-primary font-monospace fs-6"><?= $cCode ?></strong>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary copy-promo-btn" data-code="<?= $cCode ?>" title="Copy Code">
                                    <i class="far fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= $waLink ?>" target="_blank" class="btn btn-sm btn-success flex-grow-1 d-flex align-items-center justify-content-center gap-1">
                                <i class="fab fa-whatsapp"></i> WhatsApp Share
                            </a>
                            <a href="<?= BASE_URL ?>/associate/book-plot?promo=<?= urlencode($co['promo_code'] ?? '') ?>" class="btn btn-sm btn-outline-primary" title="Book Plot with this promo">
                                Book Plot
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- CLAIMS & FULFILLMENT LEDGER -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-history text-primary me-2"></i> मेरे त्योहार क्लेम्स व रिवार्ड्स रिकॉर्ड (My Claims Ledger)
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($claims)): ?>
                <div class="text-center py-4 text-muted small">
                    No festival claims submitted yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Claim ID</th>
                                <th>Festival Campaign</th>
                                <th>Reward Type</th>
                                <th>Mode</th>
                                <th>Value</th>
                                <th>Submitted On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($claims as $cl): ?>
                                <tr>
                                    <td><strong>#<?= $cl['id'] ?></strong></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($cl['offer_title'] ?? 'Festival Offer') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($cl['festival_name'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-gift text-warning me-1"></i><?= htmlspecialchars($cl['reward_item'] ?? 'Reward') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= ($cl['claim_mode'] ?? '') === 'physical_gift' ? '<span class="badge bg-info text-white">Physical Gift</span>' : '<span class="badge bg-success">Wallet Cash</span>' ?>
                                    </td>
                                    <td>
                                        <strong class="text-success">₹<?= number_format((float)($cl['cash_amount'] ?? 0), 2) ?></strong>
                                    </td>
                                    <td>
                                        <small><?= !empty($cl['claimed_at']) ? date('d M Y, h:i A', strtotime($cl['claimed_at'])) : 'N/A' ?></small>
                                    </td>
                                    <td>
                                        <?php if ($cl['claim_status'] === 'claimed'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending Verification</span>
                                        <?php elseif ($cl['claim_status'] === 'approved'): ?>
                                            <span class="badge bg-info text-white"><i class="fas fa-check me-1"></i> Approved</span>
                                        <?php elseif ($cl['claim_status'] === 'wallet_credited'): ?>
                                            <span class="badge bg-success"><i class="fas fa-wallet me-1"></i> Credited to Wallet</span>
                                        <?php elseif ($cl['claim_status'] === 'delivered'): ?>
                                            <span class="badge bg-success"><i class="fas fa-gift me-1"></i> Handed Over</span>
                                        <?php elseif ($cl['claim_status'] === 'rejected'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i> Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-promo-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var code = this.getAttribute('data-code');
            if (!code) return;
            var that = this;
            navigator.clipboard.writeText(code).then(function() {
                var orig = that.innerHTML;
                that.innerHTML = '<i class="fas fa-check text-success"></i> Copied';
                setTimeout(function() { that.innerHTML = orig; }, 2000);
            }).catch(function() {
                // Fallback prompt
                prompt('Copy promo code:', code);
            });
        });
    });
});
</script>

</div>
