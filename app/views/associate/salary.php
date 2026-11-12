<?php
/**
 * Associate Diwali Dhamaka Salary Dashboard View
 */
$data = $data ?? [];
$userId = $data['user_id'] ?? 0;
$regDate = $data['registration_date'] ?? date('Y-m-d');
$daysSinceReg = $data['days_since_reg'] ?? 0;
$balancedVol = (float)($data['balanced_volume'] ?? 0.0);
$personalVol = (float)($data['personal_volume'] ?? 0.0);
$legBal = $data['leg_balance'] ?? [];
$legs = $legBal['legs'] ?? [];
$targets = $data['targets'] ?? [];
$currTarget = $data['current_target'] ?? ($targets[0] ?? []);
$maint = $data['maintenance'] ?? [];
$maintThresh = $maint['threshold'] ?? ['min_volume' => 50000.0, 'min_visits' => 2];
$maintActivity = $maint['activity'] ?? [];
$isMaintQualified = !empty($maint['is_qualified']);
$fastStart = $data['fast_start'] ?? [];
$grants = $data['grants'] ?? [];
$totalEarned = (float)($data['total_salary_earned'] ?? 0.0);
?>

<div class="container-fluid px-4 py-3">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold">
                <i class="fas fa-hand-holding-usd text-warning me-2"></i> परमानेंट लीडरशिप करियर सैलरी प्लान
            </h2>
            <p class="text-muted small mb-0">
                Permanent Leadership Career Salary Progress, 40:60 Leg Balance, and Active Monthly Maintenance Monitor.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?php echo BASE_URL; ?>/associate/mlm-plan" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-sitemap me-1"></i> Full Business Plan
            </a>
            <a href="<?php echo BASE_URL; ?>/associate/allowances" class="btn btn-outline-success btn-sm">
                <i class="fas fa-gas-pump me-1"></i> Site Visit & Office Allowances
            </a>
            <a href="<?php echo BASE_URL; ?>/associate/wallet" class="btn btn-primary btn-sm">
                <i class="fas fa-wallet me-1"></i> My Wallet
            </a>
        </div>
    </div>

    <!-- 30-DAY FAST-START KICKSTART BANNER (IF WITHIN 30 DAYS OR QUALIFIED) -->
    <?php if (!empty($fastStart['in_window']) || !empty($fastStart['awarded'])): ?>
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; border-radius: 12px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark px-3 py-1 fw-bold fs-6">
                            <i class="fas fa-bolt me-1"></i> 30-Day Fast-Start Kickstart Bonus
                        </span>
                        <?php if (!empty($fastStart['awarded'])): ?>
                            <span class="badge bg-success px-3 py-1"><i class="fas fa-check-circle me-1"></i> Awarded (₹2,100 Credited)</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark px-3 py-1"><?php echo (int)($fastStart['days_remaining'] ?? 0); ?> Days Left</span>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold mb-2">अपनी जॉइनिंग के पहले 30 दिनों में जीतें ₹2,100 का नकद बोनस!</h4>
                    <p class="small text-white-50 mb-3">
                        5 एक्टिव एसोसिएट्स डायरेक्ट जोड़ें और कम से कम 3 वेरिफाइड कस्टमर साइट विजिट कराएं। पूरा होते ही ₹2,100 सीधे आपके वॉलेट में जमा हो जाएंगे।
                    </p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="bg-white bg-opacity-10 p-2 rounded">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>एसोसिएट्स भर्ती (Recruits):</span>
                                    <strong class="text-warning"><?php echo (int)($fastStart['recruited_associates'] ?? 0); ?> / 5</strong>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <?php $pctRec = min(100, ((int)($fastStart['recruited_associates'] ?? 0) / 5) * 100); ?>
                                    <div class="progress-bar bg-warning" style="width: <?php echo $pctRec; ?>%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-white bg-opacity-10 p-2 rounded">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>साइट विजिट्स (Site Visits):</span>
                                    <strong class="text-warning"><?php echo (int)($fastStart['verified_site_visits'] ?? 0); ?> / 3</strong>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <?php $pctVis = min(100, ((int)($fastStart['verified_site_visits'] ?? 0) / 3) * 100); ?>
                                    <div class="progress-bar bg-warning" style="width: <?php echo $pctVis; ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center mt-3 mt-lg-0">
                    <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 100px; height: 100px;">
                        <i class="fas fa-trophy fa-3x text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-warning mb-0">₹2,100</div>
                    <small class="text-white-50">Instant Cash Wallet Credit</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4">
        <!-- Card 1: 40:60 Balanced Volume -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">40:60 Balanced Volume</span>
                        <span class="badge bg-primary-subtle text-primary"><i class="fas fa-balance-scale"></i></span>
                    </div>
                    <h3 class="h4 fw-bold mb-1">₹<?php echo number_format($balancedVol); ?></h3>
                    <small class="text-muted">Personal: ₹<?php echo number_format($personalVol); ?></small>
                </div>
            </div>
        </div>

        <!-- Card 2: Current Salary Target -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Current Salary Target</span>
                        <span class="badge bg-warning-subtle text-warning"><i class="fas fa-bullseye"></i></span>
                    </div>
                    <h3 class="h4 fw-bold mb-1"><?php echo htmlspecialchars($currTarget['name'] ?? 'Target 1'); ?></h3>
                    <small class="text-muted">Goal: ₹<?php echo number_format($currTarget['target_volume'] ?? 1500000); ?> (<?php echo (int)($currTarget['days_left'] ?? 0); ?>d left)</small>
                </div>
            </div>
        </div>

        <!-- Card 3: Monthly Maintenance Status -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">This Month Status</span>
                        <?php if ($isMaintQualified): ?>
                            <span class="badge bg-success-subtle text-success"><i class="fas fa-check-circle"></i></span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning"><i class="fas fa-exclamation-triangle"></i></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="h4 fw-bold mb-1">
                        <?php if ($isMaintQualified): ?>
                            <span class="text-success">Active & Eligible</span>
                        <?php else: ?>
                            <span class="text-warning">Action Needed</span>
                        <?php endif; ?>
                    </h3>
                    <small class="text-muted">₹50k sales OR 2 site visits req.</small>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Salary Earned -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Total Salary Earned</span>
                        <span class="badge bg-success-subtle text-success"><i class="fas fa-money-bill-wave"></i></span>
                    </div>
                    <h3 class="h4 fw-bold mb-1 text-success">₹<?php echo number_format($totalEarned); ?></h3>
                    <small class="text-muted"><?php echo count($grants); ?> Grant(s) on file</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW: MONTHLY MAINTENANCE MONITOR & 40:60 LEG BALANCE BREAKDOWN -->
    <div class="row g-4 mb-4">
        <!-- Monthly Maintenance Monitor -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-shield-alt text-primary me-2"></i> मासिक मेंटेनेंस कोटा (Active Maintenance)
                        </h5>
                        <?php if ($isMaintQualified): ?>
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i> Qualified for Next Payout</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-clock me-1"></i> Inactive (Pending Activity)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="p-3 bg-light rounded mb-3 small">
                        <strong>Anti-Free-Rider सुरक्षा नियम:</strong> सैलरी की मासिक किस्त घर बैठकर नहीं मिलती। हर माह कम से कम <strong>₹50,000 की सेल</strong> अथवा <strong>2 वेरिफाइड कस्टमर साइट विजिट</strong> अनिवार्य है। 2 महीने लगातार इनएक्टिव रहने पर सैलरी स्वतः पॉज हो जाती है।
                    </div>

                    <!-- Criteria 1: Sales Volume -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>विकल्प 1: इस माह की बिक्री (Sales Volume):</span>
                            <strong>₹<?php echo number_format($maintActivity['sales_volume'] ?? 0); ?> / ₹50,000</strong>
                        </div>
                        <?php $pctSale = min(100, ((float)($maintActivity['sales_volume'] ?? 0) / 50000.0) * 100); ?>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar <?php echo $pctSale >= 100 ? 'bg-success' : 'bg-primary'; ?>" style="width: <?php echo $pctSale; ?>%;"></div>
                        </div>
                    </div>

                    <!-- Criteria 2: Verified Site Visits -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>विकल्प 2: वेरिफाइड कस्टमर साइट विजिट (Site Visits):</span>
                            <strong><?php echo (int)($maintActivity['verified_visits'] ?? 0); ?> / 2 Visits</strong>
                        </div>
                        <?php $pctVisits = min(100, ((int)($maintActivity['verified_visits'] ?? 0) / 2) * 100); ?>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar <?php echo $pctVisits >= 100 ? 'bg-success' : 'bg-info'; ?>" style="width: <?php echo $pctVisits; ?>%;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small">
                        <span class="text-muted">Month: <?php echo htmlspecialchars($maintActivity['month'] ?? date('Y-m')); ?></span>
                        <a href="<?php echo BASE_URL; ?>/associate/schedule-site-visit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar-plus me-1"></i> Schedule Site Visit
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 40:60 Leg Balance Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-balance-scale-right text-warning me-2"></i> 40:60 लेग बैलेंस संरचना (Leg Balance)
                        </h5>
                        <span class="badge bg-light text-dark border">Max 60% Per Leg</span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="p-3 bg-light rounded mb-3 small">
                        <strong>संतुलन का नियम:</strong> किसी एक सुपरस्टार लेग पर 100% निर्भरता रोकी जाती है। कुल टारगेट का अधिकतम 60% वॉल्यूम सबसे मजबूत लेग से लिया जाएगा; शेष 40% अपनी दूसरी लेग्स व पर्सनल बुकिंग से लाना होगा।
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr style="font-size: 12px;">
                                    <th>Leg / Source</th>
                                    <th class="text-end">Actual Volume</th>
                                    <th class="text-end">Capped (Qualifying)</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 13px;">
                                <tr>
                                    <td><i class="fas fa-user text-primary me-1"></i> <strong>Personal Bookings</strong></td>
                                    <td class="text-end">₹<?php echo number_format($personalVol); ?></td>
                                    <td class="text-end text-success fw-bold">₹<?php echo number_format($personalVol); ?></td>
                                </tr>
                                <?php if (!empty($legs)): ?>
                                    <?php foreach ($legs as $idx => $lg): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-users text-secondary me-1"></i>
                                                <?php echo htmlspecialchars($lg['user_name'] ?? ('Leg ' . ($idx + 1))); ?>
                                                <?php if (!empty($lg['is_strongest'])): ?>
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">Strongest (60% Max)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">₹<?php echo number_format((float)($lg['actual_volume'] ?? 0)); ?></td>
                                            <td class="text-end text-success fw-bold">₹<?php echo number_format((float)($lg['qualifying_volume'] ?? 0)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-2 small">No active team legs recorded yet. Add associates to build legs!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold" style="font-size: 13px;">
                                <tr>
                                    <td>Total Qualifying Volume:</td>
                                    <td></td>
                                    <td class="text-end text-primary">₹<?php echo number_format($balancedVol); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DIWALI DHAMAKA 5-TIER TARGET CARDS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-award text-primary me-2"></i> 5 आधिकारिक लीडरशिप सैलरी टारगेट्स (Live Career Progression)
            </h5>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                <?php foreach ($targets as $t): ?>
                    <?php
                        $stClass = 'border-light bg-light';
                        $badgeClass = 'bg-secondary';
                        $statusLabel = 'Locked';
                        if ($t['status'] === 'achieved') {
                            $stClass = 'border-success bg-success bg-opacity-10';
                            $badgeClass = 'bg-success';
                            $statusLabel = 'Achieved 🎉';
                        } elseif ($t['status'] === 'in_progress') {
                            $stClass = 'border-primary shadow-sm bg-white';
                            $badgeClass = 'bg-primary';
                            $statusLabel = 'In Progress';
                        } elseif ($t['status'] === 'expired') {
                            $stClass = 'border-danger-subtle bg-light';
                            $badgeClass = 'bg-danger';
                            $statusLabel = 'Time Expired';
                        }
                    ?>
                    <div class="col-md-6 col-lg">
                        <div class="card h-100 <?php echo $stClass; ?>" style="border-radius: 10px;">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span>
                                        <small class="text-muted fw-semibold"><?php echo (int)$t['days']; ?> Days Window</small>
                                    </div>
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($t['name']); ?></h6>
                                    <div class="h5 fw-bold text-dark mb-1">₹<?php echo number_format($t['target_volume']); ?></div>
                                    <div class="text-success fw-bold small mb-2">
                                        ₹<?php echo number_format($t['monthly_payout']); ?> / माह × <?php echo (int)$t['duration_months']; ?> महीने
                                    </div>
                                    <div class="small text-muted mb-2">
                                        Total Grant: <strong>₹<?php echo number_format($t['total_grant']); ?></strong>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 11px;">
                                        <span>Progress: <?php echo round($t['progress_pct'], 1); ?>%</span>
                                        <span><?php echo (int)$t['days_left']; ?>d left</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar <?php echo $t['status'] === 'achieved' ? 'bg-success' : 'bg-primary'; ?>" 
                                             style="width: <?php echo $t['progress_pct']; ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- SALARY GRANTS & PAYOUT HISTORY -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-history text-secondary me-2"></i> सक्रिय सैलरी ग्रांट्स एवं पेआउट लेजर (Salary Grants & History)
            </h5>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr style="font-size: 12px;">
                            <th>Grant ID</th>
                            <th>Target Description</th>
                            <th>Monthly Installment</th>
                            <th>Tenure / Paid</th>
                            <th>Status</th>
                            <th>Active Maintenance</th>
                            <th>Next Payout Date</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        <?php if (!empty($grants)): ?>
                            <?php foreach ($grants as $g): ?>
                                <tr>
                                    <td><span class="badge bg-light text-secondary border">#<?php echo (int)$g['id']; ?></span></td>
                                    <td>
                                        <strong>Target: ₹<?php echo number_format((float)($g['target_volume'] ?? 0)); ?></strong>
                                        <br><small class="text-muted">Granted: <?php echo htmlspecialchars($g['created_at'] ?? '—'); ?></small>
                                    </td>
                                    <td class="text-success fw-bold">₹<?php echo number_format((float)($g['monthly_payout'] ?? 0)); ?> / mo</td>
                                    <td>
                                        <?php echo (int)($g['paid_installments'] ?? 0); ?> of <?php echo (int)($g['duration_months'] ?? 6); ?> Months
                                        <div class="progress mt-1" style="height: 4px; width: 80px;">
                                            <?php $gpct = min(100, ((int)($g['paid_installments'] ?? 0) / max(1, (int)($g['duration_months'] ?? 6))) * 100); ?>
                                            <div class="progress-bar bg-success" style="width: <?php echo $gpct; ?>%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (($g['status'] ?? '') === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif (($g['status'] ?? '') === 'paused'): ?>
                                            <span class="badge bg-warning text-dark">Paused (Maintenance Pending)</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($g['status'] ?? 'Completed'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($g['status'] ?? '') === 'paused'): ?>
                                            <span class="text-danger small"><i class="fas fa-times-circle me-1"></i> Inactive (Hold)</span>
                                        <?php else: ?>
                                            <span class="text-success small"><i class="fas fa-check-circle me-1"></i> Compliant</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo htmlspecialchars($g['next_payout_date'] ?? date('Y-m-d', strtotime('+1 month'))); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle me-1"></i> अभी कोई एक्टिव सैलरी ग्रांट नहीं है। उपरोक्त 5 टारगेट्स में से कोई एक टारगेट पूरा करें और हर महीने सैलरी पाएं!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

