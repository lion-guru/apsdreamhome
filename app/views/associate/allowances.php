<?php
/**
 * Associate Leadership Office Support & Site Visit Fuel Allowances View
 */
$user = $user ?? [];
$rank = $rank ?? 'Ass.';
$officeEval = $officeEval ?? [];
$siteVisits = $siteVisits ?? [];
$claims = $claims ?? [];
?>

<div class="container-fluid px-4 py-3">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold">
                <i class="fas fa-briefcase text-primary me-2"></i> लीडरशिप ऑफिस सपोर्ट एवं साइट विजिट भत्ता
            </h2>
            <p class="text-muted small mb-0">
                Claim branch office rent support and customer site visit travel/fuel reimbursements.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?php echo BASE_URL; ?>/associate/salary" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-hand-holding-usd me-1"></i> Salary Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/associate/schedule-site-visit" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-calendar-check me-1"></i> New Site Visit
            </a>
            <a href="<?php echo BASE_URL; ?>/associate/wallet" class="btn btn-primary btn-sm">
                <i class="fas fa-wallet me-1"></i> My Wallet
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ROW: OFFICE SUPPORT & SITE VISIT RATES -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Branch Office Maintenance & Rent Support -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-building text-primary me-2"></i> ऑफिस किराया व मेंटेनेंस सहायता (Office Support)
                        </h5>
                        <span class="badge bg-primary-subtle text-primary fw-bold">Rank: <?php echo htmlspecialchars($rank); ?></span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <?php if (!empty($officeEval['eligible_for_program'])): ?>
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark"><?php echo htmlspecialchars($officeEval['label'] ?? ''); ?></strong>
                                <span class="badge bg-success fs-6">₹<?php echo number_format($officeEval['allowance_amount'] ?? 0); ?> / माह</span>
                            </div>
                            <div class="small text-muted">
                                मासिक टीम बिजनेस टारगेट: <strong>₹<?php echo number_format($officeEval['target_volume'] ?? 0); ?></strong>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>इस माह की टीम बिक्री (Current Month Team Sales):</span>
                                <strong>₹<?php echo number_format($officeEval['achieved_volume'] ?? 0); ?> / ₹<?php echo number_format($officeEval['target_volume'] ?? 0); ?></strong>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar <?php echo !empty($officeEval['is_qualified']) ? 'bg-success' : 'bg-primary'; ?>" 
                                     style="width: <?php echo $officeEval['progress_pct'] ?? 0; ?>%;">
                                    <?php echo round($officeEval['progress_pct'] ?? 0, 1); ?>%
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div>
                                <?php if (!empty($officeEval['is_qualified'])): ?>
                                    <span class="text-success small fw-bold">
                                        <i class="fas fa-check-circle me-1"></i> Target Achieved! Eligible for Claim.
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">
                                        Remaining: ₹<?php echo number_format($officeEval['remaining_volume'] ?? 0); ?> to unlock.
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if (($officeEval['claim_status'] ?? '') === 'paid'): ?>
                                    <button class="btn btn-success btn-sm" disabled>
                                        <i class="fas fa-check me-1"></i> Paid for This Month
                                    </button>
                                <?php elseif (($officeEval['claim_status'] ?? '') === 'claimed'): ?>
                                    <button class="btn btn-warning btn-sm" disabled>
                                        <i class="fas fa-clock me-1"></i> Under Admin Review
                                    </button>
                                <?php elseif (!empty($officeEval['is_qualified'])): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#claimOfficeModal">
                                        <i class="fas fa-paper-plane me-1"></i> Claim ₹<?php echo number_format($officeEval['allowance_amount'] ?? 0); ?>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="fas fa-lock me-1"></i> Target Incomplete
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-lock fa-2x mb-2 text-secondary"></i>
                            <p class="mb-1 fw-bold">ऑफिस सपोर्ट BDM रैंक और उससे ऊपर के लिए उपलब्ध है।</p>
                            <small>वर्तमान रैंक: <strong><?php echo htmlspecialchars($rank); ?></strong>. BDM बनने पर ₹10,000/माह, Sr. BDM पर ₹20,000/माह, VP पर ₹35,000/माह, और प्रेसिडेंट पर ₹50,000/माह ऑफिस खर्च प्राप्त होता है।</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card 2: Site Visit Fuel & Travel Allowance Slabs -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-gas-pump text-success me-2"></i> साइट विजिट पेट्रोल व वाहन भत्ता (Travel Slabs)
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <div class="p-3 border rounded mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="badge bg-info text-dark me-1">Local</span>
                                <strong>गोरखपुर लोकल टाउनशिप विजिट</strong>
                            </div>
                            <span class="badge bg-success fs-6">₹800 / Visit</span>
                        </div>
                        <small class="text-muted">कंपनी की गोरखपुर स्थित किसी भी कॉलोनी पर वेरिफाइड ग्राहक विजिट कराने पर।</small>
                    </div>

                    <div class="p-3 border rounded mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="badge bg-warning text-dark me-1">Outstation</span>
                                <strong>पूर्वांचल रीजनल / दूरस्थ विजिट</strong>
                            </div>
                            <span class="badge bg-success fs-6">₹1,800 / Trip</span>
                        </div>
                        <small class="text-muted">गोरखपुर से बाहर (देवरिया, महराजगंज, कुशीनगर, बस्ती आदि) ग्राहक टूर व वाहन खर्च।</small>
                    </div>

                    <div class="small text-muted">
                        <i class="fas fa-shield-alt text-primary me-1"></i> <strong>सुरक्षा जांच:</strong> भत्ता प्राप्त करने के लिए साइट विजिट का रिकॉर्डेड और वेरिफाइड होना आवश्यक है।
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT SITE VISITS (ELIGIBLE FOR FUEL CLAIM) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-map-marker-alt text-danger me-2"></i> हाल ही की साइट विजिट्स (Claim Travel Reimbursement)
                </h5>
                <a href="<?php echo BASE_URL; ?>/associate/site-visits" class="btn btn-outline-secondary btn-sm">
                    View All Visits
                </a>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr style="font-size: 12px;">
                            <th>Visit ID</th>
                            <th>Visitor / Client</th>
                            <th>Visit Date</th>
                            <th>Status</th>
                            <th>Claim Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        <?php if (!empty($siteVisits)): ?>
                            <?php foreach ($siteVisits as $sv): ?>
                                <tr>
                                    <td><span class="badge bg-light text-secondary border">#<?php echo (int)$sv['id']; ?></span></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($sv['visitor_name'] ?? 'Client'); ?></strong>
                                        <?php if (!empty($sv['visitor_phone'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($sv['visitor_phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($sv['visit_date'] ?? date('Y-m-d')); ?></td>
                                    <td>
                                        <?php if (($sv['status'] ?? '') === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif (($sv['status'] ?? '') === 'confirmed'): ?>
                                            <span class="badge bg-info text-dark">Confirmed</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($sv['status'] ?? 'Scheduled'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($sv['already_claimed'])): ?>
                                            <span class="badge bg-light text-success border"><i class="fas fa-check-circle me-1"></i> Claimed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning">Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if (empty($sv['already_claimed']) && in_array($sv['status'] ?? '', ['completed', 'confirmed'], true)): ?>
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    onclick="openClaimVisitModal(<?php echo (int)$sv['id']; ?>, '<?php echo htmlspecialchars(addslashes($sv['visitor_name'] ?? 'Client')); ?>')">
                                                <i class="fas fa-gas-pump me-1"></i> Claim Allowance
                                            </button>
                                        <?php elseif (!empty($sv['already_claimed'])): ?>
                                            <button class="btn btn-light btn-sm text-muted" disabled>Claimed</button>
                                        <?php else: ?>
                                            <button class="btn btn-light btn-sm text-muted" disabled title="Visit must be confirmed/completed">Not Completed</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No recent site visits found. Schedule site visits to claim travel allowance!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PAST CLAIMS & DISBURSEMENTS LEDGER -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-receipt text-info me-2"></i> क्लेम एवं भुगतान लेजर (Allowances Claim History)
            </h5>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr style="font-size: 12px;">
                            <th>Claim ID</th>
                            <th>Allowance Type</th>
                            <th>Month / Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Details / Notes</th>
                            <th>Approval Date</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        <?php if (!empty($claims)): ?>
                            <?php foreach ($claims as $c): ?>
                                <tr>
                                    <td><span class="badge bg-light text-secondary border">#<?php echo (int)$c['id']; ?></span></td>
                                    <td>
                                        <?php if (($c['allowance_type'] ?? '') === 'office_support'): ?>
                                            <span class="badge bg-primary"><i class="fas fa-building me-1"></i> Office Support</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-gas-pump me-1"></i> Site Visit Fuel</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($c['month_year'] ?? '—'); ?></td>
                                    <td class="text-success fw-bold">₹<?php echo number_format((float)($c['amount'] ?? 0)); ?></td>
                                    <td>
                                        <?php
                                            $st = $c['status'] ?? 'claimed';
                                            if ($st === 'paid') {
                                                echo '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Paid to Wallet</span>';
                                            } elseif ($st === 'approved') {
                                                echo '<span class="badge bg-info text-dark">Approved</span>';
                                            } elseif ($st === 'claimed') {
                                                echo '<span class="badge bg-warning text-dark">Pending Admin Approval</span>';
                                            } elseif ($st === 'rejected') {
                                                echo '<span class="badge bg-danger">Rejected</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">' . htmlspecialchars(ucfirst($st)) . '</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($c['notes'] ?? '—'); ?></td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($c['approved_at'] ?? 'Pending'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No allowance claims submitted yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL: CLAIM OFFICE SUPPORT -->
<div class="modal fade" id="claimOfficeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo BASE_URL; ?>/associate/allowances/claim-office" method="POST">
                <?php echo \App\Helpers\SecurityHelper::csrfField(); ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-building text-primary me-2"></i> Claim Monthly Office Support
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-light rounded mb-3 small">
                        <strong>Eligible Amount:</strong> ₹<?php echo number_format($officeEval['allowance_amount'] ?? 0); ?><br>
                        <strong>Qualifying Volume:</strong> ₹<?php echo number_format($officeEval['achieved_volume'] ?? 0); ?> of ₹<?php echo number_format($officeEval['target_volume'] ?? 0); ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Office Address / Notes (Optional)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Enter branch office address, electricity bill info or any notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check me-1"></i> Submit Office Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: CLAIM SITE VISIT ALLOWANCE -->
<div class="modal fade" id="claimVisitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo BASE_URL; ?>/associate/allowances/claim-visit" method="POST">
                <?php echo \App\Helpers\SecurityHelper::csrfField(); ?>
                <input type="hidden" name="site_visit_id" id="modalVisitId">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-gas-pump text-success me-2"></i> Claim Site Visit Travel Allowance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Client: <strong id="modalVisitorName"></strong> (Visit #<span id="modalVisitIdDisplay"></span>)
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Visit Trip Type</label>
                        <select name="visit_type" class="form-select form-select-sm" required>
                            <option value="local">Gorakhpur Local Township Visit (₹800 Flat)</option>
                            <option value="outstation">Purvanchal Regional / Outstation Trip (₹1,800 Flat)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Travel Notes / Vehicle Number (Optional)</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. UP53-AB-1234 / Green City Visit">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check me-1"></i> Submit Travel Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openClaimVisitModal(visitId, visitorName) {
    document.getElementById('modalVisitId').value = visitId;
    document.getElementById('modalVisitIdDisplay').textContent = visitId;
    document.getElementById('modalVisitorName').textContent = visitorName;
    new bootstrap.Modal(document.getElementById('claimVisitModal')).show();
}
</script>

