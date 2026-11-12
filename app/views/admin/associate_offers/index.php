<?php
$page_title = $page_title ?? 'Associate Festival & Campaign Offers';
$offers = $offers ?? [];
$colonies = $colonies ?? [];
$claims = $claims ?? [];
$stats = $stats ?? ['total_campaigns' => count($offers), 'pending_claims' => 0, 'approved_claims' => 0, 'fulfilled_claims' => 0, 'total_paid_out' => 0];

$rewardLabels = ['bonus_amount' => 'Cash Bonus ₹', 'commission_boost_pct' => 'Comm. Boost %', 'gift' => 'Physical Gift'];
$criteriaLabels = ['sale_volume' => 'Sales Volume ₹', 'booking_count' => 'Plot Bookings'];
$today = date('Y-m-d');
?>

<div class="container-fluid py-4">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-bullhorn text-danger me-2"></i> त्योहार व विशेष अभियान प्रबंधन (Festival & Campaign Management)
            </h1>
            <p class="text-muted small mb-0">
                Design festival sales targets for associates, approve reward claims, disburse instant wallet bonuses, and track physical gift handovers.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/admin/promotional-offers" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="fas fa-percent me-1"></i> Customer Festive Discounts & Codes
            </a>
            <button class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#offerModal">
                <i class="fas fa-plus me-1"></i> New Festival Campaign
            </button>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- KPI SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="text-white-50 small text-uppercase fw-bold">Total Campaigns</div>
                    <div class="fs-3 fw-bold mt-1"><?= $stats['total_campaigns'] ?? count($offers) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #d97706, #f59e0b); color: white; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="text-white-50 small text-uppercase fw-bold">Pending Claims</div>
                    <div class="fs-3 fw-bold mt-1"><?= $stats['pending_claims'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #059669, #10b981); color: white; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="text-white-50 small text-uppercase fw-bold">Approved / Ready</div>
                    <div class="fs-3 fw-bold mt-1"><?= $stats['approved_claims'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: white; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="text-white-50 small text-uppercase fw-bold">Total Disbursed</div>
                    <div class="fs-3 fw-bold mt-1">₹<?= number_format((float)($stats['total_paid_out'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS NAVIGATION -->
    <ul class="nav nav-pills mb-4" id="offerTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold" id="campaigns-tab" data-bs-toggle="pill" data-bs-target="#campaigns-pane" type="button">
                <i class="fas fa-bullhorn me-1"></i> Active &amp; Draft Campaigns (<?= count($offers) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold position-relative" id="claims-tab" data-bs-toggle="pill" data-bs-target="#claims-pane" type="button">
                <i class="fas fa-clipboard-check me-1"></i> Associate Claims Desk (<?= count($claims) ?>)
                <?php if (!empty($stats['pending_claims'])): ?>
                    <span class="badge bg-danger rounded-pill ms-1"><?= $stats['pending_claims'] ?></span>
                <?php endif; ?>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="offerTabsContent">

        <!-- TAB 1: CAMPAIGNS LIST -->
        <div class="tab-pane fade show active" id="campaigns-pane">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold">Campaigns Roster</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Campaign / Festival</th>
                                    <th>Target Requirement</th>
                                    <th>Reward &amp; Mode</th>
                                    <th>Duration Window</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($offers)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No offer campaigns yet — click 'New Festival Campaign' to launch one.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($offers as $o): ?>
                                    <?php $live = ($o['status'] ?? '') === 'active' && $today >= ($o['starts_at'] ?? '') && $today <= ($o['ends_at'] ?? ''); ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($o['title'] ?? '') ?></div>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger small">
                                                <?= htmlspecialchars($o['festival_name'] ?? 'Festival Special') ?>
                                            </span>
                                            <?php if (!empty($o['colony_name'])): ?>
                                                <small class="text-muted ms-1"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($o['colony_name']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?= ($o['criteria_type'] ?? '') === 'booking_count'
                                                    ? (int)($o['criteria_value'] ?? 0) . ' Bookings'
                                                    : '₹' . number_format((float)($o['criteria_value'] ?? 0), 0) . ' Volume' ?>
                                            </div>
                                            <?php if (!empty($o['min_site_visits'])): ?>
                                                <small class="text-muted"><i class="fas fa-route"></i> Min <?= (int)$o['min_site_visits'] ?> Site Visits</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-award text-warning me-1"></i><?= htmlspecialchars($o['reward_item'] ?? ($rewardLabels[$o['reward_type']] ?? 'Reward')) ?>
                                            </span>
                                            <div class="small fw-bold text-success mt-1">₹<?= number_format((float)($o['reward_value'] ?? 0), 2) ?></div>
                                        </td>
                                        <td class="small">
                                            <?= htmlspecialchars($o['starts_at'] ?? '') ?> &rarr; <?= htmlspecialchars($o['ends_at'] ?? '') ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $live ? 'success' : (($o['status'] ?? '') === 'active' ? 'warning text-dark' : 'secondary') ?>">
                                                <?= $live ? 'Live' : htmlspecialchars($o['status'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if (($o['status'] ?? '') === 'draft'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/activate" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                                    <button class="btn btn-sm btn-success fw-bold" onclick="return confirm('Activate? All associates will now see this offer in their portal.')">
                                                        <i class="fas fa-play me-1"></i> Activate
                                                    </button>
                                                </form>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/delete" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this draft?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php elseif (($o['status'] ?? '') === 'active'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/close" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Close this campaign? It will be marked closed.')">
                                                        <i class="fas fa-stop me-1"></i> Close
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: CLAIMS DESK -->
        <div class="tab-pane fade" id="claims-pane">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold">Associate Claim Submissions &amp; Verification</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Associate Details</th>
                                    <th>Festival Campaign</th>
                                    <th>Target Achieved</th>
                                    <th>Mode &amp; Value</th>
                                    <th>Status</th>
                                    <th class="text-end">Fulfillment Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($claims)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No associate claims submitted yet.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($claims as $cl): ?>
                                    <tr>
                                        <td><strong>#<?= $cl['id'] ?></strong></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($cl['associate_name'] ?? 'N/A') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($cl['associate_phone'] ?? '') ?> · <?= htmlspecialchars($cl['associate_email'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($cl['offer_title'] ?? '') ?></div>
                                            <small class="text-muted"><i class="fas fa-award text-warning"></i> <?= htmlspecialchars($cl['reward_item'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                Achieved: <?= number_format((float)($cl['achieved_value'] ?? 0)) ?> / <?= number_format((float)($cl['target_value'] ?? 0)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-success">₹<?= number_format((float)($cl['cash_amount'] ?? 0), 2) ?></div>
                                            <small class="badge bg-<?= ($cl['claim_mode'] ?? '') === 'physical_gift' ? 'info text-white' : 'success' ?>">
                                                <?= ($cl['claim_mode'] ?? '') === 'physical_gift' ? 'Physical Gift' : 'Wallet Cash' ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($cl['claim_status'] === 'claimed'): ?>
                                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending Verification</span>
                                            <?php elseif ($cl['claim_status'] === 'approved'): ?>
                                                <span class="badge bg-info text-white"><i class="fas fa-check me-1"></i> Approved</span>
                                            <?php elseif ($cl['claim_status'] === 'wallet_credited'): ?>
                                                <span class="badge bg-success"><i class="fas fa-wallet me-1"></i> Wallet Credited</span>
                                            <?php elseif ($cl['claim_status'] === 'delivered'): ?>
                                                <span class="badge bg-success"><i class="fas fa-gift me-1"></i> Handed Over</span>
                                            <?php elseif ($cl['claim_status'] === 'rejected'): ?>
                                                <span class="badge bg-danger"><i class="fas fa-times me-1"></i> Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($cl['claim_status'] === 'claimed'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/claims/<?= $cl['id'] ?>/approve" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <button class="btn btn-sm btn-success fw-bold">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                </form>
                                            <?php elseif ($cl['claim_status'] === 'approved'): ?>
                                                <?php if (($cl['claim_mode'] ?? '') === 'wallet_cash' || empty($cl['claim_mode'])): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/claims/<?= $cl['id'] ?>/disburse" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                        <button class="btn btn-sm btn-primary fw-bold" onclick="return confirm('Disburse ₹<?= number_format((float)$cl['cash_amount']) ?> directly to associate wallet?')">
                                                            <i class="fas fa-wallet me-1"></i> Disburse to Wallet
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/claims/<?= $cl['id'] ?>/deliver" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                        <button class="btn btn-sm btn-info text-white fw-bold" onclick="return confirm('Mark physical gift as handed over / delivered?')">
                                                            <i class="fas fa-gift me-1"></i> Mark Delivered
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-success small fw-bold"><i class="fas fa-check-double me-1"></i> Done</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- CREATE CAMPAIGN MODAL -->
<div class="modal fade" id="offerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-bullhorn me-2"></i>Create New Festival Campaign</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Campaign Title <span class="text-danger">*</span></label>
                            <input name="title" class="form-control" maxlength="150" required placeholder="e.g. Navratri Bonanza / Diwali Special Target">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Festival / Occasion Tag</label>
                            <input name="festival_name" class="form-control" maxlength="100" placeholder="e.g. Navratri Special, New Year Blast" value="Festival Special">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small">Description &amp; Terms</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Eligibility terms, duration rules, and reward details..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Target Metric Type</label>
                            <select name="criteria_type" class="form-select">
                                <option value="sale_volume">Sales Volume (₹)</option>
                                <option value="booking_count">Plot Bookings Count</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Target Value <span class="text-danger">*</span></label>
                            <input name="criteria_value" type="number" step="0.01" min="1" class="form-control" value="1000000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Min Verified Site Visits</label>
                            <input name="min_site_visits" type="number" min="0" class="form-control" value="2">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Reward Type</label>
                            <select name="reward_type" class="form-select">
                                <option value="bonus_amount">Cash Bonus</option>
                                <option value="gift">Physical Consumer Gift</option>
                                <option value="commission_boost_pct">Commission Boost %</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Reward Item Name</label>
                            <input name="reward_item" class="form-control" placeholder="e.g. 5g Pure Gold Coin / Smart LED TV" value="5g Pure Gold Coin">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Reward Value (₹ Cash Equivalent)</label>
                            <input name="reward_value" type="number" step="0.01" min="0" class="form-control" value="35000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Colony Scope</label>
                            <select name="colony_id" class="form-select">
                                <option value="0">All Colonies / Townships</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Starts On</label>
                            <input name="starts_at" type="date" class="form-control" value="<?= $today ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Ends On</label>
                            <input name="ends_at" type="date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">Create Campaign Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>
